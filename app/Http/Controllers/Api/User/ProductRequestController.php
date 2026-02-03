<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'purpose' => 'required|in:maintenance,distributor'
        ]);

        $product = Product::findOrFail($request->product_id);

        if($product->stock < $request->qty){
            return response()->json([
                'message' => 'Stock tidak cukup'
            ],422);
        }

        $data = ProductRequest::create([
            'product_id' => $request->product_id,
            'user_id' => auth()->id(),
            'qty' => $request->qty,
            'purpose' => $request->purpose,
            'status' => 'pending'
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$data->load('product')
        ]);
    }

    public function myRequests()
    {
        return ProductRequest::with('product')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }
}

