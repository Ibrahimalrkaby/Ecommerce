<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Notifications\StatusNotification;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = ProductReview::getAllReview();
        return view('backend.review.index')->with('reviews', $reviews);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'rate' => 'required|numeric|min:1',
            'review' => 'required|string'
        ]);
        $product_info = Product::getProductBySlug($request->slug);
        //  return $product_info;
        // return $request->all();
        $data = $request->all();
        $data['product_id'] = $product_info->id;
        $data['user_id'] = $request->user()->id;
        $data['status'] = 'active';
        // dd($data);
        $status = ProductReview::create($data);

        $user = User::where('role', 'admin')->get();
        $details = [
            'title' => 'New Product Rating!',
            'actionURL' => route('product-detail', $product_info->slug),
            'fas' => 'fa-star'
        ];
        Notification::send($user, new StatusNotification($details));
        if ($status) {
            request()->session()->flash('success', 'Thank you for your feedback');
        } else {
            request()->session()->flash('error', 'Something went wrong! Please try again!!');
        }
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductReview $productReview)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $review = ProductReview::find($id);
        // return $review;
        return view('backend.review.edit')->with('review', $review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $review = ProductReview::find($id);

        if ($review) {
            $this->validate($request, [
                'rate' => 'required|numeric|min:1',
                'review' => 'required|string'
            ]);
            $data = $request->all();
            $status = $review->fill($data)->update();

            if ($status) {
                request()->session()->flash('success', 'Review Successfully updated');
            } else {
                request()->session()->flash('error', 'Something went wrong! Please try again!!');
            }
        } else {
            request()->session()->flash('error', 'Review not found!!');
        }

        return redirect()->route('review.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $review = ProductReview::find($id);
        $status = $review->delete();
        if ($status) {
            request()->session()->flash('success', 'Successfully deleted review');
        } else {
            request()->session()->flash('error', 'Something went wrong! Try again');
        }
        return redirect()->route('review.index');
    }
}
