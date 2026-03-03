<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;

class UserProductController extends Controller
{
    /**
     * GET /api/user/products
     * List produk yang sudah di-approve
     */
    public function index(Request $request)
    {
        $products = Product::where('status', 'approved')
            ->withCount('images') // jumlah gambar
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar produk berhasil diambil.',
            'data'    => $products
        ]);
    }

    /**
     * GET /api/user/products/{id}
     * Detail produk
     */
    public function show($id)
    {
        $product = Product::where('status', 'approved')
            ->with(['images', 'approvedBy:id,name'])
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau belum disetujui.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $product
        ]);
    }

    /**
     * GET /api/user/products/{id}/images
     * Ambil semua gambar produk
     */
    public function images($id)
    {
        $product = Product::where('status', 'approved')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        $images = ProductImage::where('product_id', $id)->get();

        return response()->json([
            'success' => true,
            'data'    => $images
        ]);
    }
}
