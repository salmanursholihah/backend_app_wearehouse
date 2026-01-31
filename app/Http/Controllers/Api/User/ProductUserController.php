<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductUserController extends Controller
{


    public function inventory()
    {
        return response()->json([
            'success'=> true,
            'data'=> Product::where('status', 'approved')->get()
        ]);
    }
}
