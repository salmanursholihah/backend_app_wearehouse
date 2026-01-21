<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;   // ✅ WAJIB ADA

// use App\Models\Request as ItemRequest;
// use App\Models\RequestItem;
// use App\Models\Product;
// use App\Models\StockLog;
// use Illuminate\Http\Request;

// class RequestController extends Controller
// {
//     // USER → buat pengajuan
//     public function store(Request $request)
//     {
//         $req = ItemRequest::create([
//             'user_id' => auth()->id(),
//             'note' => $request->note
//         ]);

//         foreach ($request->items as $item) {
//             RequestItem::create([
//                 'request_id' => $req->id,
//                 'product_id' => $item['product_id'],
//                 'qty' => $item['qty']
//             ]);
//         }

//         return response()->json(
//             $req->load('items')
//         );
//     }

//     // ADMIN / SUPER ADMIN → proses pengajuan
//     public function process(Request $request, $id)
//     {
//         $req = ItemRequest::with('items')->findOrFail($id);

//         $req->update([
//             'status' => $request->status,
//             'processed_by' => auth()->id()
//         ]);

//         // Jika approve → potong stok
//         if ($request->status == 'approved') {

//             foreach ($req->items as $item) {

//                 $product = Product::find($item->product_id);

//                 $product->stock -= $item->qty;
//                 $product->save();

//                 StockLog::create([
//                     'product_id' => $product->id,
//                     'type' => 'out',
//                     'qty' => $item->qty,
//                     'reference_type' => 'request',
//                     'reference_id' => $req->id,
//                     'user_id' => auth()->id()
//                 ]);
//             }
//         }

//         return response()->json($req);
//     }

//     // HISTORY SESUAI ROLE
//     public function index()
//     {
//         if (auth()->user()->role == 'user') {
//             return ItemRequest::where('user_id', auth()->id())
//                 ->with('items')
//                 ->get();
//         }

//         return ItemRequest::with('items')->get();
//     }
// }


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Request as ItemRequest;
// use App\Models\RequestItem;
// use App\Models\Product;
// use App\Models\StockLog;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;
// use App\Models\LogActivity;


// class RequestController extends Controller
// {
//     /**
//      * USER → buat request barang
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'items' => 'required|array|min:1',
//             'items.*.product_id' => 'required|exists:products,id',
//             'items.*.qty' => 'required|integer|min:1',
//         ]);

//         DB::transaction(function () use ($request) {
//             $req = ItemRequest::create([
//                 'user_id' => Auth::id(),
//                 'status' => 'pending'
//             ]);

//             foreach ($request->items as $item) {
//                 RequestItem::create([
//                     'request_id' => $req->id,
//                     'product_id' => $item['product_id'],
//                     'qty' => $item['qty']
//                 ]);
//             }

//             LogActivity::create([
//                 'user_id' => Auth::id(),
//                 'action' => 'create_request',
//                 'description' => 'User membuat pengajuan barang'
//             ]);
//         });

//         return response()->json([
//             'message' => 'Request berhasil dikirim'
//         ], 201);
//     }

//     /**
//      * ADMIN → approve request
//      */
//     public function approve($id)
//     {
//         $this->checkAdmin();

//         DB::transaction(function () use ($id) {
//             $request = ItemRequest::with('items.product')->findOrFail($id);

//             if ($request->status !== 'pending') {
//                 throw new \Exception('Request sudah diproses');
//             }

//             // VALIDASI STOK
//             foreach ($request->items as $item) {
//                 if ($item->product->stock < $item->qty) {
//                     throw new \Exception(
//                         "Stok {$item->product->name} tidak cukup"
//                     );
//                 }
//             }

//             // POTONG STOK + LOG
//             foreach ($request->items as $item) {
//                 $item->product->decrement('stock', $item->qty);

