<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Request as ItemRequest;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestWebController extends Controller
{
    public function index()
    {
        $this->adminOnly();

        return view('pages.request.index', [
            'requests' => ItemRequest::with('user','items.product')->latest()->get()
        ]);
    }

    public function approve($id)
    {
        $this->adminOnly();

        DB::transaction(function () use ($id) {
            $req = ItemRequest::with('items.product')->findOrFail($id);

            foreach ($req->items as $item) {
                if ($item->product->stock < $item->qty) {
                    abort(400,'Stok tidak cukup');
                }

                $item->product->decrement('stock', $item->qty);

                StockLog::create([
                    'product_id'=>$item->product_id,
                    'type'=>'out',
                    'qty'=>$item->qty,
                    'reference_type'=>'request',
                    'reference_id'=>$req->id,
                    'user_id'=>auth()->id()
                ]);
            }

            $req->update([
                'status'=>'approved',
                'processed_by'=>auth()->id()
            ]);
        });

        return back()->with('success','Request disetujui');
    }

    public function reject(Request $request, $id)
    {
        $this->adminOnly();

        ItemRequest::findOrFail($id)->update([
            'status'=>'rejected',
            'processed_by'=>auth()->id(),
            'note'=>$request->note
        ]);

        return back()->with('success','Request ditolak');
    }

    private function adminOnly()
    {
        abort_if(!in_array(auth()->user()->role,['admin','super_admin']),403);
    }
}
