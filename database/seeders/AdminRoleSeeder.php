<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $role = [

            'id' => 1,
            'guard_name' => 'web',
            'name' => 'Admin',

        ];

        $user_role = Role::find((int) $role['id']);
        if (!$user_role) {
            $user_role =  Role::create([
                'id' => 1,
                'guard_name' => $role['guard_name'],
                'name' => $role['name'],
            ]);
        } else {
            $user_role->guard_name = $role['guard_name'];
            $user_role->name = $role['name'];
            $user_role->save();
        }

        $user_role->syncPermissions(Permission::where('guard_name', 'web')->pluck('name'));
    }
}