//                 StockLog::create([
//                     'product_id' => $item->product_id,
//                     'type' => 'out',
//                     'qty' => $item->qty,
//                     'reference_type' => 'request',
//                     'reference_id' => $request->id,
//                     'user_id' => Auth::id(),
//                     'note' => 'Approve request'
//                 ]);
//             }

//             $request->update([
//                 'status' => 'approved',
//                 'processed_by' => Auth::id()
//             ]);

//             Logactifit::create([
//                 'user_id' => Auth::id(),
//                 'action' => 'approve_request',
//                 'description' => 'Admin menyetujui request #' . $request->id
//             ]);
//         });

//         return response()->json(['message' => 'Request approved']);
//     }

//     /**
//      * ADMIN → reject request
//      */
//     public function reject($id)
//     {
//         $this->checkAdmin();

//         $request = ItemRequest::findOrFail($id);

//         $request->update([
//             'status' => 'rejected',
//             'processed_by' => Auth::id()
//         ]);

//         Logactifit::create([
//             'user_id' => Auth::id(),
//             'action' => 'reject_request',
//             'description' => 'Admin menolak request #' . $request->id
//         ]);

//         return response()->json(['message' => 'Request rejected']);
//     }

//     private function checkAdmin()
//     {
//         if (!in_array(Auth::user()->role, ['admin','super_admin'])) {
//             abort(403, 'Unauthorized');
//         }
//     }
// }





// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Models\Request as ItemRequest;
// use App\Models\RequestItem;
// use App\Models\Product;
// use App\Models\StockLog;
// use App\Models\ActivityLog;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\DB;

// class RequestController extends Controller
// {
//     /**
//      * =========================
//      * USER → BUAT REQUEST BARANG
//      * =========================
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'items'              => 'required|array|min:1',
//             'items.*.product_id' => 'required|exists:products,id',
//             'items.*.qty'        => 'required|integer|min:1',
//             'note'               => 'nullable|string'
//         ]);

//         DB::transaction(function () use ($request) {

//             $req = ItemRequest::create([
//                 'user_id' => Auth::id(),
//                 'status'  => 'pending',
//                 'note'    => $request->note
//             ]);

//             foreach ($request->items as $item) {
//                 RequestItem::create([
//                     'request_id' => $req->id,
//                     'product_id' => $item['product_id'],
//                     'qty'        => $item['qty']
//                 ]);
//             }

//             ActivityLog::create([
//                 'user_id'     => Auth::id(),
//                 'action'      => 'create_request',
//                 'description' => 'User membuat request barang #' . $req->id
//             ]);
//         });

//         return response()->json([
//             'message' => 'Request berhasil dikirim'
//         ], 201);
//     }

//     /**
//      * =========================
//      * ADMIN / SUPER ADMIN → APPROVE
//      * =========================
//      */
//     public function approve($id)
//     {
//         $this->checkAdmin();

//         try {
//             DB::transaction(function () use ($id) {

//                 $request = ItemRequest::with('items.product')->findOrFail($id);

//                 if ($request->status !== 'pending') {
//                     abort(400, 'Request sudah diproses');
//                 }

//                 // CEK STOK
//                 foreach ($request->items as $item) {
//                     if ($item->product->stock < $item->qty) {
//                         abort(400, "Stok {$item->product->name} tidak cukup");
//                     }
//                 }

//                 // POTONG STOK
//                 foreach ($request->items as $item) {
//                     $item->product->decrement('stock', $item->qty);

//                     StockLog::create([
//                         'product_id'     => $item->product_id,
//                         'type'           => 'out',
//                         'qty'            => $item->qty,
//                         'reference_type' => 'request',
//                         'reference_id'   => $request->id,
//                         'user_id'        => Auth::id(),
//                         'note'           => 'Approve request'
//                     ]);
//                 }

//                 $request->update([
//                     'status'       => 'approved',
//                     'processed_by' => Auth::id()
//                 ]);

