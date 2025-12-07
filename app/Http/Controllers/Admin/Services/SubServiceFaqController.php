<?php

namespace App\Http\Controllers\Admin\Services;

use App\Http\Controllers\Controller;
use App\Models\SubService;
use App\Models\SubServiceFaq;
use App\Repositories\SubServiceFaq\SubServiceFaqRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class SubServiceFaqController extends Controller
{
    use Toast;
    protected $faq_repo;

    public function __construct(SubServiceFaqRepository $faq_repo)
    {
        $this->faq_repo = $faq_repo;
    }

    public function index(Request $request)
    {
        $subServiceId = $request->get('sub_service_id');
        return Inertia::render('Admin/Services/FAQs/Index', [
            'faqs' => $this->faq_repo->getAllFaqs($subServiceId),
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $subServiceId
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Services/FAQs/Create', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function bulkCreate(Request $request)
    {
        return Inertia::render('Admin/Services/FAQs/BulkCreate', [
            'subServices' => SubService::with('service')->get(),
            'subServiceId' => $request->get('sub_service_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $faq = $this->faq_repo->store($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "FAQ Successfully Created");
        return redirect()->route('admin:faqs.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'faqs' => 'required|array|min:1',
            'faqs.*.title' => 'required|string|max:255',
            'faqs.*.description' => 'required|string',
            'faqs.*.order' => 'nullable|integer|min:0',
            'faqs.*.status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $faqs = $this->faq_repo->bulkStore($request->all());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", count($faqs) . " FAQ(s) Successfully Created");
        return redirect()->route('admin:faqs.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function edit(SubServiceFaq $faq)
    {
        return Inertia::render('Admin/Services/FAQs/Edit', [
            'faq' => $faq,
            'subServices' => SubService::with('service')->get()
        ]);
    }

    public function update(Request $request, SubServiceFaq $faq)
    {
        $request->validate([
            'sub_service_id' => 'required|exists:sub_services,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $faq = $this->faq_repo->update($request->all(), $faq);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "FAQ Successfully Updated");
        return redirect()->route('admin:faqs.index', ['sub_service_id' => $request->sub_service_id]);
    }

    public function show(SubServiceFaq $faq)
    {
        $faq->load('subService.service');
        return Inertia::render('Admin/Services/FAQs/Show', [
            'faq' => $faq
        ]);
    }

    public function destroy(SubServiceFaq $faq)
    {
        try {
            $faq->delete();
            $this->toast('success', "Success", "FAQ Successfully Deleted");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }

    public function changeStatus(SubServiceFaq $faq)
    {
        try {
            $faq->status = !$faq->status;
            $faq->save();
            $this->toast('success', "Success", "FAQ Status Updated");
        } catch (Throwable $th) {
            $this->toast('error', "Error", "Something Went Wrong");
        }
        return back();
    }
}
