<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Repositories\Service\ServiceRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ServiceController extends Controller
{
    use Toast;
    protected $service_repo;

    public function __construct(ServiceRepository $service_repo)
    {
        $this->service_repo = $service_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Services/Index', [
            'services' => $this->service_repo->getAllServices()
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Services/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $service = $this->service_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Service Successfully Created");
        return redirect()->route('admin:services.index');
    }

    public function edit(Service $service)
    {
        return Inertia::render('Admin/Services/Edit', [
            'service' => $service
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $service = $this->service_repo->update($request->all(), $service);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Service Successfully Updated");
        return redirect()->route('admin:services.index');
    }

    public function show(Service $service)
    {
        $service->load('subServices.serviceItems');
        return Inertia::render('Admin/Services/Show', [
            'service' => $service
        ]);
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();
            $this->toast('success', "Success", "Service Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(Service $service)
    {
        try {
            $service->status = !$service->status;
            $service->save();
            $this->toast('success', "Success", "Service Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
