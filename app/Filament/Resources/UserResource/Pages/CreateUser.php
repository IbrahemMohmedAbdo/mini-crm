<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\UserService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): \App\Models\User
    {
        $userService = app(UserService::class);
        // The $data array already contains user details and potentially employee.phone_number
        // The UserService createUser method handles creating the user and employee if role is employee
        // and phone_number is provided within the $data["employee"] array.
        
        $userData = [
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => $data["password"], // UserService will hash it
        ];

        // Pass employee-specific data if role is employee
        if ($data["role"] === "employee" && isset($data["employee"]["phone_number"])) {
            $userData["phone_number"] = $data["employee"]["phone_number"];
        }

        return $userService->createUser($userData, $data["role"]);
    }
}

