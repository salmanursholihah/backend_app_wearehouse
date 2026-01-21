<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use App\Models\Product;
// use App\Models\Request as WarehouseRequest;

// class DashboardWebController extends Controller
// {
//     public function index()
//     {
//         $this->authorizeSuper();

//         return view('pages.dashboard', [
//             'total_users'      => User::count(),
//             'total_admins'     => User::whereIn('role', ['admin','super_admin'])->count(),
//             'total_products'   => Product::count(),
//             'pending_requests' => WarehouseRequest::where('status', 'pending')->count(),
//             'total_stock'      => Product::sum('stock'),

//             'latest_requests'  => WarehouseRequest::with(['user', 'items.product'])
//                                     ->latest()
//                                     ->take(5)
//                                     ->get(),
//                                     'low_stock_products' => Product::where('stock', '<=', 10)
//                                     ->latest()
//                                     ->take(5)
//                                     ->get(),
//         ]);
//     }

//     private function authorizeSuper()
//     {
//         abort_if(auth()->user()->role !== 'super_admin', 403);
//     }
// }


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Request as ItemRequest;

class DashboardWebController extends Controller
{
    public function index()
    {
        $this->superAdmin();

        return view('pages.dashboard', [
            'total_users'    => User::where('role','user')->count(),
            'total_admins'   => User::whereIn('role',['admin','super_admin'])->count(),
            'total_products' => Product::count(),
            'pending_requests' => ItemRequest::where('status','pending')->count(),
            'total_stock'    => Product::sum('stock'),
            'latest_requests'=> ItemRequest::with('user')->latest()->take(5)->get(),
        ]);
    }

    private function superAdmin()
    {
        abort_if(auth()->user()->role !== 'super_admin', 403);
    }
}
