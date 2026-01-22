<?php

// namespace App\Http\Controllers\Web;

// use App\Http\Controllers\Controller;
// use App\Models\Product;
// use App\Models\ActivityLog;
// use Illuminate\Http\Request;

// class ProductWebController extends Controller
// {
//     public function index()
//     {
//         return view('pages.product.index', [
//             'products' => Product::latest()->get()
//         ]);
//     }

//     public function create()
//     {
//         return view('pages.product.create');
//     }

//     public function store(Request $request)
//     {
//         $data = $request->validate([
//             'sku' => 'required|unique:products',
//             'name' => 'required',
//             'stock' => 'required|integer'
//         ]);

//         $data['created_by'] = auth()->id();
//         Product::create($data);

//         ActivityLog::create([
//             'user_id' => auth()->id(),
//             'action' => 'CREATE_PRODUCT',
//             'description' => 'Tambah produk '.$data['name']
//         ]);

//         return redirect()->route('products.index')->with('success','Produk dibuat');
//     }

//     public function edit(Product $product)
//     {
//         return view('pages.product.edit', compact('product'));
//     }

//     public function update(Request $request, Product $product)
//     {
//         $product->update($request->all());

//         ActivityLog::create([
//             'user_id' => auth()->id(),
//             'action' => 'UPDATE_PRODUCT',
//             'description' => 'Update produk '.$product->name
//         ]);

//         return back()->with('success','Produk diperbarui');
//     }

//     public function destroy(Product $product)
//     {
//         $product->delete();

//         ActivityLog::create([
//             'user_id' => auth()->id(),
//             'action' => 'DELETE_PRODUCT',
//             'description' => 'Hapus produk'
//         ]);

//         return back()->with('success','Produk dihapus');
//     }
// }


namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function index()
    {
        $this->adminOnly();
        return view('pages.product.index', [
            'products' => Product::latest()->get()
        ]);
    }

    public function create()
    {
        $this->adminOnly();
        return view('pages.product.create');
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $request->validate([
            'sku'   => 'required|unique:products',
            'name'  => 'required',
            'stock' => 'required|integer|min:0'
        ]);

        Product::create([
            'sku' => $request->sku,
            'name'=> $request->name,
            'stock'=>$request->stock,
            'unit'=>$request->unit,
            'created_by'=>auth()->id()
        ]);

        return redirect()->route('product.index')->with('success','Produk ditambahkan');
    }

    public function edit(Product $product)
    {
        $this->adminOnly();
        return view('pages.product.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $this->adminOnly();
        $product->update($request->only('name','stock','unit'));
        return back()->with('success','Produk diperbarui');
    }

    public function destroy(Product $product)
    {
        $this->adminOnly();
        $product->delete();
        return back()->with('success','Produk dihapus');
    }

    private function adminOnly()
    {
        abort_if(!in_array(auth()->user()->role,['admin','super_admin']),403);
    }
}
