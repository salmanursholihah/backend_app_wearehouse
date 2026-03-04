<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProductRequestController extends Controller
{

    /**
     * GET /api/admin/product-requests
     * Optional query:
     * - status=pending|approved|rejected
     * - search=keyword (user name/email OR product name/sku)
     * - per_page=10
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $status  = $request->query('status');
        $search  = $request->query('search');

        $q = ProductRequest::query()
            ->with([
                'user:id,name,email',
                'product:id,sku,name,stock,unit,status',
                'processedBy:id,name,email',
            ])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $q->where('status', $status);
        }

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('product', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            });
        }

        $list = $q->paginate($perPage);

        // Tambahkan file_url jika butuh
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
     * GET /api/admin/product-requests/{id}
     */
    public function show($id)
    {
        $pr = ProductRequest::with([
            'user:id,name,email,phone,address',
            'product:id,sku,name,stock,unit,status',
            'processedBy:id,name,email',
        ])->find($id);

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Product request tidak ditemukan.',
            ], 404);
        }

        $pr->file_url = $pr->file_path ? asset('storage/' . $pr->file_path) : null;

        return response()->json([
            'success' => true,
            'data'    => $pr,
        ]);
    }

    /**
     * PUT /api/admin/product-requests/{id}/approve
     * Approve:
     * - hanya jika pending
     * - cek stock cukup
     * - status -> approved, processed_by
     * - kurangi product.stock
     * - insert stock_logs type=out
     */
    public function approve(Request $request, $id)
    {
        $admin = $request->user();

        $pr = ProductRequest::with(['product'])->find($id);
        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Product request tidak ditemukan.',
            ], 404);
        }

        if ($pr->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa approve karena status bukan pending.',
            ], 422);
        }

        if (!$pr->product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk pada request tidak ditemukan.',
            ], 422);
        }

        // Cek stok cukup
        if ((int) $pr->product->stock < (int) $pr->qty) {
            return response()->json([
                'success' => false,
                'message' => "Stok '{$pr->product->name}' tidak cukup. Stok: {$pr->product->stock}, diminta: {$pr->qty}.",
            ], 422);
        }

        $updated = DB::transaction(function () use ($pr, $admin) {
            // Update status request
            $update = [
                'status' => 'approved',
            ];

            // processed_by optional (kalau kolom ada)
            if (array_key_exists('processed_by', $pr->getAttributes())) {
                $update['processed_by'] = $admin->id;
            }

            $pr->update($update);

            // Kurangi stok product
            Product::where('id', $pr->product_id)->decrement('stock', (int) $pr->qty);

            // Stock log OUT
            StockLog::create([
                'product_id' => $pr->product_id,
                'type'       => 'out',
                'qty'        => (int) $pr->qty,
                'note'       => "OUT dari product_request #{$pr->id}",
                'created_by' => $admin->id,
            ]);

            return $pr;
        });

        $updated->load([
            'user:id,name,email',
            'product:id,sku,name,stock,unit,status',
            'processedBy:id,name,email',
        ]);

        $updated->file_url = $updated->file_path ? asset('storage/' . $updated->file_path) : null;

        return response()->json([
            'success' => true,
            'message' => 'Product request berhasil di-approve. Stok telah dikurangi.',
            'data'    => $updated,
        ]);
    }

    /**
     * PUT /api/admin/product-requests/{id}/reject
     * body optional:
     * { "reason": "alasan" }
     */
    public function reject(Request $request, $id)
    {
        $admin = $request->user();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $pr = ProductRequest::find($id);
        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Product request tidak ditemukan.',
            ], 404);
        }

        if ($pr->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa reject karena status bukan pending.',
            ], 422);
        }

        $update = [
            'status' => 'rejected',
        ];

        if (array_key_exists('processed_by', $pr->getAttributes())) {
            $update['processed_by'] = $admin->id;
        }

        // Jika tabel punya kolom reason (opsional)
        if (array_key_exists('reason', $pr->getAttributes())) {
            $update['reason'] = $data['reason'] ?? null;
        }

        $pr->update($update);

        $pr->load([
            'user:id,name,email',
            'product:id,sku,name,stock,unit,status',
            'processedBy:id,name,email',
        ]);

        $pr->file_url = $pr->file_path ? asset('storage/' . $pr->file_path) : null;

        return response()->json([
            'success' => true,
            'message' => 'Product request berhasil di-reject.',
            'data'    => $pr,
        ]);
    }

}
