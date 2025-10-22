<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test customer
        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'address' => '123 Main Street, City, Country',
                'status' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create additional customers using factory
        Customer::factory(10)->create();
    }
}
