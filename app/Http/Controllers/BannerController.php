<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::latest('id')->paginate();
        return view('backend.banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title'       => 'required|string',
            'photo'       => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // ✅ file فعلي
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $slug = generateUniqueSlug($request->title, Banner::class);
        $validatedData['slug'] = $slug;

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('banners', 'public');
        }

        $banner = Banner::create($validatedData);

        $message = $banner
            ? 'Banner successfully created'
            : 'Error, Please try again';

        return redirect()->route('banner.index')->with(
            $banner ? 'success' : 'error',
            $message
        );
    }


    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return redirect()->back()->with('error', 'banner not found');
        }

        return view('backend.banner.edit', compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return redirect()->back()->with('error', 'banner not found');
        }

        $validatedData = $request->validate([
            'title' => 'required|string',
            'photo' => $request->hasFile('photo')
                ? 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|string|max:2048',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('photo')) {
            $banner->deleteStoredPhotoIfExists();
            $validatedData['photo'] = $request->file('photo')->store('banners', 'public');
        } else {
            $validatedData['photo'] = $banner->normalizeIncomingPhotoString($request->input('photo'));
        }

        $status = $banner->update($validatedData);

        $message = $status
            ? 'banner successfully updated'
            : 'Error, Please try again';

        return redirect()->route('banner.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return redirect()->back()->with('error', 'banner not found');
        }

        $banner->deleteStoredPhotoIfExists();
        $status = $banner->delete();

        $message = $status
            ? 'banner successfully deleted'
            : 'Error, Please try again';

        return redirect()->route('banner.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
