<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;

class AboutUsController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'About us fetched successfully',
            'data' => AboutUs::first(),
        ]);
    }
}
