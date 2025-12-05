<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\API\Division;
use App\Models\API\Pengurus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengurusController extends Controller
{
    /**
     * Helper function untuk menentukan prioritas urutan berdasarkan jabatan
     */
    private function getPositionPriority($jabatan)
    {
        if (!$jabatan) return 999; // Tanpa jabatan di urutan terakhir

        $jabatan = strtolower($jabatan);

        // Prioritas utama (BPH Inti)
        if (str_contains($jabatan, 'ketua umum') || str_contains($jabatan, 'ketua')) {
            return 1;
        }
        if (str_contains($jabatan, 'sekretaris')) {
            return 2;
        }
        if (str_contains($jabatan, 'bendahara')) {
            return 3;
        }

        // Koordinator divisi
        if (str_contains($jabatan, 'koordinator') || str_contains($jabatan, 'koor')) {
            return 10;
        }

        // Wakil koordinator
        if (str_contains($jabatan, 'wakil')) {
            return 11;
        }

        // Staff/anggota biasa
        if (str_contains($jabatan, 'staff') || str_contains($jabatan, 'anggota')) {
            return 20;
        }

        // Default (jabatan lain yang tidak dikenali)
        return 15;
    }

    public function index()
    {
        // Eager load division dan urutkan berdasarkan prioritas jabatan
        $pengurus = Pengurus::with('division')->get();

        // Sort pengurus berdasarkan prioritas jabatan
        $sorted = $pengurus->sortBy(function($item) {
            return $this->getPositionPriority($item->jabatan);
        })->values();

        return response()->json([
            'data' => $sorted
        ]);
    }

    public function show(string $id)
    {
        $pengurus = Pengurus::with('division')->find($id);
        
        if (!$pengurus) {
            return response()->json(['message' => 'Pengurus not found'], 404);
        }

        return response()->json(['data' => $pengurus]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:50',
            'division_id' => 'required|integer|exists:divisions,id',
            'jabatan' => 'nullable|string|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'required|string',
        ]);

        // Upload foto jika ada
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('pengurus', 'public');
        }

        $pengurus = Pengurus::create($validated);

        // Load relasi division setelah create
        $pengurus->load('division');

        return response()->json([
            'message' => 'Pengurus created successfully',
            'data' => $pengurus
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $pengurus = Pengurus::find($id);
        
        if (!$pengurus) {
            return response()->json(['message' => 'Pengurus not found'], 404);
        }

        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:50',
            'division_id' => 'sometimes|required|integer|exists:divisions,id',
            'jabatan' => 'sometimes|nullable|string|max:100',
            'foto' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'sometimes|required|string',
        ]);

        // Update foto jika ada file baru
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($pengurus->foto) {
                Storage::disk('public')->delete($pengurus->foto);
            }
            $validated['foto'] = $request->file('foto')->store('pengurus', 'public');
        }

        $pengurus->update($validated);

        // Load relasi division setelah update
        $pengurus->load('division');

        return response()->json([
            'message' => 'Pengurus updated successfully',
            'data' => $pengurus
        ]);
    }

    public function destroy(string $id)
    {
        $pengurus = Pengurus::find($id);
        
        if (!$pengurus) {
            return response()->json(['message' => 'Pengurus not found'], 404);
        }
        
        // Hapus foto jika ada
        if ($pengurus->foto) {
            Storage::disk('public')->delete($pengurus->foto);
        }
        
        $pengurus->delete();

        return response()->json(['message' => 'Pengurus deleted successfully']);
    }

    public function divisions()
    {
        $divisions = Division::select('id', 'nama')->orderBy('nama', 'asc')->get();

        return response()->json([
            'data' => $divisions
        ]);
    }
}