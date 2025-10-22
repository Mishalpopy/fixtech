<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'View Dashboard']);

        //users 
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'View Users']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Create Users']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Edit Users']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Delete Users']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Change Users Status']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Update Users Password']);

        //customers 
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'View Customers']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Create Customers']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Edit Customers']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Delete Customers']);

        //partners 
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'View Partners']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Create Partners']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Edit Partners']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Delete Partners']);
        Permission::updateOrCreate(['guard_name' => 'web', 'name' => 'Approve Partners']);
    }
}
