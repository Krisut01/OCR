<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', [ImageController::class, 'index'])->name('home');
Route::post('/upload', [ImageController::class, 'upload'])->name('upload');
Route::delete('/images/{id}', [ImageController::class, 'destroy'])->name('delete');
// In web.php (or your routes file)
Route::delete('/delete-all', [ImageController::class, 'deleteAll'])->name('deleteAll');

