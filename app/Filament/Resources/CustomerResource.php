<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\Employee;
use App\Services\CustomerService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        $user = Auth::user();
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone_number')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('assigned_employee_id')
                    ->label('Assign to Employee')
                    ->options(Employee::all()->pluck('user.name', 'id')) // Assumes Employee has a user relationship with name
                    ->searchable()
                    ->visible(fn () => Auth::user()->role === 'admin'), // Only admin can assign
                // added_by_employee_id will be set automatically for employees
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('assignedEmployee.user.name') // Assumes assignedEmployee->user->name path
                    ->label('Assigned To')
                    ->default('N/A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('addedByEmployee.user.name') // Assumes addedByEmployee->user->name path
                    ->label('Added By')
                    ->default('N/A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            RelationManagers\ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        if ($user->role === 'employee') {
            // Employees see customers assigned to them OR customers they added themselves.
            return parent::getEloquentQuery()
                ->where(function (Builder $query) use ($user) {
                    $query->where('assigned_employee_id', $user->employee?->id)
                          ->orWhere('added_by_employee_id', $user->employee?->id);
                });
        }
        return parent::getEloquentQuery(); // Admins see all customers
    }

    // In Pages/CreateCustomer.php, override handleRecordCreation to use CustomerService
    // and set added_by_employee_id if the creator is an employee.

    // In Pages/EditCustomer.php, override handleRecordUpdate to use CustomerService.
}

