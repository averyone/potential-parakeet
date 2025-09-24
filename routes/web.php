<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PdfEditorController;

Route::get('/', function () {
    return view('welcome');
});

// Debug route for testing PDF analysis
Route::get('/debug', function () {
    return view('debug');
});

// Debug route for testing Vue.js
Route::get('/debug-vue', function () {
    return view('debug-vue');
});

// CSRF token endpoint for debugging
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

// Test upload page
Route::get('/test-upload', function () {
    return view('test-upload');
});

// PDF routes
Route::prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/', [PdfController::class, 'index'])->name('index');
    Route::post('/form-fields', [PdfController::class, 'getFormFields'])->name('form-fields');
    Route::post('/fill-form', [PdfController::class, 'fillForm'])->name('fill-form');
    Route::post('/merge', [PdfController::class, 'mergePdfs'])->name('merge');
    Route::post('/split', [PdfController::class, 'splitPdf'])->name('split');
    Route::post('/info', [PdfController::class, 'getPdfInfo'])->name('info');
});

// PDF Editor routes
Route::prefix('pdf-editor')->name('pdf-editor.')->group(function () {
    Route::get('/', [PdfEditorController::class, 'index'])->name('index');
    Route::get('/simple', function() {
        return view('pdf.editor-simple');
    })->name('simple');
    Route::get('/fixed', function() {
        return view('pdf.editor-fixed');
    })->name('fixed');
    
    // API endpoints for the editor
    Route::post('/load', [PdfEditorController::class, 'loadPdf'])->name('load');
    Route::get('/data', [PdfEditorController::class, 'getPdfData'])->name('data');
    Route::post('/save', [PdfEditorController::class, 'saveEdits'])->name('save');
    Route::get('/preview', [PdfEditorController::class, 'generatePreview'])->name('preview');
    Route::post('/export', [PdfEditorController::class, 'exportPdf'])->name('export');
    Route::get('/form-fields', [PdfEditorController::class, 'getFormFields'])->name('form-fields');
    Route::post('/update-field', [PdfEditorController::class, 'updateFormField'])->name('update-field');
    Route::delete('/session', [PdfEditorController::class, 'deleteSession'])->name('delete-session');
    Route::get('/sessions', [PdfEditorController::class, 'listSessions'])->name('sessions');
});

