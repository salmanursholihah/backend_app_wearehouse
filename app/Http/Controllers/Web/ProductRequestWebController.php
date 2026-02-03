<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductRequestWebController extends Controller
{
    public function index()
    {
        return view('pages.request.index',[
            'requests'=> ProductRequest::with(['product','user'])->latest()->get()
        ]);
    }

    public function approve(ProductRequest $request)
    {
        $request->product->decrement('stock',$request->qty);

        $request->update([
            'status'=>'approved',
            'approved_by'=>auth()->id()
        ]);

        return back()->with('success','Approved');
    }

    public function reject(ProductRequest $request)
    {
        $request->update([
            'status'=>'rejected',
            'approved_by'=>auth()->id()
        ]);

        return back()->with('success','Rejected');
    }
}

