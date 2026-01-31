<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Auth;

class ProductAdminController extends Controller
{

    public function index()
    {
        return Product::with('images')
            ->where('created_by', Auth::id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required',
            'stock' => 'integer|min:0',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpg,jpeg,png'
        ]);

        $product = Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'stock' => $request->stock ?? 0,
            'description' => $request->description ?? '',
            'unit' => 'pcs',
            'status' => 'pending',
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $image->store('products', 'public'),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product submitted for approval',
            'data' => $product->load('images')
        ], 201);
    }
}
