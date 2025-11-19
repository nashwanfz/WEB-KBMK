<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DocumentationController;
use App\Http\Controllers\Api\PengurusController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ProfileDescController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/documentations', [DocumentationController::class, 'index']);
Route::get('/documentations/{id}', [DocumentationController::class, 'show']);
Route::get('/pengurus', [PengurusController::class, 'index']);
Route::get('/pengurus/{id}', [PengurusController::class, 'show']);
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::get('/schedules/{id}', [ScheduleController::class, 'show']);
Route::get('/profile-descs', [ProfileDescController::class, 'index']);
Route::get('/profile-descs/{id}', [ProfileDescController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role.admin')->group(function () {
        Route::apiResource('documentations', DocumentationController::class)->except(['index', 'show']);
    });
    Route::middleware('role.superadmin')->group(function () {
        Route::apiResource('pengurus', PengurusController::class)->except(['index', 'show']);
        Route::apiResource('schedules', ScheduleController::class)->except(['index', 'show']);
        Route::apiResource('profile-descs', ProfileDescController::class)->except(['index', 'show']);
    });

});