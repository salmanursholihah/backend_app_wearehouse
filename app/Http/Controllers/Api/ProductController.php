<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * GET ALL PRODUCTS (ADMIN & USER)
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Product::with('images')->latest()->get()
        ]);
    }

    /**
     * CREATE PRODUCT + MULTI IMAGE (ADMIN ONLY)
     */
    public function store(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'stock' => 'integer|min:0',
            'unit' => 'nullable|string',
            'images.*' => 'image|mimes:jpg,jpeg,png'
        ]);

        $product = Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'description' => $request->description,
            'stock' => $request->stock ?? 0,
            'unit' => $request->unit ?? 'pcs',
            'created_by' => Auth::id()
        ]);

        // ✅ MULTI IMAGE UPLOAD
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created',
            'data' => $product->load('images')
        ], 201);
    }

    /**
     * UPDATE PRODUCT (ADMIN ONLY)
     */
    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string',
            'description' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'unit' => 'nullable|string',
            'images.*' => 'image|mimes:jpg,jpeg,png'
        ]);

        $product->update($request->only([
            'name', 'description', 'stock', 'unit'
        ]));

        // ✅ TAMBAH IMAGE BARU (TANPA HAPUS YANG LAMA)
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated',
            'data' => $product->load('images')
        ]);
    }

    /**
     * DELETE PRODUCT + IMAGE (ADMIN ONLY)
     */
    public function destroy($id)
    {
        $this->checkAdmin();

        $product = Product::with('images')->findOrFail($id);

        // hapus file image
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted'
        ]);
    }

    /**
     * ROLE CHECK
     */
    private function checkAdmin()
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized');
        }
    }
}
