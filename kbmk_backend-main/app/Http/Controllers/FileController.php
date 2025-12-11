<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response; // Tambahkan import ini

class FileController extends Controller
{
    /**
     * Menampilkan file dari storage dengan header yang benar untuk ditampilkan di browser.
     *
     * @param string $path
     * @return \Illuminate\Http\Response
     */
    public function serve($path)
    {
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $file = Storage::disk('public')->get($path);
        
        // Tentukan MIME type berdasarkan ekstensi file
        $mimeType = $this->getMimeType($path);

        // Buat response dengan header yang tepat
        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"'
        ]);
    }

    /**
     * Helper untuk mendapatkan MIME type dari ekstensi file.
     *
     * @param string $path
     * @return string
     */
    private function getMimeType($path)
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mimes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        // Kembalikan MIME type berdasarkan ekstensi, atau default jika tidak ditemukan
        return $mimes[strtolower($extension)] ?? 'application/octet-stream';
    }
}