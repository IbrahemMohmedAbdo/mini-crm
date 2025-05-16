<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use App\Models\User;
use App\Services\UserService; // To create/update the underlying user
use App\Services\EmployeeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Details')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(table: User::class, column: 'email', ignoreRecord: true),
                        Forms\Components\TextInput::make('user.password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $context): string => $context === 'edit' ? 'Leave blank to keep current password.' : ''),
                    ])->columns(2),
                Forms\Components\Section::make('Employee Specific Details')
                    ->schema([
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(255),
                        // Add other employee-specific fields here if any
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable(),
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
                Tables\Actions\DeleteAction::make()
                    ->before(function (Employee $record, EmployeeService $employeeService) {
                        // Custom logic before deleting an employee, e.g., reassign customers
                        // For now, we assume cascading delete or manual reassignment is handled elsewhere or not required by this task
                        // If the user should be deleted as well, that logic would go here or in the service.
                    }),
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
            RelationManagers\AssignedCustomersRelationManager::class,
            RelationManagers\AddedCustomersRelationManager::class,
            RelationManagers\ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function afterCreate(Employee $record, array $data): void
    {
        // User creation logic should be handled in CreateEmployee page using UserService
        // For example, in Pages/CreateEmployee.php:
        // protected function handleRecordCreation(array $data): Employee
        // {
        //     $userService = app(UserService::class);
        //     $user = $userService->createUser($data['user'], 'employee');
        //     $employeeService = app(EmployeeService::class);
        //     return $employeeService->createEmployee($user, $data);
        // }
    }

    public static function afterSave(Employee $record, array $data): void
    {
        $userService = app(UserService::class);
        $userData = [];
        if(isset($data['user']['name'])) $userData['name'] = $data['user']['name'];
        if(isset($data['user']['email'])) $userData['email'] = $data['user']['email'];
        if(isset($data['user']['password']) && filled($data['user']['password'])) $userData['password'] = $data['user']['password'];
        
        if(!empty($userData)){
            $userService->updateUser($record->user, $userData);
        }
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }
}

