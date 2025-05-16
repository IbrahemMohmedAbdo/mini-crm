<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $userService = app(UserService::class);
        // The $data array already contains user details and potentially employee.phone_number
        // The UserService updateUser method handles updating the user and employee if role is employee
        // and phone_number is provided within the $data["employee"] array.

        $userData = [
            "name" => $data["name"],
            "email" => $data["email"],
        ];

        if (!empty($data["password"])) {
            $userData["password"] = $data["password"]; // UserService will hash it
        }
        
        if (isset($data["role"])) {
            $userData["role"] = $data["role"];
        }

        // Pass employee-specific data if role is employee
        if ($data["role"] === "employee" && isset($data["employee"]["phone_number"])) {
            $userData["phone_number"] = $data["employee"]["phone_number"];
        }

        return $userService->updateUser($record, $userData);
    }
}

