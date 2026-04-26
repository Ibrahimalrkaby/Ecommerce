<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    protected function normalizePhotoPath(string $photo): string
    {
        $photo = trim($photo);

        return parse_url($photo, PHP_URL_PATH) ?: $photo;
    }

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
            'photo'       => 'required|string', // ✅ it's a URL string, not a file
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
        ]);

        $slug = generateUniqueSlug($request->title, Banner::class);

        // ✅ No need for ->store(), LFM already saved the file
        // Just save the URL/path directly from the input
        $validatedData['slug'] = $slug;
        $validatedData['photo'] = $this->normalizePhotoPath($request->photo);

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
            'photo' => 'required | string',
            'description' => 'nullable | string',
            'status' => 'required|in:active,inactive',
        ]);

        $validatedData['photo'] = $this->normalizePhotoPath($request->photo);

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
