<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductRequestApprovalController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST ALL REQUEST (ADMIN VIEW)
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $requests = ProductRequest::with(['product', 'user'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE REQUEST
    |--------------------------------------------------------------------------
    */
    public function approve($id)
    {
        DB::beginTransaction();

        try {

            $requestData = ProductRequest::where('id', $id)
                ->where('status', 'pending')
                ->first();

            if (!$requestData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request tidak ditemukan atau sudah diproses'
                ], 404);
            }

            $product = Product::find($requestData->product_id);

            if ($requestData->qty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock tidak mencukupi'
                ], 400);
            }

            // Kurangi stock
            $product->decrement('stock', $requestData->qty);

            // Update request
            $requestData->update([
                'status' => 'approved',
                'approved_by' => auth()->id()
            ]);

            // Buat stock log
            StockLog::create([
                'product_id' => $product->id,
                'type' => 'out',
                'qty' => $requestData->qty,
                'reference_type' => 'product_request',
                'reference_id' => $requestData->id,
                'user_id' => auth()->id(),
                'note' => 'Approved product request'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request berhasil di-approve'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT REQUEST
    |--------------------------------------------------------------------------
    */
    public function reject($id)
    {
        $requestData = ProductRequest::where('id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$requestData) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan atau sudah diproses'
            ], 404);
        }

        $requestData->update([
            'status' => 'rejected',
            'approved_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil di-reject'
        ]);
    }
}
