<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(UserService $userService): void
    {
        // Create Admin User
        $userService->createUser([
            "name" => "Admin User",
            "email" => "admin@example.com",
            "password" => "password", // UserService will hash this
            // No phone_number needed for admin
        ], "admin");

        // Create Employee User
        $userService->createUser([
            "name" => "Employee User",
            "email" => "employee@example.com",
            "password" => "password", // UserService will hash this
            "phone_number" => "123-456-7890" // This will be used by UserService to create Employee record
        ], "employee");
    }
}

