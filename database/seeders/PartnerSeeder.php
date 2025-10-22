<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an approved test partner
        Partner::updateOrCreate(
            ['email' => 'partner@example.com'],
            [
                'name' => 'Test Partner',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'address' => '123 Business Street, City, Country',
                'company_name' => 'Test Company Ltd',
                'trade_license_no' => 'TL-123456',
                'status' => true,
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Create pending partners using factory
        Partner::factory(3)->pending()->create();

        // Create approved partners
        Partner::factory(5)->create();

        // Create rejected partner
        Partner::factory(1)->rejected()->create();
    }
}
