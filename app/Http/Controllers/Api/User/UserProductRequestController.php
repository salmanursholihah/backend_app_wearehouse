<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProductRequestController extends Controller
{
    /**
     * POST /api/user/product-requests
     * Create product_request + upload file (optional)
     *
     * form-data:
     * - product_id (optional kalau tabel kamu ada)
     * - quantity
     * - purpose (optional)
     * - file (optional) => pdf/jpg/png
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'purpose'    => ['nullable', 'string', 'max:500'],
            'file'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ]);

        // Jika ada product_id di tabel, validasi produk harus approved
        if (!empty($data['product_id'])) {
            $product = Product::where('id', $data['product_id'])
                ->where('status', 'approved')
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan atau belum approved.',
                ], 422);
            }
        }

        $created = DB::transaction(function () use ($user, $request, $data) {
            $filePath = null;

            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store('product_requests', 'public');
            }

            $pr = ProductRequest::create([
                'user_id'    => $user->id,
                'product_id' => $data['product_id'] ?? null, // kalau kolom ada
                'quantity'   => (int) $data['quantity'],
                'purpose'    => $data['purpose'] ?? null,    // sesuaikan nama kolom
                'file_path'  => $filePath,
                'status'     => 'pending',
            ]);

            return $pr;
        });

        $created->load([
            'product:id,name,status', // kalau relasi product ada
            'user:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product request berhasil dibuat.',
            'data'    => [
                'product_request' => $created,
                'file_url' => $created->file_path ? asset('storage/' . $created->file_path) : null,
            ],
        ], 201);
    }

    /**
     * GET /api/user/product-requests
     * List product requests milik user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 10);

        $list = ProductRequest::where('user_id', $user->id)
            ->with(['product:id,name,status'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Tambahkan file_url di response (opsional)
        $list->getCollection()->transform(function ($item) {
            $item->file_url = $item->file_path ? asset('storage/' . $item->file_path) : null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    /**
     * GET /api/user/product-requests/{id}
     * Detail product request milik user
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $pr = ProductRequest::where('user_id', $user->id)
            ->with(['product:id,name,status', 'user:id,name,email'])
            ->find($id);

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Product request tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'product_request' => $pr,
                'file_url' => $pr->file_path ? asset('storage/' . $pr->file_path) : null,
            ],
        ]);
    }

    /**
     * PUT /api/user/product-requests/{id}/cancel
     * Cancel hanya jika status masih pending
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $pr = ProductRequest::where('user_id', $user->id)->find($id);

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Product request tidak ditemukan.',
            ], 404);
        }

        if ($pr->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa dibatalkan karena status bukan pending.',
            ], 422);
        }

        $pr->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product request berhasil dibatalkan.',
            'data'    => $pr,
        ]);
    }
}
