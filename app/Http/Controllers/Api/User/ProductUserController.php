<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductUserController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Product::with('images')
                ->where('status', 'approved')
                ->latest()
                ->get()
        ]);    }
}
