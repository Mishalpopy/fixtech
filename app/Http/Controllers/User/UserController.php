<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\AccountCreatedMail;
use App\Models\User;
use App\Repositories\Site\SiteRepository;
use App\Repositories\User\RoleRepository;
use App\Repositories\User\UserRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Throwable;

class UserController extends Controller
{
    use Toast;
    protected $user_repo, $role_repository,$site_reposiotry;

    public function __construct(UserRepository $user_repo, RoleRepository $role_repository)
    {
        $this->user_repo = $user_repo;
        $this->role_repository = $role_repository;
    }


    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::get(),
            'roles' => $this->role_repository->getAllRoles(),
        ]);
    }



    public function store(Request $request)
    {
        DB::beginTransaction();

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'password' => 'required',

        ]);

        try {
            $user = $this->user_repo->createUser($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "User Successfully Created");
        return back();
    }

    public function update(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $this->user_repo->update($user, $request->all());
        } catch (Throwable $th) {
            DB::rollBack();
         
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "User Successfully Updated");
        return back();
    }

    public function  changeStatus(Request $request,  User $user)
    {
        DB::beginTransaction();
        try {
            $status = $this->user_repo->updateStatus($user);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Status Successfully Updated");
        return back();
    }

    public function updatePassword(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $user = $this->user_repo->updatePassword($request->all(), $user);
        } catch (Throwable $th) {
            DB::rollBack();
            dd($th);
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Password Successfully Updated");
        return back();
    }

    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            $this->user_repo->deleteUser($user);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "User Successfully Deleted");
        return back();
    }
}
