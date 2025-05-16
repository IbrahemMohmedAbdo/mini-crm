<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActionResource\Pages;
use App\Filament\Resources\ActionResource\RelationManagers;
use App\Models\Action;
use App\Models\Customer;
use App\Models\Employee;
use App\Services\ActionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static ?string $navigationIcon = "heroicon-o-clipboard-document-list";

    protected static ?string $navigationGroup = "CRM";

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        return $form
            ->schema([
                Forms\Components\Select::make("customer_id")
                    ->label("Customer")
                    ->relationship("customer", "name") // Assumes Customer model has a name attribute
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make("employee_id")
                    ->label("Employee Responsible")
                    ->options(Employee::all()->pluck("user.name", "id")) // Assumes Employee has user->name
                    ->default($user->role === "employee" ? $user->employee?->id : null)
                    ->disabled($user->role === "employee") // Employee can only log actions for themselves
                    ->required(),
                Forms\Components\Select::make("action_type")
                    ->options([
                        "call" => "Call",
                        "visit" => "Visit",
                        "follow_up" => "Follow-up",
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make("action_date")
                    ->default(Carbon::now())
                    ->required(),
                Forms\Components\Textarea::make("result")
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("customer.name")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("employee.user.name") // Assumes employee->user->name path
                    ->label("Employee")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make("action_type")
                    ->badge()
                     ->color(fn (string $state): string => match ($state) {
                        "call" => "info",
                        "visit" => "warning",
                        "follow_up" => "success",
                        default => "gray",
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make("action_date")
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make("result")
                    ->limit(50)
                    ->tooltip(fn (Action $record) => $record->result),
                Tables\Columns\TextColumn::make("created_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make("updated_at")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListActions::route("/"),
            "create" => Pages\CreateAction::route("/create"),
            "edit" => Pages\EditAction::route("/{record}/edit"),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        if ($user->role === "employee") {
            // Employees see actions they performed OR actions related to customers assigned to them
            return parent::getEloquentQuery()
                ->where("employee_id", $user->employee?->id)
                ->orWhereHas("customer", function (Builder $query) use ($user) {
                    $query->where("assigned_employee_id", $user->employee?->id);
                });
        }
        return parent::getEloquentQuery(); // Admins see all actions
    }

    // In Pages/CreateAction.php, override handleRecordCreation to use ActionService.
    // In Pages/EditAction.php, override handleRecordUpdate to use ActionService.
}

