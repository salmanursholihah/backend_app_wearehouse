<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductApprovalWebController extends Controller
{
    /// ⭐ LIST REQUEST PENDING
    public function index()
    {
        $requests = ProductRequest::with(['product', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('super_admin.product_requests.index', compact('requests'));
    }

    /// ⭐ APPROVE REQUEST
    public function approve($id)
    {
        try {

            DB::transaction(function () use ($id) {

                $request = ProductRequest::lockForUpdate()
                    ->with('product')
                    ->findOrFail($id);

                if ($request->status !== 'pending') {
                    throw new \Exception("Request sudah diproses");
                }

                $product = $request->product;

                if (!$product) {
                    throw new \Exception("Product tidak ditemukan");
                }

                if ($product->stock < $request->qty) {
                    throw new \Exception("Stock tidak cukup");
                }

                /// 🔥 Kurangi Stock
                $product->decrement('stock', $request->qty);

                /// Update Request
                $request->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
            });

            return redirect()->back()->with('success', 'Request berhasil di approve & stock berkurang');

        } catch (\Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /// ⭐ REJECT REQUEST
    public function reject($id)
    {
        try {

            $request = ProductRequest::findOrFail($id);

            if ($request->status !== 'pending') {
                throw new \Exception("Request sudah diproses");
            }

            $request->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            return redirect()->back()->with('success', 'Request berhasil di reject');

        } catch (\Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
