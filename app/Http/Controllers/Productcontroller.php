<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Product;
class Productcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            "message" => "Products retrieved successfully",
            "list" => Product::with('category')->get(),
            "total" => Product::count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'currency' => 'required|in:USD,KH',
            'status' => 'required|in:active,inactive',
        ]);
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl= $request->file('image')->store('products', 'public');
        }
        $product = Product::create([
            "category_id" => $request->category_id,
            "product_name" => $request->product_name,
            "quantity" => $request->quantity,
            "image" => $imageUrl,
            "purchase_price" => $request->purchase_price,
            "sale_price" => $request->sale_price,
            "currency" => $request->currency,
            "status" => $request->status,
        ]);
        $product->save();
        $product->image = $imageUrl 
        ? asset('storage/' . $imageUrl) 
        : null;
        return response()->json([
            "message" => "Product created successfully",
            "data" => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
        return response()->json([
            "message" => "Product retrieved successfully",
            "data" => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findorFail($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'product_name' => 'required|string',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'currency' => 'required|in:USD,KH',
            'status' => 'required|in:active,inactive',
        ]);
        if ($request->hasFile('image')) {
        // Delete old image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        // Store new image
        $product->image = $request->file('image')->store('products', 'public');
    }
        $product->update([
            "category_id" => $request->category_id,
            "product_name" => $request->product_name,
            "quantity" => $request->quantity,
            "image" => $product->image,
            "purchase_price" => $request->purchase_price,
            "sale_price" => $request->sale_price,
            "currency" => $request->currency,
            "status" => $request->status,
        ]);
        $product->save();
        $product->image = $product->image 
        ? asset('storage/' . $product->image) 
        : null;
        return response()->json([
            "message" => "Product updated successfully",
            "data" => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::with('category')->find($id);
        if (!$product) {
            return response()->json([
                "message" => "Product not found"
            ], 404);
        }
        $product->delete();
        return response()->json([
            "message" => "Product deleted successfully"
        ]);
    }
}
