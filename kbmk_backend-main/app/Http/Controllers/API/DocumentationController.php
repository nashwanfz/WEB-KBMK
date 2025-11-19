<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Documentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="Documentation",
 *     required={"nama", "deskripsi", "tanggal", "lokasi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", maxLength=50, example="Kegiatan Bakti Sosial"),
 *     @OA\Property(property="deskripsi", type="string", example="Kegiatan sosial di desa Sukamaju."),
 *     @OA\Property(property="tanggal", type="string", format="date", example="2024-10-20"),
 *     @OA\Property(property="lokasi", type="string", maxLength=255, example="Aula Desa Sukamaju"),
 *     @OA\Property(property="foto", type="string", example="documentations/nama_file.jpg"),
 *     @OA\Property(property="foto_url", type="string", example="http://localhost:8000/storage/documentations/nama_file.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class DocumentationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/documentations",
     *     tags={"Documentations"},
     *     summary="Menampilkan daftar dokumentasi (dengan paginasi)",
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(
     *         @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Documentation")),
     *         @OA\Property(property="links", type="object"),
     *         @OA\Property(property="meta", type="object")
     *     ))
     * )
     */
    public function index()
    {
        $documentations = Documentation::latest()->paginate(10);

        $data = $documentations->getCollection()->map(function ($item) {
            $item->foto_url = $item->foto_url; 
            return $item;
        });

        return response()->json([
            'data' => $data,
            'links' => $documentations->links(),
            'meta' => [
                'current_page' => $documentations->currentPage(),
                'last_page' => $documentations->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/documentations/{id}",
     *     tags={"Documentations"},
     *     summary="Menampilkan detail dokumentasi",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Documentation"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(string $id)
    {
        $documentation = Documentation::find($id);
        if (!$documentation) return response()->json(['message' => 'Not found'], 404);

        $documentation->foto_url = $documentation->foto_url;

        return response()->json(['data' => $documentation]);
    }

    /**
     * @OA\Post(
     *     path="/documentations",
     *     tags={"Documentations"},
     *     summary="Menambah dokumentasi baru",
     *     description="Memerlukan autentikasi dan role 'admin'. Mengupload foto.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"nama", "deskripsi", "tanggal", "lokasi", "foto"},
     *                 @OA\Property(property="nama", type="string", maxLength=50),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="tanggal", type="string", format="date"),
     *                 @OA\Property(property="lokasi", type="string", maxLength=255),
     *                 @OA\Property(property="foto", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Berhasil dibuat", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Documentation"))),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'lokasi' => 'required|string|max:255',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $fotoPath = $request->file('foto')->store('documentations', 'public');

        $documentation = Documentation::create($request->except('foto') + ['foto' => $fotoPath]);
        return response()->json(['message' => 'Documentation created successfully', 'data' => $documentation], 201);
    }

    /**
     * @OA\Put(
     *     path="/documentations/{id}",
     *     tags={"Documentations"},
     *     summary="Memperbarui dokumentasi",
     *     description="Memerlukan autentikasi dan role 'admin'. Dapat mengganti foto.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="nama", type="string", maxLength=50),
     *                 @OA\Property(property="deskripsi", type="string"),
     *                 @OA\Property(property="tanggal", type="string", format="date"),
     *                 @OA\Property(property="lokasi", type="string", maxLength=255),
     *                 @OA\Property(property="foto", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Documentation"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, string $id)
    {
        $documentation = Documentation::find($id);
        if (!$documentation) return response()->json(['message' => 'Not found'], 404);

        $request->validate([
            'nama' => 'sometimes|required|string|max:50',
            'deskripsi' => 'sometimes|required|string',
            'tanggal' => 'sometimes|required|date',
            'lokasi' => 'sometimes|required|string|max:255',
            'foto' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            Storage::disk('public')->delete($documentation->foto);
            $data['foto'] = $request->file('foto')->store('documentations', 'public');
        }

        $documentation->update($data);

        return response()->json(['message' => 'Documentation updated successfully', 'data' => $documentation]);
    }

    /**
     * @OA\Delete(
     *     path="/documentations/{id}",
     *     tags={"Documentations"},
     *     summary="Menghapus dokumentasi",
     *     description="Memerlukan autentikasi dan role 'admin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil dihapus"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id)
    {
        $documentation = Documentation::find($id);
        if (!$documentation) return response()->json(['message' => 'Not found'], 404);

        Storage::disk('public')->delete($documentation->foto);
        $documentation->delete();

        return response()->json(['message' => 'Documentation deleted successfully']);
    }
}