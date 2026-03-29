<?php

namespace App\Http\Controllers;

use App\Models\Shipping;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shippings = Shipping::orderBy('id', 'DESC')->paginate(10);
        return view('backend.shipping.index', compact('shippings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.shipping.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string',
            'price' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ]);




        $status = Shipping::create($validatedData);
        if ($status) {
            request()->session()->flash('success', 'Shipping successfully created');
        } else {
            request()->session()->flash('error', 'Error, Please try again');
        }
        return redirect()->route('shipping.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shipping $shipping)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return redirect()->back()->with('error', 'shipping not found');
        }

        return view('backend.shipping.edit', compact('shipping'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return redirect()->back()->with('error', 'shipping not found');
        }

        $this->validate($request, [
            'type' => 'string|required',
            'price' => 'nullable|numeric',
            'status' => 'required|in:active,inactive'
        ]);
        $data = $request->all();
        // return $data;
        $status = $shipping->fill($data)->save();
        if ($status) {
            request()->session()->flash('success', 'Shipping successfully updated');
        } else {
            request()->session()->flash('error', 'Error, Please try again');
        }
        return redirect()->route('shipping.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $shipping = Shipping::find($id);

        if (!$shipping) {
            return redirect()->back()->with('error', 'shipping not found');
        }

        $status = $shipping->delete();

        $message = $status
            ? 'shipping successfully deleted'
            : 'Error, Please try again';

        return redirect()->route('shipping.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
