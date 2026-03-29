<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{

    protected $product = null;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function addToCart(Request $request)
    {
        // dd($request->all);

        if (empty($request->slug)) {
            request()->session()->flash('error', 'Invalid Products');
            return back();
        }
        $product = Product::where('slug', $request->slug)->first();

        if (empty($product)) {
            request()->session()->flash('error', 'Invalid Products');
            return back();
        }

        $already_cart = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->where('product_id', $product->id)->first();
        if ($already_cart) {
            // dd($already_cart);
            $already_cart->quantity = $already_cart->quantity + 1;
            $already_cart->amount = $product->price + $already_cart->amount;
            // return $already_cart->quantity;
            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) return back()->with('error', 'Stock not sufficient!.');
            $already_cart->save();
        } else {

            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = 1;
            $cart->amount = $cart->price * $cart->quantity;
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) return back()->with('error', 'Stock not sufficient!.');
            $cart->save();
            $wishlist = Wishlist::where('user_id', auth()->user()->id)->where('cart_id', null)->update(['cart_id' => $cart->id]);
        }
        request()->session()->flash('success', 'Product successfully added to cart');
        return back();
    }

    public function singleAddToCart(Request $request)
    {
        $request->validate([
            'slug' => 'required',
            'quant' => 'required',
        ]);

        $product = Product::where('slug', $request->slug)->first();
        if ($product->stock < $request->quant[1]) {
            return back()->with('error', 'Out of stock, You can add other products.');
        }
        if (($request->quant[1] < 1) || empty($product)) {
            request()->session()->flash('error', 'Invalid Products');
            return back();
        }
        $already_cart = Cart::where('user_id', auth()->user()->id)->where('order_id', null)->where('product_id', $product->id)->first();

        if ($already_cart) {
            $already_cart->quantity = $already_cart->quantity + $request->quant[1];
            // $already_cart->price = ($product->price * $request->quant[1]) + $already_cart->price ;
            $already_cart->amount = ($product->price * $request->quant[1]) + $already_cart->amount;

            if ($already_cart->product->stock < $already_cart->quantity || $already_cart->product->stock <= 0) return back()->with('error', 'Stock not sufficient!.');

            $already_cart->save();
        } else {

            $cart = new Cart;
            $cart->user_id = auth()->user()->id;
            $cart->product_id = $product->id;
            $cart->price = ($product->price - ($product->price * $product->discount) / 100);
            $cart->quantity = $request->quant[1];
            $cart->amount = ($product->price * $request->quant[1]);
            if ($cart->product->stock < $cart->quantity || $cart->product->stock <= 0) return back()->with('error', 'Stock not sufficient!.');
            // return $cart;
            $cart->save();
        }
        request()->session()->flash('success', 'Product successfully added to cart.');
        return back();
    }

    public function cartDelete(Request $request)
    {
        $cart = Cart::find($request->id);
        if ($cart) {
            $cart->delete();
            request()->session()->flash('success', 'Cart successfully removed');
            return back();
        }
        request()->session()->flash('error', 'Error please try again');
        return back();
    }

    public function cartUpdate(Request $request)
    {
        // dd($request->all());
        if ($request->quant) {
            $error = array();
            $success = '';
            // return $request->quant;
            foreach ($request->quant as $k => $quant) {
                // return $k;
                $id = $request->qty_id[$k];
                // return $id;
                $cart = Cart::find($id);
                // return $cart;
                if ($quant > 0 && $cart) {
                    // return $quant;

                    if ($cart->product->stock < $quant) {
                        request()->session()->flash('error', 'Out of stock');
                        return back();
                    }
                    $cart->quantity = ($cart->product->stock > $quant) ? $quant  : $cart->product->stock;
                    // return $cart;

                    if ($cart->product->stock <= 0) continue;
                    $after_price = ($cart->product->price - ($cart->product->price * $cart->product->discount) / 100);
                    $cart->amount = $after_price * $quant;
                    // return $cart->price;
                    $cart->save();
                    $success = 'Cart successfully updated!';
                } else {
                    $error[] = 'Cart Invalid!';
                }
            }
            return back()->with($error)->with('success', $success);
        } else {
            return back()->with('Cart Invalid!');
        }
    }

    public function checkout(Request $request)
    {
        return view('frontend.pages.checkout');
    }
    // /**
    //  * Display a listing of the resource.
    //  */
    // public function index()
    // {
    //     $brands = Cart::latest('id')->paginate();
    //     return view('backend.brand.index', compact('brands'));
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     return view('backend.brand.create');
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'title' => 'required|string',
    //         'status' => 'required|in:active,inactive',
    //     ]);

    //     $slug = generateUniqueSlug($request->title, Cart::class);

    //     $validatedData['slug'] = $slug;

    //     $brand = Cart::create($validatedData);

    //     $message = $brand
    //         ? 'Brand successfully created'
    //         : 'Error, Please try again';

    //     return redirect()->route('brand.index')->with(
    //         $brand ? 'success' : 'error',
    //         $message
    //     );
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Cart $cart)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit($id)
    // {
    //     $brand = Cart::find($id);

    //     if (!$brand) {
    //         return redirect()->back()->with('error', 'Brand not found');
    //     }

    //     return view('backend.brand.edit', compact('brand'));
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, $id)
    // {
    //     $brand = Cart::find($id);

    //     if (!$brand) {
    //         return redirect()->back()->with('error', 'Brand not found');
    //     }

    //     $validatedData = $request->validate([
    //         'title' => 'required|string',
    //         'status' => 'required|in:active,inactive',
    //     ]);

    //     $status = $brand->update($validatedData);

    //     $message = $status
    //         ? 'Brand successfully updated'
    //         : 'Error, Please try again';

    //     return redirect()->route('brand.index')->with(
    //         $status ? 'success' : 'error',
    //         $message
    //     );
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy($id)
    // {
    //     $brand = Cart::find($id);

    //     if (!$brand) {
    //         return redirect()->back()->with('error', 'Brand not found');
    //     }

    //     $status = $brand->delete();

    //     $message = $status
    //         ? 'Brand successfully deleted'
    //         : 'Error, Please try again';

    //     return redirect()->route('brand.index')->with(
    //         $status ? 'success' : 'error',
    //         $message
    //     );
    // }
}
