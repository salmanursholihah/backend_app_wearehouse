<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApprovalController extends Controller
{
    public function pending()
    {
        return Product::with('images')
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function approve($id)
    {
        return DB::transaction(function () use ($id) {

            $request = ProductRequest::lockForUpdate()
                ->with('product')
                ->findOrFail($id);

            if ($request->status !== 'pending') {
                return response()->json([
                    'message' => 'Sudah diproses'
                ], 422);
            }

            $product = $request->product;

            if (!$product) {
                return response()->json([
                    'message' => 'Product tidak ditemukan'
                ], 404);
            }

            if ($product->stock < $request->qty) {
                return response()->json([
                    'message' => 'Stock tidak cukup'
                ], 422);
            }

            /// 🔥 Kurangi stock
            $product->stock -= $request->qty;
            $product->save();

            /// Update request
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request Approved & Stock Updated'
            ]);
        });
    }

    public function reject($id)
    {
        $request = ProductRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return response()->json([
                'message' => 'Sudah diproses'
            ], 422);
        }

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
