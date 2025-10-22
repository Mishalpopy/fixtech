<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@mail.com',
                'password' => Hash::make('123456')
            ]);
        } else {
            $admin->name = 'Admin';
            $admin->email = 'admin@mail.com';
            $admin->save();
        }

        $role = Role::find(1);
        $admin->assignRole($role);
    }
}
