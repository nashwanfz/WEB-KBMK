<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\API\SuratDisposition;
use App\Models\API\SuratRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @OA\Schema(
 *     schema="SuratRequest",
 *     required={"nama_pengirim", "perihal", "tujuan", "jenis_surat", "file_surat"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nomor_surat", type="string", example="SR-20231120-ABCD"),
 *     @OA\Property(property="nama_pengirim", type="string", example="John Doe"),
 *     @OA\Property(property="email_pengirim", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="perihal", type="string", example="Permohonan Kerjasama"),
 *     @OA\Property(property="tujuan", type="string", example="Divisi Humas KBMK"),
 *     @OA\Property(property="asal_instansi", type="string", example="PT. Sejahtera"),
 *     @OA\Property(property="jenis_surat", type="string", example="Kerjasama"),
 *     @OA\Property(property="file_surat", type="string", example="surat_requests/proposal.pdf"),
 *     @OA\Property(property="status", type="string", enum={"pending", "diteruskan", "diproses", "selesai"}, example="pending"),
 *     @OA\Property(property="dispositions", type="array", @OA\Items(ref="#/components/schemas/SuratDisposition")),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="SuratDisposition",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="surat_request_id", type="integer", example=1),
 *     @OA\Property(property="assigned_to", type="integer", example=5),
 *     @OA\Property(property="catatan", type="string", example="Mohon segera diproses."),
 *     @OA\Property(property="status", type="string", enum={"belum dibaca", "diproses", "selesai"}, example="belum dibaca"),
 *     @OA\Property(property="assignedUser", type="object", ref="#/components/schemas/UserSimple"),
 *     @OA\Property(property="suratRequest", type="object", ref="#/components/schemas/SuratRequest"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SuratRequestController extends Controller
{
    /**
     * @OA\Post(
     *     path="/surat-requests",
     *     tags={"Surat Masuk"},
     *     summary="Guest mengajukan surat baru",
     *     description="Endpoint publik untuk mengajukan surat permohonan atau lainnya.",
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="multipart/form-data",
     *             @OA\Schema(required={"nama_pengirim", "perihal", "tujuan", "jenis_surat", "file_surat"},
     *                 @OA\Property(property="nama_pengirim", type="string", maxLength=255),
     *                 @OA\Property(property="email_pengirim", type="string", format="email", maxLength=255),
     *                 @OA\Property(property="perihal", type="string", maxLength=255),
     *                 @OA\Property(property="tujuan", type="string"),
     *                 @OA\Property(property="asal_instansi", type="string", maxLength=255),
     *                 @OA\Property(property="jenis_surat", type="string", maxLength=255),
     *                 @OA\Property(property="file_surat", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Surat berhasil diajukan", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratRequest"))),
     *     @OA\Response(response=422, description="Error validasi")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_pengirim' => 'required|string|max:255',
            'email_pengirim' => 'nullable|email|max:255',
            'perihal' => 'required|string|max:255',
            'tujuan' => 'required|string',
            'asal_instansi' => 'nullable|string|max:255',
            'jenis_surat' => 'required|string|max:255',
            'file_surat' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $filePath = $request->file('file_surat')->store('surat_requests');
        $nomorSurat = 'SR-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $surat = SuratRequest::create([
            'nomor_surat' => $nomorSurat,
            'nama_pengirim' => $request->nama_pengirim,
            'email_pengirim' => $request->email_pengirim,
            'perihal' => $request->perihal,
            'tujuan' => $request->tujuan,
            'asal_instansi' => $request->asal_instansi,
            'jenis_surat' => $request->jenis_surat,
            'file_surat' => $filePath,
        ]);

        return response()->json([
            'message' => 'Surat berhasil diajukan.',
            'data' => $surat
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/surat-requests",
     *     tags={"Surat Masuk"},
     *     summary="Admin/Superadmin melihat semua pengajuan surat",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SuratRequest")))),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        // Muat data disposisi juga untuk melihat ke mana surat sudah ditugaskan
        $surats = SuratRequest::with('dispositions.assignedUser')->latest()->get();

        return response()->json([
            'data' => $surats
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/surat-requests/{suratRequest}/assign",
     *     tags={"Surat Masuk"},
     *     summary="Admin/Superadmin menugaskan surat ke koordinator",
     *     description="Memerlukan autentikasi dan role 'admin' atau 'superadmin'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="suratRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(required={"assigned_to"},
     *                 @OA\Property(property="assigned_to", type="integer", description="ID User (Koordinator Divisi)"),
     *                 @OA\Property(property="catatan", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Surat berhasil ditugaskan", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratRequest"))),
     *     @OA\Response(response=404, description="Surat tidak ditemukan"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function assign(Request $request, SuratRequest $suratRequest)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'catatan' => 'nullable|string',
        ]);

        $assignedUser = User::findOrFail($request->assigned_to);

        DB::beginTransaction();
        try {
            SuratDisposition::create([
                'surat_request_id' => $suratRequest->id,
                'assigned_to' => $request->assigned_to,
                'catatan' => $request->catatan,
            ]);

            $suratRequest->update(['status' => 'diteruskan']);

            DB::commit();

            return response()->json([
                'message' => "Surat berhasil ditugaskan kepada {$assignedUser->username}.",
                'data' => $suratRequest->load('dispositions.assignedUser')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menugaskan surat.'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/my-dispositions",
     *     tags={"Surat Masuk"},
     *     summary="Koordinator melihat surat yang ditugaskan kepadanya",
     *     description="Memerlukan autentikasi dan role 'koor_divisi'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Berhasil", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SuratDisposition")))),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function myDispositions()
    {
        $dispositions = SuratDisposition::where('assigned_to', Auth::user()->id)
            ->with('suratRequest') 
            ->latest()
            ->get();

        return response()->json([
            'data' => $dispositions
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/surat-dispositions/{suratDisposition}/status",
     *     tags={"Surat Masuk"},
     *     summary="Koordinator memperbarui status surat",
     *     description="Memerlukan autentikasi dan role 'koor_divisi'.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="suratDisposition", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\MediaType(mediaType="application/json",
     *             @OA\Schema(required={"status"},
     *                 @OA\Property(property="status", type="string", enum={"diproses", "selesai"})
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status berhasil diperbarui", @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SuratDisposition"))),
     *     @OA\Response(response=403, description="Unauthorized - Bukan tugas Anda"),
     *     @OA\Response(response=404, description="Disposisi tidak ditemukan"),
     *     @OA\Response(response=422, description="Error validasi"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function updateDispositionStatus(Request $request, SuratDisposition $suratDisposition)
    {
        if ($suratDisposition->assigned_to !== Auth::user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'status' => 'required|in:diproses,selesai',
        ]);
        $suratDisposition->update(['status' => $request->status]);

        if ($request->status === 'selesai') {
            $suratDisposition->suratRequest->update(['status' => 'selesai']);
        }

        return response()->json([
            'message' => 'Status berhasil diperbarui.',
            'data' => $suratDisposition
        ]);
    }
}