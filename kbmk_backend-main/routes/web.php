<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/files/{path}', [FileController::class, 'serve'])
    ->where('path', '.*');