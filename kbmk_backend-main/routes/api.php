<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\PengurusController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ProfileDescController;
use App\Http\Controllers\Api\SuratRequestController;
use App\Http\Controllers\Api\SuratOutgoingController;
use App\Http\Controllers\Api\LinkController;

// API Routes - Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/divisions', [PengurusController::class, 'divisions']);

// Route untuk melihat data (bisa diakses siapa saja)
Route::get('/documentations', [DocumentationController::class, 'index']);
Route::get('/documentations/{id}', [DocumentationController::class, 'show']);
Route::get('/pengurus', [PengurusController::class, 'index']);
Route::get('/pengurus/{id}', [PengurusController::class, 'show']);
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
Route::get('/profile-descs', [ProfileDescController::class, 'index']);
Route::get('/profile-descs/{id}', [ProfileDescController::class, 'show']);

// Route untuk melihat link (publik)
Route::get('/links', [LinkController::class, 'index']);
Route::get('/links/{id}', [LinkController::class, 'show']);

// Guest mengajukan surat
Route::post('/surat-requests', [SuratRequestController::class, 'store']);



// API Routes - Protected (Membutuhkan Login)
Route::middleware('auth:sanctum')->group(function () {
    
    // Route untuk user yang sudah login (semua role)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // API Routes - Fitur Persuratan

    // Route yang hanya bisa diakses oleh Superadmin & Admin (Sekretaris/Ketua)
    Route::middleware(['role.admin'])->group(function () {
        // Melihat semua surat masuk
        Route::get('/surat-requests', [SuratRequestController::class, 'index']);
        // Menugaskan surat ke divisi
        Route::patch('/surat-requests/{suratRequest}/assign', [SuratRequestController::class, 'assign']);
        // Melihat dan mengelola surat keluar
        Route::apiResource('surat-outgoing', SuratOutgoingController::class);
        
        // Mengelola link (hanya admin & superadmin)
        Route::apiResource('links', LinkController::class)->except(['index', 'show']);
    });
    
    // Route yang hanya bisa diakses oleh Koordinator Divisi
    Route::middleware(['role.koordinator'])->group(function () {
        // Koordinator hanya melihat surat yang ditugaskan ke divisinya
        Route::get('/my-dispositions', [SuratRequestController::class, 'myDispositions']);
        // Koordinator mengubah status surat yang menjadi tanggung jawabnya
        Route::patch('/surat-dispositions/{suratDisposition}/status', [SuratRequestController::class, 'updateDispositionStatus']);
    });

    
    // Route yang hanya bisa diakses oleh Superadmin (Ketua)
    Route::middleware(['role.superadmin'])->group(function () {
        Route::apiResource('pengurus', PengurusController::class)->except(['index', 'show']);
        Route::apiResource('schedules', ScheduleController::class)->except(['index', 'show']);
        Route::apiResource('profile-descs', ProfileDescController::class)->except(['index', 'show']);
    });

    // Route yang bisa diakses oleh Admin & Koor Media
    Route::middleware(['role.admin', 'role.koor_media'])->group(function () {
        Route::apiResource('documentations', DocumentationController::class)->except(['index', 'show']);
    });

});