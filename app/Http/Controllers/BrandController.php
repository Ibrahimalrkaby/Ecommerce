<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;


class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::latest('id')->paginate();
        return view('backend.brand.index', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.brand.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);
        $slug = generateUniqueSlug($request->title, Brand::class);

        $validatedData['slug'] = $slug;
        $brand = Brand::create($validatedData);

        $message = $brand ? 'Brand created successfully' : 'Brand not created';

        return redirect()->route('brand.index')->with(
            $brand ? 'success' : 'error',
            $message
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Brand $brand)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return redirect()->route('brand.index')->with('error', 'Brand not found');
        }
        return view('backend.brand.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            return redirect()->back()->with('error', 'Brand not found');
        }
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);
        $status = $brand->update($validatedData);
        $message = $status ? 'Brand updated successfully' : 'Brand not updated';
        return redirect()->route('brand.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return redirect()->back()->with('error', 'Brand not found');
        }

        $status = $brand->delete();

        $message = $status
            ? 'Brand successfully deleted'
            : 'Error, Please try again';

        return redirect()->route('brand.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
