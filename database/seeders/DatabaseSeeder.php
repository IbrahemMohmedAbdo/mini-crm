<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // First create users (admin and employee)
        $this->call(UserSeeder::class);

        // Then create employee records (though UserSeeder already handles this)
        $this->call(EmployeeSeeder::class);

        // Create customers (which depend on users and employees)
        $this->call(CustomerSeeder::class);

        // Finally create actions (which depend on customers and employees)
        $this->call(ActionSeeder::class);
    }
}
