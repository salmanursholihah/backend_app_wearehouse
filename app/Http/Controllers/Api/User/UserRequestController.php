<?php

// namespace App\Http\Controllers\Api\User;

// use App\Http\Controllers\Controller;
// use App\Models\Product;
// use App\Models\Request as RequestModel;
// use App\Models\RequestItem;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Validation\ValidationException;

// class UserRequestController extends Controller
// {
//     /**
//      * POST /api/user/requests
//      * Create request + items
//      *
//      * body (pakai NAMA product):
//      * {
//      *   "note": "opsional",
//      *   "items": [
//      *     {"product_name": "Filter Air", "qty": 5},
//      *     {"product_name": "Pompa", "qty": 1}
//      *   ]
//      * }
//      *
//      * DB akan simpan ke request_items: product_id + qty (sesuai migration)
//      */
//     public function store(Request $request)
//     {
//         $user = $request->user();

//         $data = $request->validate([
//             'note' => ['nullable', 'string', 'max:500'],
//             'items' => ['required', 'array', 'min:1'],
//             'items.*.product_name' => ['required', 'string', 'max:255'],
//             'items.*.qty'          => ['required', 'integer', 'min:1'],
//         ]);

//         // Normalisasi nama (trim + lowercase) untuk cek duplikat
//         $namesNormalized = collect($data['items'])
//             ->pluck('product_name')
//             ->map(fn($n) => trim(mb_strtolower($n)));

//         // Cegah nama produk duplikat di payload
//         if ($namesNormalized->count() !== $namesNormalized->unique()->count()) {
//             throw ValidationException::withMessages([
//                 'items' => ['Terdapat product_name yang duplikat. Gabungkan qty dalam 1 item.'],
//             ]);
//         }

//         // Ambil list nama asli yang sudah di-trim (untuk query whereIn)
//         $namesTrimmed = collect($data['items'])
//             ->pluck('product_name')
//             ->map(fn($n) => trim($n))
//             ->values()
//             ->all();

//         /**
//          * Lookup products by name (approved only)
//          * Catatan: kolom name tidak unique di migration,
//          * jadi kalau ada nama yang sama lebih dari 1 record -> kita tolak (ambigu).
//          */
//         $products = Product::query()
//             ->whereIn('name', $namesTrimmed)
//             ->where('status', 'approved')
//             ->get(['id', 'name', 'stock', 'status']);

//         // Group berdasarkan lowercase name untuk deteksi duplikat di DB
//         $grouped = $products->groupBy(fn($p) => mb_strtolower(trim($p->name)));

//         // Validasi: semua product_name harus ketemu tepat 1 produk approved
//         $notFound = [];
//         $ambiguous = [];

//         foreach ($namesTrimmed as $name) {
//             $key = mb_strtolower(trim($name));
//             $match = $grouped->get($key);

//             if (!$match || $match->count() === 0) {
//                 $notFound[] = $name;
//             } elseif ($match->count() > 1) {
//                 $ambiguous[] = $name;
//             }
//         }

//         if (!empty($notFound)) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Produk tidak ditemukan / belum approved: ' . implode(', ', $notFound),
//             ], 422);
//         }

//         if (!empty($ambiguous)) {
//             return response()->json([
//                 'success' => false,
//                 'message' =>
//                 'Nama produk ambigu (lebih dari 1 produk dengan nama sama): ' .
//                     implode(', ', $ambiguous) .
//                     '. Solusi: buat nama produk unik atau gunakan SKU di request.',
//             ], 422);
//         }

//         // OPTIONAL: validasi stok (kalau kamu mau blok request melebihi stok)
//         foreach ($data['items'] as $item) {
//             $key = mb_strtolower(trim($item['product_name']));
//             $p = $grouped->get($key)->first();

//             if ((int) $p->stock < (int) $item['qty']) {
//                 return response()->json([
//                     'success' => false,
//                     'message' => "Stok produk '{$p->name}' tidak cukup. Stok: {$p->stock}, diminta: {$item['qty']}.",
//                 ], 422);
//             }
//         }

