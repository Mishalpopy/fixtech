<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceItem;
use App\Models\SubService;
use App\Repositories\ServiceItem\ServiceItemRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ServiceItemController extends Controller
{
    use Toast;
    protected $service_item_repo;

    public function __construct(ServiceItemRepository $service_item_repo)
    {
        $this->service_item_repo = $service_item_repo;
    }

    public function index(Request $request)
    {
        $subServiceId = $request->get('sub_service_id');
        return Inertia::render('Admin/Services/ServiceItems/Index', [
            'serviceItems' => $this->service_item_repo->getAllServiceItems($subServiceId),
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $subServiceId
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Services/ServiceItems/Create', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $serviceItem = $this->service_item_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Service Item Successfully Created");
        return redirect()->route('admin:service-items.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function edit(ServiceItem $serviceItem)
    {
        return Inertia::render('Admin/Services/ServiceItems/Edit', [
            'serviceItem' => $serviceItem,
            'subServices' => SubService::with('service')->get()
        ]);
    }

    public function update(Request $request, ServiceItem $serviceItem)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $serviceItem = $this->service_item_repo->update($request->all(), $serviceItem);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Service Item Successfully Updated");
        return redirect()->route('admin:service-items.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function show(ServiceItem $serviceItem)
    {
        $serviceItem->load('subService.service');
        return Inertia::render('Admin/Services/ServiceItems/Show', [
            'serviceItem' => $serviceItem
        ]);
    }

    public function destroy(ServiceItem $serviceItem)
    {
        try {
            $serviceItem->delete();
            $this->toast('success', "Success", "Service Item Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(ServiceItem $serviceItem)
    {
        try {
            $serviceItem->status = !$serviceItem->status;
            $serviceItem->save();
            $this->toast('success', "Success", "Service Item Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
