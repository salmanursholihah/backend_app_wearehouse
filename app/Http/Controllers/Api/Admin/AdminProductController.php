<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    /**
     * GET /api/admin/products
     * Optional query:
     * - status=pending|approved|rejected
     * - search=keyword (sku/name)
     * - per_page=10
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $status  = $request->query('status');
        $search  = $request->query('search');

        $q = Product::query()
            ->with(['creator:id,name,email', 'approver:id,name,email'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $q->where('status', $status);
        }

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('sku', 'like', "%{$search}%")
                   ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $products = $q->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * POST /api/admin/products
     * body:
     * {
     *   "sku": "SKU-001",
     *   "name": "Filter Air",
     *   "description": "opsional",
     *   "stock": 10,
     *   "unit": "pcs"
     * }
     */
    public function store(Request $request)
    {
        $admin = $request->user();

        $data = $request->validate([
            'sku'         => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'unit'        => ['nullable', 'string', 'max:30'],
        ]);

        $product = Product::create([
            'sku'         => $data['sku'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'stock'       => (int)($data['stock'] ?? 0),
            'unit'        => $data['unit'] ?? 'pcs',
            'created_by'  => $admin->id,
            // status default 'pending' by migration
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat.',
            'data' => $product,
        ], 201);
    }

    /**
     * GET /api/admin/products/{id}
     */
    public function show($id)
    {
        $product = Product::with([
            'creator:id,name,email',
            'approver:id,name,email',
            'images:id,product_id,image,created_at',
        ])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        // Tambahkan url image
        $product->images->transform(function ($img) {
            $img->image_url = $img->image ? asset('storage/' . $img->image) : null;
            return $img;
        });

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * PUT /api/admin/products/{id}
     * Update data basic product (tidak mengubah created_by/approved_by)
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $data = $request->validate([
            'sku'         => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'name'        => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'stock'       => ['nullable', 'integer', 'min:0'],
            'unit'        => ['nullable', 'string', 'max:30'],
        ]);

        $product->update([
            'sku'         => $data['sku'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'stock'       => (int)($data['stock'] ?? $product->stock),
            'unit'        => $data['unit'] ?? $product->unit,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'data' => $product,
        ]);
    }

    /**
     * DELETE /api/admin/products/{id}
     * Hapus product + hapus file images juga
     */
    public function destroy($id)
    {
        $product = Product::with('images')->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($product) {
            // hapus file images dulu (record akan cascade, tapi file tidak)
            foreach ($product->images as $img) {
                if ($img->image && Storage::disk('public')->exists($img->image)) {
                    Storage::disk('public')->delete($img->image);
                }
            }

            $product->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    /**
     * PUT /api/admin/products/{id}/approve
     * Set status approved + approved_by (migration: approved_by nullable)
     */
    public function approve(Request $request, $id)
    {
        $admin = $request->user();

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->update([
            'status'      => 'approved',
            'approved_by' => $admin->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil di-approve.',
            'data' => $product,
        ]);
    }

    /**
     * PUT /api/admin/products/{id}/reject
     * Set status rejected + approved_by (sebagai reviewer)
     */
    public function reject(Request $request, $id)
    {
        $admin = $request->user();

        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $product->update([
            'status'      => 'rejected',
            'approved_by' => $admin->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil di-reject.',
            'data' => $product,
        ]);
    }

    /**
     * GET /api/admin/products/{id}/images
     */
    public function images($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $images = ProductImage::where('product_id', $id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($img) {
                $img->image_url = $img->image ? asset('storage/' . $img->image) : null;
                return $img;
            });

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    /**
     * POST /api/admin/products/{id}/images
     * form-data:
     * - image (required) => jpg/jpeg/png/webp
     */
    public function addImage(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        $data = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $path = $request->file('image')->store('products', 'public');

        $img = ProductImage::create([
            'product_id' => $product->id,
            'image'      => $path,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil ditambahkan.',
            'data' => [
                'id' => $img->id,
                'product_id' => $img->product_id,
                'image' => $img->image,
                'image_url' => asset('storage/' . $img->image),
                'created_at' => $img->created_at,
            ],
        ], 201);
    }

    /**
     * DELETE /api/admin/product-images/{imageId}
     */
    public function deleteImage($imageId)
    {
        $img = ProductImage::find($imageId);

        if (!$img) {
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan.',
            ], 404);
        }

        DB::transaction(function () use ($img) {
            if ($img->image && Storage::disk('public')->exists($img->image)) {
                Storage::disk('public')->delete($img->image);
            }
            $img->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil dihapus.',
        ]);
    }
}
