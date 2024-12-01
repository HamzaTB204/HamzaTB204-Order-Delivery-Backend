<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }        
        try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProduct = CartProduct::where('favorite_id', $cart->id)->get();
            if (!$cartProduct) {
                return response()->json(['message' => 'You did not add any product to your favorite'], 404);
            }
            $allproduct=[];
            foreach ($cartProduct as $product) {
                $allproduct[]=Cart::find($product->product_id);
            }
            return response()->json(['Favorite Product' => $allproduct]);
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['Failed' => false, 'message' => 'User  not authenticated.'], 401);
        }      
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1', 
        ]);
        $product = Product::find( $request->product_id);
        try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProductExists = CartProduct::where('cart_id', $cart->id)->where('product_id', $request->product_id)->exists();
            $isUpdated = Product::find( $product->product_id)->Quantity($product->quantity);
            if ($isUpdated) {
                if ($cartProductExists) {
                    return response()->json(['success' => false, 'message' => 'Product is already in your cart.'], 409);
                }
                $totalPrice = $product->price * $request->quantity;
                CartProduct::create([
                    'cart_id'=> $cart->id,
                    'product_id'=> $request->product_id,
                    'quantity' => $request->quantity,
                    'price' => $totalPrice,
                ]);
            }
            return response()->json(['success' => ' Added to your cart '], 200);
        } catch (\Exception $e) {
        
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }   
        try {
            return response()->json(['product'=> Product::find($id)]);

        }catch (\Exception $e) {
        
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }   
       try {
            $cart = Cart::where('user_id', $user->id)->first();
            $cartProduct = CartProduct::where('cart_id', $cart->id)->where('product_id' ,$id)->first();
            $deleted=$cartProduct->delete();
            if ($deleted) {
                return response()->json(['message' => ' Deleted Done '], 200);
            } else {
                return response()->json(['message' => 'Not Deleted'], 500);
            }
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }

    public function add_cart_To_order(){
        $user = auth()->user(); 
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User  not authenticated.'], 401);
        }   
        $cart=Cart::where('user_id', $user->id)->first();
        $cartProduct = CartProduct::where('cart_id', $cart->id)->get();
        try {
            foreach ($cartProduct as $product){
                $order = Order::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                ]);
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->product_id,
                    'quantity' => $product->quantity,
                    'price' => $product->price,
                ]);
                CartProduct::where('cart_id', $cart->id)->where('product_id' ,$product->product_id)->first()->delete();
            }
            return response()->json(['success'=> true,'Done'=> 'Your order has been added successfully '], 200);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => ''. $e->getMessage()], 500);
        }
    }
}
