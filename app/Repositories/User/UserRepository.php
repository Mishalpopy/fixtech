<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Models\User\UserGroup;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserRepository
{

    public function createUser($data)
    {

        $role = Role::where('id', $data['role'])->pluck('name')->first();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => true,
            'password' => $data['password'],
        ]);
        $user->assignRole($role);

        $sites = [];
        $site_groups = [];

        if (isset($data['site_groups'])) {
            foreach ($data['site_groups'] as $site) {
                if (str_starts_with($site, 'site_group_')) {
                    $site_groups[] = str_replace('site_group_', '', $site);
                } elseif (str_starts_with($site, 'site_')) {
                    $sites[] = str_replace('site_', '', $site);
                }
            }
        }

        $user->sites()->sync($sites);
        $user->siteGroups()->sync($site_groups);

        return $user;
    }

    public function update(User $user, $data)
    {
        $roles = Role::where('id', $data['role'])->pluck('name')->first();
        $user->name = $data['name'];
        $user->email = $data['email'];

        $user->save();
        if (!$user->roles->isEmpty() && $roles) {
            foreach ($user->roles as $role) {
                $user->removeRole($role->name);
            }
        }

        $site_groups = [];
        $sites = [];

        if (isset($data['site_groups'])) {
            foreach ($data['site_groups'] as $site) {
                if (str_starts_with($site, 'site_group_')) {
                    $site_groups[] = str_replace('site_group_', '', $site);
                } elseif (str_starts_with($site, 'site_')) {
                    $sites[] = str_replace('site_', '', $site);
                }
            }
        }
        
        $user->sites()->sync($sites);
        $user->siteGroups()->sync($site_groups);

        $user->assignRole($roles);
        return $user;
    }

    public function changePassword(User $user, $data)
    {
        // $user->password = Hash::make($data['password']);
        // $user->save();
        // return $user;
    }

    public function updateStatus(User $user)
    {
        $user->status = !$user->status;
        $user->save();
        return $user;
    }

    public function updatePassword($data, $user)
    {
        $user->password = Hash::make($data['password']);
        $user->save();

        return $user;
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        // PasswordResetToken::where('email', $user->email)->delete();
    }

    public function getAllActiveUsers()
    {
        return User::where('status', true)->get();
    }

    public function getAllUsers()
    {
        return User::get();
    }
}
