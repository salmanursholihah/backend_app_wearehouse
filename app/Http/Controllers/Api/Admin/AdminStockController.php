<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStockController extends Controller
{

    /**
     * GET /api/user/stock-logs?per_page=10&search=skuOrName&type=in|out&product_id=1
     */
    public function logs(Request $request)
    {
        $perPage   = (int) $request->query('per_page', 10);
        $perPage   = ($perPage > 0 && $perPage <= 50) ? $perPage : 10;

        $search    = $request->query('search');      // sku / name
        $type      = $request->query('type');        // in|out (opsional)
        $productId = $request->query('product_id');  // opsional

        $q = StockLog::query()
            ->with([
                'product:id,sku,name,stock,unit',
                'user:id,name,email',
            ])
            ->latest();

        if ($type) {
            $q->where('type', $type); // pastikan kolomnya ada
        }

        if ($productId) {
            $q->where('product_id', $productId);
        }

        if ($search) {
            $q->whereHas('product', function ($qq) use ($search) {
                $qq->where('sku', 'like', "%{$search}%")
                   ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $logs = $q->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * GET /api/user/stock-logs/{id}
     */
    public function show($id)
    {
        $log = StockLog::with([
            'product:id,sku,name,stock,unit',
            'user:id,name,email',
        ])->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Stock log tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    /**
     * POST /api/user/stock/in
     * body:
     * {
     *   "product_id": 1,
     *   "qty": 10,
     *   "note": "restock dari supplier (opsional)"
     * }
     */
    public function stockIn(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty'        => ['required', 'integer', 'min:1'],
            'note'       => ['nullable', 'string', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($data, $user) {
            // lock row product biar aman dari race condition
            $product = Product::where('id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                abort(404, 'Produk tidak ditemukan.');
            }

            $before = (int) $product->stock;
            $after  = $before + (int) $data['qty'];

            $product->update([
                'stock' => $after,
            ]);

            // Catat log (sesuaikan kolom sesuai migration kamu)
            $log = StockLog::create([
                'product_id'   => $product->id,
                'user_id'      => $user->id,
                'type'         => 'in',              // in|out
                'qty'          => (int) $data['qty'],
                'before_stock' => $before,           // kalau migration kamu beda, rename
                'after_stock'  => $after,
                'note'         => $data['note'] ?? null,
            ]);

            $log->load(['product:id,sku,name,stock,unit', 'user:id,name,email']);

            return [
                'product' => $product->only(['id', 'sku', 'name', 'stock', 'unit']),
                'log'     => $log,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Stock in berhasil.',
            'data' => $result,
        ], 201);
    }

    /**
     * POST /api/user/stock/out
     * body:
     * {
     *   "product_id": 1,
     *   "qty": 3,
     *   "note": "barang keluar untuk pengajuan user (opsional)"
     * }
     */
    public function stockOut(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty'        => ['required', 'integer', 'min:1'],
            'note'       => ['nullable', 'string', 'max:500'],
        ]);

        $result = DB::transaction(function () use ($data, $user) {
            $product = Product::where('id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            if (!$product) {
                abort(404, 'Produk tidak ditemukan.');
            }

            $before = (int) $product->stock;
            $qty    = (int) $data['qty'];

            if ($before < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock tidak cukup. Stock sekarang: {$before}",
                ], 422);
            }

            $after = $before - $qty;

            $product->update([
                'stock' => $after,
            ]);

            $log = StockLog::create([
                'product_id'   => $product->id,
                'user_id'      => $user->id,
                'type'         => 'out',
                'qty'          => $qty,
                'before_stock' => $before,
                'after_stock'  => $after,
                'note'         => $data['note'] ?? null,
            ]);

            $log->load(['product:id,sku,name,stock,unit', 'user:id,name,email']);

            return [
                'product' => $product->only(['id', 'sku', 'name', 'stock', 'unit']),
                'log'     => $log,
            ];
        });

        // kalau di transaction tadi return response (stok tidak cukup), langsung balikin
        if ($result instanceof \Illuminate\Http\JsonResponse) return $result;

        return response()->json([
            'success' => true,
            'message' => 'Stock out berhasil.',
            'data' => $result,
        ], 201);
    }
}

