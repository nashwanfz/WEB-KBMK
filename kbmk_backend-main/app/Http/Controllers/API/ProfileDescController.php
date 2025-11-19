<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\ProfileDesc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="ProfileDesc",
 *     required={"judul", "jenis", "deskripsi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="judul", type="string", maxLength=50, example="Tentang Kami"),
 *     @OA\Property(property="jenis", type="string", enum={"about", "visi", "misi"}, example="about"),
 *     @OA\Property(property="deskripsi", type="string", example="Kami adalah organisasi yang..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProfileDescController extends Controller
{
    /**
     * @OA\Get(
     *     path="/profile-descs",
     *     tags={"Profile Descriptions"},
     *     summary="Menampilkan semua deskripsi profil",
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProfileDesc"))))
     * )
     */
    public function index()
    {
        $profileDescs = ProfileDesc::all();
        return response()->json(['data' => $profileDescs]);
    }

    /**
     * @OA\Get(
     *     path="/profile-descs/{id}",
     *     tags={"Profile Descriptions"},
     *     summary="Menampilkan detail deskripsi profil",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/ProfileDesc"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:50',
            'jenis' => 'required|in:about,visi,misi',
            'deskripsi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profileDesc = ProfileDesc::create($request->all());

        return response()->json([
            'message' => 'Profile Description created successfully',
            'data' => $profileDesc
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/profile-descs",
     *     tags={"Profile Descriptions"},
     *     summary="Menambah deskripsi profil baru",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ProfileDesc")
     *     ),
     *     @OA\Response(response=201, description="Berhasil dibuat", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/ProfileDesc"))),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id)
    {
        $profileDesc = ProfileDesc::find($id);

        if (!$profileDesc) {
            return response()->json(['message' => 'Profile Description not found.'], 404);
        }

        return response()->json(['data' => $profileDesc]);
    }

    /**
     * @OA\Put(
     *     path="/profile-descs/{id}",
     *     tags={"Profile Descriptions"},
     *     summary="Memperbarui deskripsi profil",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="judul", type="string", maxLength=50),
     *             @OA\Property(property="jenis", type="string", enum={"about", "visi", "misi"}),
     *             @OA\Property(property="deskripsi", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/ProfileDesc"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, string $id)
    {
        $profileDesc = ProfileDesc::find($id);

        if (!$profileDesc) {
            return response()->json(['message' => 'Profile Description not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|required|string|max:50',
            'jenis' => 'sometimes|required|in:about,visi,misi',
            'deskripsi' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profileDesc->update($request->all());

        return response()->json([
            'message' => 'Profile Description updated successfully',
            'data' => $profileDesc
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/profile-descs/{id}",
     *     tags={"Profile Descriptions"},
     *     summary="Menghapus deskripsi profil",
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
        $profileDesc = ProfileDesc::find($id);

        if (!$profileDesc) {
            return response()->json(['message' => 'Profile Description not found.'], 404);
        }

        $profileDesc->delete();

        return response()->json(['message' => 'Profile Description deleted successfully.']);
    }
}