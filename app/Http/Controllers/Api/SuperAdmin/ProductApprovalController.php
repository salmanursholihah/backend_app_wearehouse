<?php
namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Product;

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
        Product::findOrFail($id)->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Product approved']);
    }

    public function reject($id)
    {
        Product::findOrFail($id)->update([
            'status' => 'rejected'
        ]);

        return response()->json(['message' => 'Product rejected']);
    }
}
