<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PdfController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// PDF API routes (no CSRF protection)
Route::prefix('pdf')->group(function () {
    Route::post('/form-fields', [PdfController::class, 'getFormFields']);
    Route::post('/fill-form', [PdfController::class, 'fillForm']);
    Route::post('/merge', [PdfController::class, 'mergePdfs']);
    Route::post('/split', [PdfController::class, 'splitPdf']);
    Route::post('/info', [PdfController::class, 'getPdfInfo']);
});

// Diagnostic route
Route::get('/debug', function() {
    return response()->json([
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit')
    ]);
});
