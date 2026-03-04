<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRequestController extends Controller
{

    /**
     * GET /api/admin/requests
     * Optional query:
     * - status=pending|approved|rejected|taken
     * - search=keyword (user name/email)
     * - per_page=10
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $status  = $request->query('status');
        $search  = $request->query('search');

        $q = Request::query()
            ->with([
                'user:id,name,email',
                'processedBy:id,name,email',
            ])
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $q->where('status', $status);
        }

        if ($search) {
            $q->whereHas('user', function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $requests = $q->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * GET /api/admin/requests/{id}
     * Detail request + items + products
     */
    public function show($id)
    {
        $req = Request::with([
            'user:id,name,email,phone,address',
            'processedBy:id,name,email',
            'items.product:id,sku,name,stock,unit,status',
        ])->find($id);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $req,
        ]);
    }

    /**
     * PUT /api/admin/requests/{id}/approve
     * Approve request:
     * - hanya boleh jika pending
     * - cek stok cukup
     * - kurangi stock product
     * - insert stock_logs type out
     * - set processed_by
     */
    public function approve(Request $request, $id)
    {
        $admin = $request->user();

        $req = Request::with(['items'])->find($id);
        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        if ($req->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak bisa di-approve karena status bukan pending.',
            ], 422);
        }

        // Ambil semua product terkait
        $productIds = $req->items->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get(['id', 'name', 'stock']);

        if ($products->count() !== $productIds->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Ada produk pada request yang sudah tidak tersedia.',
            ], 422);
        }

        // Cek stok cukup untuk setiap item
        foreach ($req->items as $item) {
            $p = $products->firstWhere('id', (int)$item->product_id);
            if (!$p) continue;

            if ((int)$p->stock < (int)$item->qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok '{$p->name}' tidak cukup. Stok: {$p->stock}, diminta: {$item->qty}.",
                ], 422);
            }
        }

        $updated = DB::transaction(function () use ($req, $admin, $products) {
            // Update header request
            $req->update([
                'status' => 'approved',
                'processed_by' => $admin->id,
            ]);

            // Kurangi stok + buat log OUT
            foreach ($req->items as $item) {
                $p = $products->firstWhere('id', (int)$item->product_id);
                if (!$p) continue;

                // Kurangi stock
                Product::where('id', $p->id)->decrement('stock', (int)$item->qty);

                // Insert stock_logs
                StockLog::create([
                    'product_id' => $p->id,
                    'type'       => 'out',
                    'qty'        => (int) $item->qty,
                    'note'       => "OUT dari request #{$req->id}",
                    'created_by' => $admin->id,
                ]);
            }

            return $req;
        });

        $updated->load([
            'user:id,name,email',
            'processedBy:id,name,email',
            'items.product:id,sku,name,stock,unit,status',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil di-approve. Stok telah dikurangi.',
            'data' => $updated,
        ]);
    }

    /**
     * PUT /api/admin/requests/{id}/reject
     * body optional:
     * { "reason": "stok habis / tidak valid" }
     */
    public function reject(Request $request, $id)
    {
        $admin = $request->user();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $req = Request::find($id);
        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        if ($req->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak bisa di-reject karena status bukan pending.',
            ], 422);
        }

        // Update field reason hanya jika kolomnya ada
        $update = [
            'status' => 'rejected',
            'processed_by' => $admin->id,
        ];

        // aman: set reason hanya kalau attribute ada di model fillable & kolom ada
        if (array_key_exists('reason', $req->getAttributes())) {
            $update['reason'] = $data['reason'] ?? null;
        }

        $req->update($update);

        $req->load(['user:id,name,email', 'processedBy:id,name,email', 'items.product:id,sku,name,stock,unit,status']);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil di-reject.',
            'data' => $req,
        ]);
    }

    /**
     * PUT /api/admin/requests/{id}/taken
     * Mark taken: hanya jika approved
     */
    public function markTaken(Request $request, $id)
    {
        $admin = $request->user();

        $req = Request::find($id);
        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        if ($req->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa set taken karena status bukan approved.',
            ], 422);
        }

        $req->update([
            'status' => 'taken',
            'processed_by' => $admin->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil ditandai sebagai taken.',
            'data' => $req,
        ]);
    }

}
