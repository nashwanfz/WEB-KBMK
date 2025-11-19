<?php

// app/Http/Controllers/Api/PengurusController.php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Pengurus",
 *     required={"nama", "divisi", "deskripsi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", maxLength=50, example="Nanda Arianto"),
 *     @OA\Property(property="divisi", type="string", maxLength=50, example="Humas"),
 *     @OA\Property(property="foto", type="string", example="pengurus/nama_file.jpg"),
 *     @OA\Property(property="foto_url", type="string", example="http://localhost:8000/storage/pengurus/nama_file.jpg"),
 *     @OA\Property(property="deskripsi", type="string", example="Bertanggung jawab atas hubungan masyarakat."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class PengurusController extends Controller
{
    /**
     * @OA\Get(
     *     path="/pengurus",
     *     tags={"Pengurus"},
     *     summary="Menampilkan daftar pengurus (dengan paginasi)",
     *     description="Endpoint publik untuk melihat data pengurus",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Pengurus")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer")
     *             )
     *         )
     *     )
     * )
     */
        public function index()
    {
        // Ambil semua data pengurus tanpa paginate
        $pengurus = Pengurus::latest()->get();

        // Tambahkan foto_url ke setiap item
        $data = $pengurus->map(function ($item) {
            $item->foto_url = $item->foto_url;
            return $item;
        });

        return response()->json([
            'data' => $data,
            'links' => new \stdClass(), // kosongkan links karena tidak ada paginate
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
            ]
        ]);
    }


    /**
     * @OA\Get(
     *     path="/pengurus/{id}",
     *     tags={"Pengurus"},
     *     summary="Menampilkan detail pengurus",
     *     description="Endpoint publik untuk melihat detail satu pengurus",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Pengurus"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id)
    {
        $pengurus = Pengurus::find($id);
        if (!$pengurus) return response()->json(['message' => 'Not found'], 404);

        $pengurus->foto_url = $pengurus->foto_url;

        return response()->json(['data' => $pengurus]);
    }

    /**
     * @OA\Post(
     *     path="/pengurus",
     *     tags={"Pengurus"},
     *     summary="Menambah data pengurus baru",
     *     description="Memerlukan autentikasi dan role 'superadmin'. Mengupload foto.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"nama", "divisi", "foto", "deskripsi"},
     *                 @OA\Property(property="nama", type="string", maxLength=50),
     *                 @OA\Property(property="divisi", type="string", maxLength=50),
     *                 @OA\Property(property="foto", type="string", format="binary"),
     *                 @OA\Property(property="deskripsi", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Berhasil dibuat", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Pengurus"))),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'required|string',
        ]);

        $fotoPath = $request->file('foto')->store('pengurus', 'public');
        $pengurus = Pengurus::create($request->except('foto') + ['foto' => $fotoPath]);

        return response()->json(['message' => 'Pengurus created successfully', 'data' => $pengurus], 201);
    }

    /**
     * @OA\Put(
     *     path="/pengurus/{id}",
     *     tags={"Pengurus"},
     *     summary="Memperbarui data pengurus",
     *     description="Memerlukan autentikasi dan role 'superadmin'. Dapat mengganti foto.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nama", type="string", maxLength=50),
     *                 @OA\Property(property="divisi", type="string", maxLength=50),
     *                 @OA\Property(property="foto", type="string", format="binary"),
     *                 @OA\Property(property="deskripsi", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Pengurus"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, string $id)
    {
        $pengurus = Pengurus::find($id);
        if (!$pengurus) return response()->json(['message' => 'Not found'], 404);

        $request->validate([
            'nama' => 'sometimes|required|string|max:50',
            'divisi' => 'sometimes|required|string|max:50',
            'foto' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'sometimes|required|string',
        ]);

        $data = $request->except('foto');
        if ($request->hasFile('foto')) {
            Storage::disk('public')->delete($pengurus->foto);
            $data['foto'] = $request->file('foto')->store('pengurus', 'public');
        }
        $pengurus->update($data);

        return response()->json(['message' => 'Pengurus updated successfully', 'data' => $pengurus]);
    }

    /**
     * @OA\Delete(
     *     path="/pengurus/{id}",
     *     tags={"Pengurus"},
     *     summary="Menghapus data pengurus",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil dihapus"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id)
    {
        $pengurus = Pengurus::find($id);
        if (!$pengurus) return response()->json(['message' => 'Not found'], 404);
        
        Storage::disk('public')->delete($pengurus->foto);
        $pengurus->delete();

        return response()->json(['message' => 'Pengurus deleted successfully']);
    }
}