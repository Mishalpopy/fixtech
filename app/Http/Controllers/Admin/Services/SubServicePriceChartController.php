<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\SubService;
use App\Models\SubServicePriceChart;
use App\Repositories\SubServicePriceChart\SubServicePriceChartRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class SubServicePriceChartController extends Controller
{
    use Toast;
    protected $price_chart_repo;

    public function __construct(SubServicePriceChartRepository $price_chart_repo)
    {
        $this->price_chart_repo = $price_chart_repo;
    }

    public function index(Request $request)
    {
        $subServiceId = $request->get('sub_service_id');
        return Inertia::render('Admin/Services/PriceCharts/Index', [
            'priceCharts' => $this->price_chart_repo->getAllPriceCharts($subServiceId),
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $subServiceId
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Services/PriceCharts/Create', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function bulkCreate(Request $request)
    {
        return Inertia::render('Admin/Services/PriceCharts/BulkCreate', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'time_duration' => 'required|string|max:255',
            'current_price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'is_urgent' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $priceChart = $this->price_chart_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Price Chart Successfully Created");
        return redirect()->route('admin:price-charts.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'price_charts' => 'required|array|min:1',
            'price_charts.*.time_duration' => 'required|string|max:255',
            'price_charts.*.current_price' => 'required|numeric|min:0',
            'price_charts.*.original_price' => 'nullable|numeric|min:0',
            'price_charts.*.is_urgent' => 'nullable|boolean',
            'price_charts.*.order' => 'nullable|integer|min:0',
            'price_charts.*.status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $priceCharts = $this->price_chart_repo->bulkStore($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", count($priceCharts) . " Price Chart(s) Successfully Created");
        return redirect()->route('admin:price-charts.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function edit(SubServicePriceChart $priceChart)
    {
        return Inertia::render('Admin/Services/PriceCharts/Edit', [
            'priceChart' => $priceChart,
            'subServices' => SubService::with('service')->get()
        ]);
    }

    public function update(Request $request, SubServicePriceChart $priceChart)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'time_duration' => 'required|string|max:255',
            'current_price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'is_urgent' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $priceChart = $this->price_chart_repo->update($request->all(), $priceChart);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Price Chart Successfully Updated");
        return redirect()->route('admin:price-charts.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function show(SubServicePriceChart $priceChart)
    {
        $priceChart->load('subService.service');
        return Inertia::render('Admin/Services/PriceCharts/Show', [
            'priceChart' => $priceChart
        ]);
    }

    public function destroy(SubServicePriceChart $priceChart)
    {
        try {
            $priceChart->delete();
            $this->toast('success', "Success", "Price Chart Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(SubServicePriceChart $priceChart)
    {
        try {
            $priceChart->status = !$priceChart->status;
            $priceChart->save();
            $this->toast('success', "Success", "Price Chart Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
