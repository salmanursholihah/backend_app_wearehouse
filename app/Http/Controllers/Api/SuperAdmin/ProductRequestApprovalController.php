<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductRequest;

class ProductRequestApprovalController extends Controller
{
    public function index()
    {
        return ProductRequest::with(['product', 'user'])
            ->latest()
            ->get();
    }

    public function approve($id)
    {
        $request = ProductRequest::findOrFail($id);

        if ($request->status !== 'pending') {
            return response()->json(['message' => 'Already processed'], 422);
        }

        $product = $request->product;

        if ($product->stock < $request->qty) {
            return response()->json(['message' => 'Stock tidak cukup'], 422);
        }

        // Kurangi stock
        $product->decrement('stock', $request->qty);

        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

        return response()->json(['message' => 'Approved']);
    }

    public function reject($id)
    {
        $request = ProductRequest::findOrFail($id);

        $request->update([
            'status' => 'rejected',
            'approved_by' => auth()->id()
        ]);

        return response()->json(['message' => 'Rejected']);
    }
}
