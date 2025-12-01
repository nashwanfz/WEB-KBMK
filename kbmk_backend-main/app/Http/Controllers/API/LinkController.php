<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\API\Link;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Link",
 *     required={"nama", "link"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", example="Google Drive KBMK"),
 *     @OA\Property(property="link", type="string", format="uri", example="https://drive.google.com/drive/folders/xyz"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class LinkController extends Controller
{
    /**
     * @OA\Get(
     *     path="/links",
     *     tags={"Link"},
     *     summary="Menampilkan daftar link",
     *     description="Endpoint publik untuk melihat semua link.",
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Link"))))
     * )
     */
    public function index()
    {
        $links = Link::latest()->get();
        return response()->json(['data' => $links]);
    }

    /**
     * @OA\Get(
     *     path="/links/{id}",
     *     tags={"Link"},
     *     summary="Menampilkan detail link",
     *     description="Endpoint publik untuk melihat detail satu link.",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Link"))),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Link $link)
    {
        return response()->json(['data' => $link]);
    }

    /**
     * @OA\Post(
     *     path="/links",
     *     tags={"Link"},
     *     summary="Menambah link baru",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(required={"nama", "link"},
     *                 @OA\Property(property="nama", type="string", maxLength=255, example="Link GForm"),
     *                 @OA\Property(property="link", type="string", format="uri", example="https://forms.gle/xyz")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Link berhasil dibuat", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Link"))),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:links,nama',
            'link' => 'required|url|max:255',
        ]);

        $link = Link::create($request->all());

        return response()->json(['message' => 'Link berhasil ditambahkan.', 'data' => $link], 201);
    }

    /**
     * @OA\Put(
     *     path="/links/{id}",
     *     tags={"Link"},
     *     summary="Memperbarui link",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="nama", type="string", maxLength=255, example="Link GForm Baru"),
     *                 @OA\Property(property="link", type="string", format="uri", example="https://forms.gle/abc")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Link berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Link"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, Link $link)
    {
        $request->validate([
            'nama' => 'sometimes|required|string|max:255|unique:links,nama,' . $link->id,
            'link' => 'sometimes|required|url|max:255',
        ]);

        $link->update($request->all());

        return response()->json(['message' => 'Link berhasil diperbarui.', 'data' => $link]);
    }

    /**
     * @OA\Delete(
     *     path="/links/{id}",
     *     tags={"Link"},
     *     summary="Menghapus link",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Link berhasil dihapus"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(Link $link)
    {
        $link->delete();
        return response()->json(['message' => 'Link berhasil dihapus.']);
    }
}