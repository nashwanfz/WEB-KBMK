<?php

// Perbaikan namespace menjadi standar Laravel (Api bukan API)
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\API\Pengurus;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Pengurus",
 *     required={"nama", "division_id", "deskripsi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", maxLength=50, example="Nanda Arianto"),
 *     @OA\Property(property="jabatan", type="string", maxLength=100, example="Ketua Umum"),
 *     @OA\Property(property="division_id", type="integer", example=2),
 *     @OA\Property(property="division", type="object",
 *         @OA\Property(property="id", type="integer", example=2),
 *         @OA\Property(property="nama", type="string", example="Perlengkapan"),
 *         @OA\Property(property="deskripsi", type="string", example="Mengelola logistik.")
 *     ),
 *     @OA\Property(property="foto", type="string", example="pengurus/nama_file.jpg"),
 *     @OA\Property(property="foto_url", type="string", example="http://localhost:8000/storage/pengurus/nama_file.jpg"),
 *     @OA\Property(property="deskripsi", type="string", example="Bertanggung jawab atas kelancaran program kerja."),
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
     *     summary="Menampilkan daftar pengurus",
     *     description="Endpoint publik untuk melihat data pengurus beserta divisinya",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Pengurus"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $pengurus = Pengurus::with('division')->latest()->get();

        $data = $pengurus->map(function ($item) {
            $item->foto_url = $item->foto_url;
            return $item;
        });

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/pengurus/{id}",
     *     tags={"Pengurus"},
     *     summary="Menampilkan detail pengurus",
     *     description="Endpoint publik untuk melihat detail satu pengurus beserta divisinya",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Pengurus"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id)
    {
        $pengurus = Pengurus::with('division')->find($id);
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
     *             @OA\Schema(required={"nama", "division_id", "deskripsi"},
     *                 @OA\Property(property="nama", type="string", maxLength=50),
     *                 @OA\Property(property="jabatan", type="string", maxLength=100),
     *                 @OA\Property(property="division_id", type="integer", description="ID dari tabel divisions"),
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
            'division_id' => 'required|integer|exists:divisions,id',
            'jabatan' => 'nullable|string|max:100',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'required|string',
        ]);

        $fotoPath = $request->file('foto')->store('pengurus', 'public');
        $pengurus = Pengurus::create([
            'nama' => $request->nama,
            'division_id' => $request->division_id,
            'jabatan' => $request->jabatan,
            'foto' => $fotoPath,
            'deskripsi' => $request->deskripsi,
        ]);

        $pengurus->load('division');

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
     *                 @OA\Property(property="jabatan", type="string", maxLength=100),
     *                 @OA\Property(property="division_id", type="integer", description="ID dari tabel divisions"),
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
            'division_id' => 'sometimes|required|integer|exists:divisions,id',
            'jabatan' => 'sometimes|nullable|string|max:100',
            'foto' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'sometimes|required|string',
        ]);

        $data = $request->only(['nama', 'division_id', 'jabatan', 'deskripsi']);
        if ($request->hasFile('foto')) {
            Storage::disk('public')->delete($pengurus->foto);
            $data['foto'] = $request->file('foto')->store('pengurus', 'public');
        }
        $pengurus->update($data);
        $pengurus->load('division');

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