<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\SubService;
use App\Repositories\SubService\SubServiceRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class SubServiceController extends Controller
{
    use Toast;
    protected $sub_service_repo;

    public function __construct(SubServiceRepository $sub_service_repo)
    {
        $this->sub_service_repo = $sub_service_repo;
    }

    public function index(Request $request)
    {
        $serviceId = $request->get('service_id');
        return Inertia::render('Admin/Services/SubServices/Index', [
            'subServices' => $this->sub_service_repo->getAllSubServices($serviceId),
            'services' => Service::all(),
            'serviceId' => $serviceId
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Services/SubServices/Create', [
            'services' => Service::all(),
            'serviceId' => $request->get('service_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $subService = $this->sub_service_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Sub Service Successfully Created");
        return redirect()->route('admin:sub-services.index', ['service_id' => $request->service_id]);
    }

    public function edit(SubService $subService)
    {
        return Inertia::render('Admin/Services/SubServices/Edit', [
            'subService' => $subService,
            'services' => Service::all()
        ]);
    }

    public function update(Request $request, SubService $subService)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $subService = $this->sub_service_repo->update($request->all(), $subService);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Sub Service Successfully Updated");
        return redirect()->route('admin:sub-services.index', ['service_id' => $request->service_id]);
    }

    public function show(SubService $subService)
    {
        $subService->load('service', 'serviceItems', 'processes', 'priceCharts', 'faqs');
        return Inertia::render('Admin/Services/SubServices/Show', [
            'subService' => $subService
        ]);
    }

    public function destroy(SubService $subService)
    {
        try {
            $subService->delete();
            $this->toast('success', "Success", "Sub Service Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(SubService $subService)
    {
        try {
            $subService->status = !$subService->status;
            $subService->save();
            $this->toast('success', "Success", "Sub Service Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
