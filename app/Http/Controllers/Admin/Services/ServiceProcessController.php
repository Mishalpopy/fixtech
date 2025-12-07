<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceProcess;
use App\Models\SubService;
use App\Repositories\ServiceProcess\ServiceProcessRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ServiceProcessController extends Controller
{
    use Toast;
    protected $process_repo;

    public function __construct(ServiceProcessRepository $process_repo)
    {
        $this->process_repo = $process_repo;
    }

    public function index(Request $request)
    {
        $subServiceId = $request->get('sub_service_id');
        return Inertia::render('Admin/Services/Processes/Index', [
            'processes' => $this->process_repo->getAllProcesses($subServiceId),
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $subServiceId
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Services/Processes/Create', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function bulkCreate(Request $request)
    {
        return Inertia::render('Admin/Services/Processes/BulkCreate', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'processes' => 'required|array|min:1',
            'processes.*.title' => 'required|string|max:255',
            'processes.*.description' => 'nullable|string',
            'processes.*.order' => 'nullable|integer|min:0',
            'processes.*.status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $subServiceId = $request->sub_service_id;
            $createdProcesses = [];

            foreach ($request->processes as $processData) {
                $process = $this->process_repo->store([
                    'sub_service_id' => $subServiceId,
                    'title' => $processData['title'],
                    'description' => $processData['description'] ?? null,
                    'order' => $processData['order'] ?? 0,
                    'status' => $processData['status'] ?? true,
                ]);
                $createdProcesses[] = $process;
            }
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", count($createdProcesses) . " Process(es) Successfully Created");
        return redirect()->route('admin:processes.index', ['sub_service_id' => $subServiceId]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $process = $this->process_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Process Successfully Created");
        return redirect()->route('admin:processes.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function edit(ServiceProcess $process)
    {
        return Inertia::render('Admin/Services/Processes/Edit', [
            'process' => $process,
            'subServices' => SubService::with('service')->get()
        ]);
    }

    public function update(Request $request, ServiceProcess $process)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $process = $this->process_repo->update($request->all(), $process);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Process Successfully Updated");
        return redirect()->route('admin:processes.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function show(ServiceProcess $process)
    {
        $process->load('subService.service');
        return Inertia::render('Admin/Services/Processes/Show', [
            'process' => $process
        ]);
    }

    public function destroy(ServiceProcess $process)
    {
        try {
            $process->delete();
            $this->toast('success', "Success", "Process Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(ServiceProcess $process)
    {
        try {
            $process->status = !$process->status;
            $process->save();
            $this->toast('success', "Success", "Process Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
