<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Services\UserService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'employee' => 'Employee',
                    ])
                    ->required(),
                // If employee-specific fields are managed here, add them.
                // For example, if Employee model has a phone_number field:
                Forms\Components\TextInput::make('employee.phone_number')
                    ->label('Phone Number (Employee)')
                    ->tel()
                    ->visible(fn (Forms\Get $get) => $get('role') === 'employee') // Only show if role is employee
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'success',
                        'employee' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('employee.phone_number') // Display employee phone if role is employee
                    ->label('Phone Number')
                    ->getStateUsing(fn (User $record) => $record->employee?->phone_number)
                    ->default('-'), 
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // Using the UserService for creating and updating users
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // The UserService will handle password hashing and employee creation
        return $data;
    }

    public static function afterCreate(User $record, array $data): void
    {
        // If role is employee and employee specific data is provided, ensure employee record is updated
        // This logic is now primarily in UserService, but can be fine-tuned here if needed
        if ($record->role === 'employee' && isset($data['employee']['phone_number'])) {
            if ($record->employee) {
                $record->employee->update(['phone_number' => $data['employee']['phone_number']]);
            } else {
                // This case should ideally be handled by UserService during creation
                Employee::create([
                    'user_id' => $record->id,
                    'phone_number' => $data['employee']['phone_number'],
                ]);
            }
        }
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // The UserService will handle password hashing if provided
        return $data;
    }
    
    public static function afterSave(User $record, array $data): void
    {
        // Similar to afterCreate, ensure employee data is handled if role is employee
        if ($record->role === 'employee' && isset($data['employee']['phone_number'])) {
            if ($record->employee) {
                $record->employee->update(['phone_number' => $data['employee']['phone_number']]);
            } else {
                 Employee::create([
                    'user_id' => $record->id,
                    'phone_number' => $data['employee']['phone_number'],
                ]);
            }
        } elseif ($record->role === 'admin' && $record->employee) {
            // If role changed from employee to admin, consider deleting the associated employee record
            // $record->employee->delete(); // Or handle as per business logic
        }
    }
}

