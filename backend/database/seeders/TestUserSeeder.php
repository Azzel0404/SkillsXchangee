<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user for database connection testing
        User::create([
            'firstname' => 'Test',
            'lastname' => 'User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'bdate' => '1990-01-01',
            'address' => 'Test Address, Test City',
            'gender' => 'other',
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        $this->command->info('Test user created successfully!');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: password123');
    }
}
