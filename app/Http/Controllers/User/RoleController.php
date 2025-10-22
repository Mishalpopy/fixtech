<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\RoleRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Throwable;


class RoleController extends Controller
{
    use Toast;
    protected $role_repo;

    public function __construct(RoleRepository $role_repo)
    {
        $this->role_repo = $role_repo;
    }


    public function index()
    {
        return Inertia::render('Roles/Index', [
            'roles' => Role::get()
        ]);
    }

    public function create()
    {
        return Inertia::render('Roles/Create', [
            'permissions' => $this->role_repo->permissionsData()
        ]);
    }



    public function store(Request $request)
    {

        $data = $request->all();

        $this->validateData($data);

        try {
            $role = Role::create([
                'guard_name' => 'web',
                'name' => $data['name'],
            ]);

            $role->givePermissionTo($data['permissions']);
        } catch (Throwable $th) {
            throw $th;

            return back()->withErrors(['errors' => "Something went wrong"]);
        }
        $this->toast('success', 'Success', "Role Created Successfully");
        return redirect()->route('roles.index');
    }

    public function edit(string $id)
    {
        $roles = Role::findOrFail($id);

        $data = $this->role_repo->permissionsData();

        return Inertia::render('Roles/Edit', [
            'role' => $roles->load('permissions'),
            'permissions' => $data
        ]);
    }




    public function update(Request $request, Role $role)
    {

        $data = $request->all();
        $data['id'] = $role->id;

        $this->validateData($data, true);
        try {
            $roles = Role::findOrFail($role->id);
            $roles->name = $data['name'];
            $role->syncPermissions($data['permissions']);
            $roles->update();
        } catch (Throwable $th) {
            throw $th;
            return back()->withErrors(['errors' =>"Something went wrong"]);
        }

        $this->toast('success', 'Updated', "Role Updated Succesfully");
        return redirect()->route('roles.index');
    }

    public function destroy(Role $role)
    {

        DB::beginTransaction();
        try {
            $this->role_repo->delete($role);
        } catch (\Throwable $th) {


            DB::rollback();
            if ($th->getCode() == 400) {

                $this->toast('error', 'Error', $th->getmessage());
                return back()->withErrors(['errors' => $th->getmessage()]);
            } else {
                $this->toast('error', 'Error Message', "Something went wrong");
                return back()->withErrors(['errors' => "Something went wrong"]);
            }
        }
        DB::commit();

        $this->toast('success', 'Deleted', "Role Deleted Succesfully");
        return back();
    }

    public function validateData($data, $for_update = false)
    {
        validator(
            $data,
            [
                'name' => 'bail|required|string|unique:roles,name' . ($for_update ? ',' . $data['id'] : ''),
                'permissions' => 'bail|required|array',
            ],
         
        )->validate();
    }
}
