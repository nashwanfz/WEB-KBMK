<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\API\SuratOutgoing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="SuratOutgoing",
 *     required={"perihal", "tujuan", "jenis_surat", "file_surat"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nomor_surat", type="string", example="SK-20231120-WXYZ"),
 *     @OA\Property(property="perihal", type="string", example="Undangan Acara Maulid Nabi"),
 *     @OA\Property(property="tujuan", type="string", example="Seluruh Civitas Akademika Fakultas Teknik"),
 *     @OA\Property(property="jenis_surat", type="string", example="Undangan"),
 *     @OA\Property(property="file_surat", type="string", example="surat_outgoing/undangan.pdf"),
 *     @OA\Property(property="dibuat_oleh", type="integer", example=2),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/UserSimple"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SuratOutgoingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/surat-outgoing",
     *     tags={"Surat Keluar"},
     *     summary="Admin/Superadmin melihat semua surat keluar",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SuratOutgoing")))),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $surats = SuratOutgoing::with('creator')->latest()->get();
        return response()->json(['data' => $surats]);
    }

    /**
     * @OA\Post(
     *     path="/surat-outgoing",
     *     tags={"Surat Keluar"},
     *     summary="Admin/Superadmin membuat surat keluar baru",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"perihal", "tujuan", "jenis_surat", "file_surat"},
     *                 @OA\Property(property="perihal", type="string", maxLength=255),
     *                 @OA\Property(property="tujuan", type="string"),
     *                 @OA\Property(property="jenis_surat", type="string", maxLength=255),
     *                 @OA\Property(property="file_surat", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Surat keluar berhasil dibuat", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratOutgoing"))),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string',
            'jenis_surat' => 'required|string|max:255',
            'file_surat' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $filePath = $request->file('file_surat')->store('surat_outgoing');
        $nomorSurat = 'SK-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $surat = SuratOutgoing::create([
            'nomor_surat' => $nomorSurat,
            'perihal' => $request->perihal,
            'tujuan' => $request->tujuan,
            'jenis_surat' => $request->jenis_surat,
            'file_surat' => $filePath,
            'dibuat_oleh' => Auth::user()->id,
        ]);

        return response()->json([
            'message' => 'Surat keluar berhasil dibuat.',
            'data' => $surat->load('creator')
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/surat-outgoing/{suratOutgoing}",
     *     tags={"Surat Keluar"},
     *     summary="Menampilkan detail surat keluar",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="suratOutgoing", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratOutgoing"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(SuratOutgoing $suratOutgoing)
    {
        return response()->json(['data' => $suratOutgoing->load('creator')]);
    }

    /**
     * @OA\Put(
     *     path="/surat-outgoing/{suratOutgoing}",
     *     tags={"Surat Keluar"},
     *     summary="Memperbarui surat keluar",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="suratOutgoing", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="perihal", type="string", maxLength=255),
     *                 @OA\Property(property="tujuan", type="string"),
     *                 @OA\Property(property="jenis_surat", type="string", maxLength=255),
     *                 @OA\Property(property="file_surat", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Surat keluar berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratOutgoing"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, SuratOutgoing $suratOutgoing)
    {
        $request->validate([
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string',
            'jenis_surat' => 'required|string|max:255',
            'file_surat' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $data = $request->only(['perihal', 'tujuan', 'jenis_surat']);

        if ($request->hasFile('file_surat')) {
            Storage::delete($suratOutgoing->file_surat);
            $data['file_surat'] = $request->file('file_surat')->store('surat_outgoing');
        }

        $suratOutgoing->update($data);

        return response()->json([
            'message' => 'Surat keluar berhasil diperbarui.',
            'data' => $suratOutgoing->load('creator')
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/surat-outgoing/{suratOutgoing}",
     *     tags={"Surat Keluar"},
     *     summary="Menghapus surat keluar",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="suratOutgoing", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Surat keluar berhasil dihapus"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(SuratOutgoing $suratOutgoing)
    {
        Storage::delete($suratOutgoing->file_surat);
        
        $suratOutgoing->delete();

        return response()->json(['message' => 'Surat keluar berhasil dihapus.']);
    }
}