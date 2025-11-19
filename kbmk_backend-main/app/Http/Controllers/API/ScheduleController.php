<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\API\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="Schedule",
 *     required={"nama", "tanggal", "deskripsi"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nama", type="string", maxLength=50, example="Rapat Rutinan"),
 *     @OA\Property(property="tanggal", type="string", format="date", example="2024-12-25"),
 *     @OA\Property(property="deskripsi", type="string", example="Membahas agenda kegiatan bulan depan."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/schedules",
     *     tags={"Schedules"},
     *     summary="Menampilkan daftar semua jadwal",
     *     description="Mengembalikan array dari objek jadwal yang diurutkan berdasarkan tanggal",
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Schedule"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        $schedules = Schedule::orderBy('tanggal', 'asc')->get();
        return response()->json(['data' => $schedules]);
    }

    /**
     * @OA\Post(
     *     path="/schedules",
     *     tags={"Schedules"},
     *     summary="Membuat jadwal baru",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Schedule")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Jadwal berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Schedule created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error validasi",
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="array", @OA\Items(type="string", example="The nama field is required.")),
     *             @OA\Property(property="tanggal", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $schedule = Schedule::create($request->all());

        return response()->json([
            'message' => 'Schedule created successfully',
            'data' => $schedule
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/schedules/{id}",
     *     tags={"Schedules"},
     *     summary="Menampilkan detail jadwal berdasarkan ID",
     *     description="Mengembalikan satu objek jadwal",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari jadwal",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Jadwal tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Schedule not found.")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        }

        return response()->json(['data' => $schedule]);
    }

    /**
     * @OA\Put(
     *     path="/schedules/{id}",
     *     tags={"Schedules"},
     *     summary="Memperbarui jadwal",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari jadwal",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nama", type="string", maxLength=50),
     *             @OA\Property(property="tanggal", type="string", format="date"),
     *             @OA\Property(property="deskripsi", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jadwal berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Schedule updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Schedule")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Schedule not found."),
     *     @OA\Response(response=422, description="Error validasi."),
     *     @OA\Response(response=401, description="Unauthorized.")
     * )
     */
    public function update(Request $request, string $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:50',
            'tanggal' => 'sometimes|required|date',
            'deskripsi' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $schedule->update($request->all());

        return response()->json([
            'message' => 'Schedule updated successfully',
            'data' => $schedule
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/schedules/{id}",
     *     tags={"Schedules"},
     *     summary="Menghapus jadwal",
     *     description="Memerlukan autentikasi dan role 'superadmin'",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID dari jadwal",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Jadwal berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Schedule deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Schedule not found."),
     *     @OA\Response(response=401, description="Unauthorized.")
     * )
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found.'], 404);
        }

        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully.']);
    }
}