//         $created = DB::transaction(function () use ($user, $data, $grouped) {
//             $req = RequestModel::create([
//                 'user_id' => $user->id,
//                 'status'  => 'pending',
//                 'note'    => $data['note'] ?? null,
//             ]);

//             $itemsPayload = collect($data['items'])->map(function ($item) use ($req, $grouped) {
//                 $key = mb_strtolower(trim($item['product_name']));
//                 $product = $grouped->get($key)->first();

//                 return [
//                     'request_id' => $req->id,
//                     'product_id' => (int) $product->id,
//                     'qty'        => (int) $item['qty'],
//                     'created_at' => now(),
//                     'updated_at' => now(),
//                 ];
//             })->toArray();

//             RequestItem::insert($itemsPayload);

//             return $req;
//         });

//         // Load detail items + product
//         $created->load([
//             'items.product:id,sku,name,stock,unit,status',
//             'processedBy:id,name,email',
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Request berhasil dibuat.',
//             'data'    => $created,
//         ], 201);
//     }

//     /**
//      * GET /api/user/requests
//      */
//     public function index(Request $request)
//     {
//         $user = $request->user();
//         $perPage = (int) $request->query('per_page', 10);

//         $requests = RequestModel::where('user_id', $user->id)
//             ->withCount('items')
//             ->orderBy('created_at', 'desc')
//             ->paginate($perPage);

//         return response()->json([
//             'success' => true,
//             'data'    => $requests,
//         ]);
//     }

//     /**
//      * GET /api/user/requests/{id}
//      */
//     public function show(Request $request, $id)
//     {
//         $user = $request->user();

//         $req = RequestModel::where('user_id', $user->id)
//             ->with([
//                 'items.product:id,sku,name,stock,unit,status',
//                 'processedBy:id,name,email',
//             ])
//             ->find($id);

//         if (!$req) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Request tidak ditemukan.',
//             ], 404);
//         }

//         return response()->json([
//             'success' => true,
//             'data'    => $req,
//         ]);
//     }

//     /**
//      * PUT /api/user/requests/{id}/cancel
//      */
//     public function cancel(Request $request, $id)
//     {
//         $user = $request->user();

//         $req = RequestModel::where('user_id', $user->id)->find($id);

//         if (!$req) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Request tidak ditemukan.',
//             ], 404);
//         }

//         if ($req->status !== 'pending') {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Request tidak bisa dibatalkan karena status bukan pending.',
//             ], 422);
//         }

//         $req->update(['status' => 'cancelled']); // kalau migration status tidak punya cancelled, hapus baris ini & ganti flow
//         // NOTE: di migration status requests hanya pending/approved/rejected/taken.
//         // Kalau kamu belum punya 'cancelled', sebaiknya:
//         // - tambah enum cancelled di migration, atau
//         // - jadikan cancel = delete request (pilih salah satu).

//         return response()->json([
//             'success' => true,
//             'message' => 'Request berhasil dibatalkan.',
//             'data'    => $req,
//         ]);
//     }

//     /**
//      * PUT /api/user/requests/{id}/confirm-taken
//      */
//     public function confirmTaken(Request $request, $id)
//     {
//         $user = $request->user();

//         $req = RequestModel::where('user_id', $user->id)->find($id);

//         if (!$req) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Request tidak ditemukan.',
//             ], 404);
//         }

//         if ($req->status !== 'approved') {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Tidak bisa konfirmasi diambil karena status belum approved.',
//             ], 422);
//         }

//         $req->update(['status' => 'taken']);

//         return response()->json([
//             'success' => true,
//             'message' => 'Berhasil konfirmasi barang diambil.',
//             'data'    => $req,
//         ]);
//     }
// }


