<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest('id')->paginate();
        return view('backend.users.index')->with('users', $users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|string|unique:user',
            'password' => 'required|string',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|string'
        ]);

        $slug = generateUniqueSlug($request->title, User::class);

        $validatedData['slug'] = $slug;

        $user = User::create($validatedData);

        $message = $user
            ? 'user successfully created'
            : 'Error, Please try again';

        return redirect()->route('user.index')->with(
            $user ? 'success' : 'error',
            $message
        );
    }

    /**
     * Display the specified resource.
     */
    // public function show(User ${{ modelVariable }})
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'user not found');
        }

        return view('backend.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'user not found');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'required|string|unique:user',
            'password' => 'required|string',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|string'
        ]);

        $status = $user->update($validatedData);

        $message = $status
            ? 'user successfully updated'
            : 'Error, Please try again';

        return redirect()->route('user.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'user not found');
        }

        $status = $user->delete();

        $message = $status
            ? 'user successfully deleted'
            : 'Error, Please try again';

        return redirect()->route('user.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
