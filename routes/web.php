<?php

use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileUploadController::class, 'index'])
    ->name('files.index');

Route::post('/upload', [FileUploadController::class, 'store'])
    ->name('files.upload');

Route::get('/files/{file}/download', [FileUploadController::class, 'download'])
    ->name('files.download');

Route::delete('/files/{file}', [FileUploadController::class, 'destroy'])
    ->name('files.destroy');
