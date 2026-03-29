<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest('id')->paginate();
        // dd($categories);
        return view('backend.category.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parent_cats = Category::where('is_parent', 1)->orderBy('title', 'asc')->get();
        return view('backend.category.create', compact('parent_cats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string',
            'summary' => 'nullable|string',
            'photo' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $slug = generateUniqueSlug($request->title, Category::class);

        $validatedData['slug'] = $slug;

        $validatedData['is_parent'] = $request->input('is_parent', '0');

        $category = Category::create($validatedData);

        $message = $category
            ? 'category successfully created'
            : 'Error, Please try again';

        return redirect()->route('category.index')->with(
            $category ? 'success' : 'error',
            $message
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $category = Category::find($id);
        $parent_cats = Category::where('is_parent', 1)->get();
        if (!$category) {
            return redirect()->back()->with('error', 'category not found');
        }

        return view('backend.category.edit', compact('category', 'parent_cats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return redirect()->back()->with('error', 'category not found');
        }

        $validatedData = $request->validate([
            'title' => 'required|string',
            'summary' => 'nullable|string',
            'photo' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'is_parent' => 'sometimes|in:1',
            'parent_id' => 'nullable|exists:categories,id'
        ]);

        $validatedData['is_parent'] = $request->input('is_parent', 0);

        $status = $category->update($validatedData);

        $message = $status
            ? 'category successfully updated'
            : 'Error, Please try again';

        return redirect()->route('category.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        // dd($category);
        $child_cat_id = Category::where('parent_id', $id)->pluck('id');

        $status = $category->delete();

        if ($status && $child_cat_id->count() > 0) {
            Category::shiftChild($child_cat_id);
        }

        $message = $status
            ? 'category successfully deleted'
            : 'Error, Please try again';

        return redirect()->route('category.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    public function getChildByParent(Request $request)
    {
        $category = Category::findOrFail($request->id);
        $child_cat = Category::getChildByParentID($request->id);
        dd('child_cat');

        if ($child_cat->count() <= 0) {
            return response()->json(['status' => false, 'msg' => '', 'data' => null]);
        }

        return response()->json(['status' => true, 'msg' => '', 'data' => $child_cat]);
    }
}
