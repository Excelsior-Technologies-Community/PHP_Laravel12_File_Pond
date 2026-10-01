<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::get('/', [FileUploadController::class, 'index'])
    ->name('files.index');

Route::post('/upload', [FileUploadController::class, 'store'])
    ->name('files.upload');

Route::get('/files/{file}/download', [FileUploadController::class, 'download'])
    ->name('files.download');

Route::get('/files/{file}/preview', [FileUploadController::class, 'preview'])
    ->name('files.preview');

Route::post('/files/{file}/duplicate', [FileUploadController::class, 'duplicate'])
    ->name('files.duplicate');

Route::delete('/files/{file}', [FileUploadController::class, 'destroy'])
    ->name('files.destroy');

Route::post('/files/bulk-delete', [FileUploadController::class, 'bulkDelete'])
    ->name('files.bulk-delete');

Route::post('/files/bulk-download', [FileUploadController::class, 'bulkDownload'])
    ->name('files.bulk-download');

Route::get('/files/export/csv', [FileUploadController::class, 'exportCsv'])
    ->name('files.export.csv');