//code 2

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Request as RequestModel; // alias
use App\Models\RequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserRequestController extends Controller
{
    /**
     * POST /api/user/requests
     * body (pakai NAMA product):
     * {
     *   "note": "opsional",
     *   "items": [
     *     {"product_name": "Filter Air", "qty": 5},
     *     {"product_name": "Pompa", "qty": 1}
     *   ]
     * }
     *
     * DB simpan ke request_items: product_id + qty
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.qty'          => ['required', 'integer', 'min:1'],
        ]);

        // Cegah duplikat nama produk dalam payload (case-insensitive)
        $namesNormalized = collect($data['items'])
            ->pluck('product_name')
            ->map(fn($n) => trim(mb_strtolower($n)));

        if ($namesNormalized->count() !== $namesNormalized->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => ['Terdapat product_name yang duplikat. Gabungkan qty dalam 1 item.'],
            ]);
        }

        $namesTrimmed = collect($data['items'])
            ->pluck('product_name')
            ->map(fn($n) => trim($n))
            ->values()
            ->all();

        // Ambil products approved berdasarkan name
        $products = Product::query()
            ->whereIn('name', $namesTrimmed)
            ->where('status', 'approved')
            ->get(['id', 'name', 'stock', 'status']);

        $grouped = $products->groupBy(fn($p) => mb_strtolower(trim($p->name)));

        // Validasi: semua nama harus ketemu tepat 1 product
        $notFound = [];
        $ambiguous = [];

        foreach ($namesTrimmed as $name) {
            $key = mb_strtolower(trim($name));
            $match = $grouped->get($key);

            if (!$match || $match->count() === 0) $notFound[] = $name;
            elseif ($match->count() > 1) $ambiguous[] = $name;
        }

        if (!empty($notFound)) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan / belum approved: ' . implode(', ', $notFound),
            ], 422);
        }

        if (!empty($ambiguous)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama produk ambigu (lebih dari 1). Buat nama unik atau gunakan SKU.',
            ], 422);
        }

        // OPTIONAL: validasi stok cukup
        foreach ($data['items'] as $item) {
            $key = mb_strtolower(trim($item['product_name']));
            $p = $grouped->get($key)->first();
            if ((int)$p->stock < (int)$item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok '{$p->name}' tidak cukup. Stok: {$p->stock}, diminta: {$item['qty']}.",
                ], 422);
            }
        }

        $created = DB::transaction(function () use ($user, $data, $grouped) {
            $req = RequestModel::create([
                'user_id' => $user->id,
                'status'  => 'pending',
                'note'    => $data['note'] ?? null,
            ]);

            $itemsPayload = collect($data['items'])->map(function ($item) use ($req, $grouped) {
                $key = mb_strtolower(trim($item['product_name']));
                $product = $grouped->get($key)->first();

                return [
                    'request_id' => $req->id,
                    'product_id' => (int) $product->id,
                    'qty'        => (int) $item['qty'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            RequestItem::insert($itemsPayload);

            return $req;
        });

        $created->load([
            'items.product:id,sku,name,stock,unit,status',
            'processedBy:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil dibuat.',
            'data'    => $created,
        ], 201);
    }

    /**
     * GET /api/user/requests
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 10);

        $requests = RequestModel::where('user_id', $user->id)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $requests,
        ]);
    }

    /**
     * GET /api/user/requests/{id}
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $req = RequestModel::where('user_id', $user->id)
            ->with([
                'items.product:id,sku,name,stock,unit,status',
                'processedBy:id,name,email',
            ])
            ->find($id);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $req,
        ]);
    }

    /**
     * PUT /api/user/requests/{id}/cancel
     * Aman untuk migration yang tidak punya status cancelled:
     * - hanya boleh jika pending
     * - kita DELETE header + items (cascade jika foreign key cascadeOnDelete)
     */
    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $req = RequestModel::where('user_id', $user->id)->with('items')->find($id);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        if ($req->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak bisa dibatalkan karena status bukan pending.',
            ], 422);
        }

        DB::transaction(function () use ($req) {
            // kalau tidak cascade, hapus items dulu
            RequestItem::where('request_id', $req->id)->delete();
            $req->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil dibatalkan (dihapus).',
        ]);
    }

    /**
     * PUT /api/user/requests/{id}/confirm-taken
     */
    public function confirmTaken(Request $request, $id)
    {
        $user = $request->user();

        $req = RequestModel::where('user_id', $user->id)->find($id);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request tidak ditemukan.',
            ], 404);
        }

        if ($req->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa konfirmasi diambil karena status belum approved.',
            ], 422);
        }

        $req->update(['status' => 'taken']);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil konfirmasi barang diambil.',
            'data'    => $req,
        ]);
    }
}
