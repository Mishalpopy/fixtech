<?php

namespace App\Http\Controllers\Admin\Reviews;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Repositories\Review\ReviewRepository;
use App\Traits\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Throwable;

class ReviewController extends Controller
{
    use Toast;
    protected $review_repo;

    public function __construct(ReviewRepository $review_repo)
    {
        $this->review_repo = $review_repo;
    }

    public function index()
    {
        return Inertia::render('Admin/Reviews/Index', [
            'reviews' => $this->review_repo->getAllReviews()
        ]);
    }

    public function show(Review $review)
    {
        $review->load(['customer', 'service']);
        return Inertia::render('Admin/Reviews/Show', [
            'review' => $review
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();

        try {
            $this->review_repo->update($request->all(), $review);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Successfully Updated");
        return redirect()->route('admin:reviews.index');
    }

    public function destroy(Review $review)
    {
        DB::beginTransaction();

        try {
            $review->delete();
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Successfully Deleted");
        return redirect()->route('admin:reviews.index');
    }

    public function changeStatus(Review $review)
    {
        DB::beginTransaction();

        try {
            $newStatus = $review->status === 'active' ? 'inactive' : 'active';
            $review->update(['status' => $newStatus]);
        } catch (Throwable $th) {
            DB::rollBack();
            $this->toast('error', "Error", "Something Went Wrong");
            return back();
        }
        DB::commit();
        $this->toast('success', "Success", "Review Status Successfully Updated");
        return back();
    }
}
