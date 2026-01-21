<?php

// namespace App\Http\Controllers\Api;
// use App\Models\Product;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Auth;


// class ProductController extends Controller
// {
//     public function index()
//     {
//         return Product::all();
//     }

//     public function store(Request $request)
//     {
//         $this->checkAdmin();

//         $request->validate([
//             'sku' => 'required|unique:products',
//             'name' => 'required',
//             'stock' => 'integer|min:0'
//         ]);

//         Product::create([
//             'sku' => $request->sku,
//             'name' => $request->name,
//             'stock' => $request->stock,
//             'unit' => $request->unit,
//             'created_by' => Auth::id()
//         ]);

//         return response()->json(['message' => 'Product created']);
//     }
//         public function update(Request $request, $id)
//     {
//         $product = Product::findOrFail($id);
//         $product->update($request->all());

//         return response()->json($product);
//     }

//     public function destroy($id)
//     {
//         Product::findOrFail($id)->delete();

//         return response()->json(['message' => 'deleted']);
//     }


//     private function checkAdmin()
//     {
//         if (!in_array(Auth::user()->role, ['admin','super_admin'])) {
//             abort(403);
//         }
//     }
// }


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(
            Product::latest()->get()
        );
    }

    public function store(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'sku' => 'required|unique:products',
            'name' => 'required',
            'stock' => 'integer|min:0'
        ]);

        $product = Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'description' => $request->description,
            'stock' => $request->stock ?? 0,
            'unit' => $request->unit ?? 'pcs',
            'created_by' => Auth::id()
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin();

        $product = Product::findOrFail($id);
        $product->update($request->only([
            'name','description','stock','unit'
        ]));

        return response()->json($product);
    }

    public function destroy($id)
    {
        $this->checkAdmin();

        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    private function checkAdmin()
    {
        if (!in_array(Auth::user()->role, ['admin','super_admin'])) {
            abort(403);
        }
    }
}
