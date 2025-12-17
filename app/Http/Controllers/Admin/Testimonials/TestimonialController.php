<?php

namespace App\Http\Controllers\Admin\Testimonials;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Repositories\Testimonial\TestimonialRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class TestimonialController extends Controller
{
    use Toast;
    protected $testimonial_repo;

    public function __construct(TestimonialRepository $testimonial_repo)
    {
        $this->testimonial_repo = $testimonial_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => $this->testimonial_repo->getAllTestimonials()
        ]);
    }

    public function show(Testimonial $testimonial)
    {
        $testimonial->load(['customer', 'approvedBy']);
        return Inertia::render('Admin/Testimonials/Show', [
            'testimonial' => $testimonial
        ]);
    }

    public function edit(Testimonial $testimonial)
    {
        $testimonial->load(['customer', 'approvedBy']);
        return Inertia::render('Admin/Testimonials/Edit', [
            'testimonial' => $testimonial
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $this->testimonial_repo->update($request->all(), $testimonial);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Updated");
        return redirect()->route('admin:testimonials.index');
    }

    public function destroy(Testimonial $testimonial)
    {
        DB::beginTransaction();

        try {
            $testimonial->delete();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Deleted");
        return redirect()->route('admin:testimonials.index');
    }

    public function approve(Testimonial $testimonial)
    {
        DB::beginTransaction();

        try {
            $this->testimonial_repo->approve($testimonial, auth()->id());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Approved");
        return back();
    }

    public function reject(Testimonial $testimonial)
    {
        DB::beginTransaction();

        try {
            $this->testimonial_repo->reject($testimonial, auth()->id());
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Testimonial Successfully Rejected");
        return back();
    }
}
