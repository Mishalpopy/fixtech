<?php

namespace Database\Seeders;

use Database\Seeders\Agent\AgentCategorySeeder;
use Database\Seeders\Flight\FlightTypeSeeder;
use Database\Seeders\Payment\PaymentStatusSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            PermissionSeeder::class,
            AdminSeeder::class,
            AdminRoleSeeder::class,
        ]);
    }
}
