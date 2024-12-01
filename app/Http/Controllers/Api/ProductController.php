<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $product = Product::all();
            if ($product->isEmpty()) {
                return response()->json([
                    'message' => 'No products available.'
                ], 404); 
            }
            return response()->json([
                'data' =>'',
                'products' => $product]);
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
        }
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       try {
        $fileds = $request-> validate([
            'en_name' => "required|max:30",
            'ar_name' => "required|max:30",
            'en_description' => "required|max:255",
            'ar_description' => "required|max:255",
            'quantity' => "required",
            'price' => "required",
            'store_id' => "required",
        ]);

        Product::create($fileds);

        return response()->json([
            'message' =>  "Added Done"
        ]);
       }catch (\Exception $e) {
        return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
       }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::where('id', $id)->first();
        if (!$product) {
            return response()->json(['message' => 'Not Found'],200) ;
        }
        return response()->json(['product' => $product], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       try {
        $product = Product::where('id', $id)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $fields = $request-> validate([
            'en_name' => "nullable|max:30",
            'ar_name' => "nullable|max:30",
            'en_description' => "nullable|max:15",
            'ar_description' => "nullable|max:20",
            'quantity' => "nullable",
            'price' => "nullable",
            'store_id' => "exists:stores,id",
        ]);
        $product->fill(array_filter($fields)); 
        $product->save();
        return response()->json([
            'message' => 'updated Done',
            'product' => $product], 200);
       } catch (\Exception $e) {
             return response()->json(['message' => 'An error occurred' . $e->getMessage()], 500);
       }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        try {
            if (!$product) {
                return response()->json(['message' => 'The product Does not Exist'], 404);
            }
    
            $deleted = $product->delete();
    
            if ($deleted) {
                return response()->json(['message' => ' Deleted Done '], 200);
            } else {
                return response()->json(['message' => 'Not Deleted'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => ' Something Wrong happenend ' . $e->getMessage()], 500);
        }
    }
}
