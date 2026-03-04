<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminAboutController extends Controller
{

    /**
     * GET /api/user/about  (role:user)  -> READ ONLY
     * GET /api/admin/about (role:admin) -> READ
     *
     * List semua about (biasanya 1 record saja)
     */
    public function index(Request $request)
    {
        $data = AboutUs::orderBy('created_at', 'desc')->get();

        // Tambahkan image_url jika ada
        $data->transform(function ($item) {
            $item->image_url = $item->image ? asset('storage/' . $item->image) : null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/user/about/{id}
     */
    public function show(Request $request, $id)
    {
        $about = AboutUs::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'Data About tidak ditemukan.',
            ], 404);
        }

        $about->image_url = $about->image ? asset('storage/' . $about->image) : null;

        return response()->json([
            'success' => true,
            'data' => $about,
        ]);
    }

    /**
     * POST /api/user/about (HARUSNYA ADMIN)
     * Aman: kalau role user akses endpoint ini, kita tolak.
     *
     * body/form-data:
     * - title (required)
     * - content (required)
     * - image (optional file)
     */
    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'image'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('about', 'public');
        }

        $about = AboutUs::create([
            'title'   => $data['title'],
            'content' => $data['content'],
            'image'   => $imagePath,
        ]);

        $about->image_url = $about->image ? asset('storage/' . $about->image) : null;

        return response()->json([
            'success' => true,
            'message' => 'About berhasil dibuat.',
            'data' => $about,
        ], 201);
    }

    /**
     * PUT /api/user/about/{id} (HARUSNYA ADMIN)
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdmin($request);

        $about = AboutUs::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'Data About tidak ditemukan.',
            ], 404);
        }

        $data = $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'image'   => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = $about->image;

        if ($request->hasFile('image')) {
            // hapus file lama
            if ($about->image && Storage::disk('public')->exists($about->image)) {
                Storage::disk('public')->delete($about->image);
            }
            $imagePath = $request->file('image')->store('about', 'public');
        }

        $about->update([
            'title'   => $data['title'],
            'content' => $data['content'],
            'image'   => $imagePath,
        ]);

        $about->image_url = $about->image ? asset('storage/' . $about->image) : null;

        return response()->json([
            'success' => true,
            'message' => 'About berhasil diperbarui.',
            'data' => $about,
        ]);
    }

    /**
     * DELETE /api/user/about/{id} (HARUSNYA ADMIN)
     */
    public function destroy(Request $request, $id)
    {
        $this->ensureAdmin($request);

        $about = AboutUs::find($id);

        if (!$about) {
            return response()->json([
                'success' => false,
                'message' => 'Data About tidak ditemukan.',
            ], 404);
        }

        if ($about->image && Storage::disk('public')->exists($about->image)) {
            Storage::disk('public')->delete($about->image);
        }

        $about->delete();

        return response()->json([
            'success' => true,
            'message' => 'About berhasil dihapus.',
        ]);
    }

    /**
     * Helper: user tidak boleh CRUD.
     */
    private function ensureAdmin(Request $request): void
    {
        $role = $request->user()?->role;

        if ($role !== 'admin' && $role !== 'super_admin') {
            abort(response()->json([
                'success' => false,
                'message' => 'Forbidden. Hanya admin yang boleh mengubah About.',
            ], 403));
        }
    }

}
