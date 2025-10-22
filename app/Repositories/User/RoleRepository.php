<?php

namespace App\Repositories\User;

use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository
{

    public function getAllRoles()
    {
        return Role::get();
    }



    public function permissionsData()
    {

        $permissions = Permission::where('guard_name', 'web')->get();

        $data['count'] = $permissions->count();
        $data['permission'] = [];
        foreach ($permissions as $permission) {

            // dd($permission->module == 'Dashboard');

            if ($permission->module == 'Dashboard') {
                $data['permission']['Dashboard'][] = $permission;
            } else if ($permission->module == 'Users') {
                $data['permission']['Users'][] = $permission;
            } else if ($permission->module == 'User Groups') {
                $data['permission']['User Groups'][] = $permission;
            } else if ($permission->module == 'Roles') {
                $data['permission']['Roles'][] = $permission;
            } else if ($permission->module == 'Sites') {
                $data['permission']['Sites'][] = $permission;
            } else if ($permission->module == 'Site Groups') {
                $data['permission']['Site Groups'][] = $permission;
            } else if ($permission->module == 'Question Types') {
                $data['permission']['Question Types'][] = $permission;
            } else if ($permission->module == 'Templates') {
                $data['permission']['Templates'][] = $permission;
            } else if ($permission->module == 'Inspections') {
                $data['permission']['Inspections'][] = $permission;
            } else if ($permission->module == 'Tasks') {
                $data['permission']['Tasks'][] = $permission;
            } else if ($permission->module == 'Schedules') {
                $data['permission']['Schedules'][] = $permission;
            } else if ($permission->module == 'Resources') {
                $data['permission']['Resources'][] = $permission;
            } else if ($permission->module == 'Task Types') {
                $data['permission']['Task Types'][] = $permission;
            } else if ($permission->module == 'Task Status') {
                $data['permission']['Task Statuses'][] = $permission;
            } else if ($permission->module == 'Task Labels') {
                $data['permission']['Task Labels'][] = $permission;
            } else if ($permission->module == 'Resource Types') {
                $data['permission']['Resource Types'][] = $permission;
            } else if ($permission->module == 'Resource Readings') {
                $data['permission']['Resource Readings'][] = $permission;
            }
        }

        return $data;
    }

    public function delete($role)
    {

        if ($role->users->count() > 0) {

            throw new Exception('This role has user cannot Delete', 400);
            return;
        } else {

            $role->delete();
        }
    }























    // public function store($data)
    // {

    //     $location = Location::create([
    //         'name' => $data['name']
    //     ]);

    //     return $location;
    // }

    // public function update($data, $location)
    // {
    //     $location->name = $data['name'];
    //     $location->save();
    //     return $location;
    // }

    // public function getAllLocations()
    // {
    //     return Location::get();
    // }

    // public function getAllActiveLocations()
    // {
    //     return Location::where('status', true)->get();
    // }

    // public function changeStatus($location)
    // {

    //     $location->status = !$location->status;
    //     $location->save();
    //     return true;
    // }

    // public function destroy($location)
    // {
    //     $location->delete();
    //     return true;
    // }
}