//                 ActivityLog::create([
//                     'user_id'     => Auth::id(),
//                     'action'      => 'approve_request',
//                     'description' => 'Approve request #' . $request->id
//                 ]);
//             });

//             return response()->json([
//                 'message' => 'Request berhasil di-approve'
//             ]);

//         } catch (\Throwable $e) {
//             return response()->json([
//                 'message' => $e->getMessage()
//             ], 400);
//         }
//     }

//     /**
//      * =========================
//      * ADMIN / SUPER ADMIN → REJECT
//      * =========================
//      */
//     public function reject(Request $request, $id)
//     {
//         $this->checkAdmin();

//         $req = ItemRequest::findOrFail($id);

//         if ($req->status !== 'pending') {
//             return response()->json([
//                 'message' => 'Request sudah diproses'
//             ], 400);
//         }

//         $req->update([
//             'status'       => 'rejected',
//             'processed_by' => Auth::id(),
//             'note'         => $request->note
//         ]);

//         ActivityLog::create([
//             'user_id'     => Auth::id(),
//             'action'      => 'reject_request',
//             'description' => 'Reject request #' . $req->id
//         ]);

//         return response()->json([
//             'message' => 'Request berhasil ditolak'
//         ]);
//     }

//     /**
//      * =========================
//      * LIST REQUEST (ROLE BASED)
//      * =========================
//      */
//     public function index()
//     {
//         if (Auth::user()->role === 'user') {
//             return ItemRequest::where('user_id', Auth::id())
//                 ->with('items.product')
//                 ->latest()
//                 ->get();
//         }

//         return ItemRequest::with('user','items.product')
//             ->latest()
//             ->get();
//     }

//     /**
//      * =========================
//      * ROLE GUARD
//      * =========================
//      */
//     private function checkAdmin()
//     {
//         if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
//             abort(403, 'Unauthorized');
//         }
//     }
// }

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request as ItemRequest;
use App\Models\RequestItem;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // USER
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'exists:products,id',
            'items.*.qty' => 'integer|min:1'
        ]);

        DB::transaction(function () use ($request) {
            $req = ItemRequest::create([
                'user_id' => Auth::id(),
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                RequestItem::create([
                    'request_id' => $req->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty']
                ]);
            }
        });

        return response()->json(['message' => 'Request dikirim'], 201);
    }

    // ADMIN
    public function approve($id)
    {
        $this->checkAdmin();

        DB::transaction(function () use ($id) {
            $request = ItemRequest::with('items.product')->findOrFail($id);

            if ($request->status !== 'pending') {
                abort(400, 'Request sudah diproses');
            }

            foreach ($request->items as $item) {
                if ($item->product->stock < $item->qty) {
                    abort(400, 'Stok tidak cukup');
                }
            }

            foreach ($request->items as $item) {
                $item->product->decrement('stock', $item->qty);

                StockLog::create([
                    'product_id' => $item->product_id,
                    'type' => 'out',
                    'qty' => $item->qty,
                    'reference_type' => 'request',
                    'reference_id' => $request->id,
                    'user_id' => Auth::id()
                ]);
            }

            $request->update([
                'status' => 'approved',
                'processed_by' => Auth::id()
            ]);
        });

        return response()->json(['message' => 'Request approved']);
    }

    public function reject($id)
    {
        $this->checkAdmin();

        ItemRequest::findOrFail($id)->update([
            'status' => 'rejected',
            'processed_by' => Auth::id()
        ]);

        return response()->json(['message' => 'Request rejected']);
    }

    public function index()
    {
        if (Auth::user()->role === 'user') {
            return response()->json(
                ItemRequest::where('user_id', Auth::id())->with('items')->get()
            );
        }

        return response()->json(
            ItemRequest::with('items')->get()
        );
    }

    private function checkAdmin()
    {
        if (!in_array(Auth::user()->role, ['admin','super_admin'])) {
            abort(403);
        }
    }
}
