<?php

namespace App\Http\Controllers;

use App\Services\PdfEditorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PdfEditorController extends Controller
{
    protected PdfEditorService $pdfEditorService;

    public function __construct(PdfEditorService $pdfEditorService)
    {
        $this->pdfEditorService = $pdfEditorService;
    }

    /**
     * Display the PDF editor interface
     */
    public function index()
    {
        return view('pdf.editor');
    }

    /**
     * Load a PDF for editing
     */
    public function loadPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Store the PDF template
            $originalName = $request->file('pdf_file')->getClientOriginalName();
            $templatePath = $request->file('pdf_file')->store('pdf/templates', 'local');
            $templateFullPath = Storage::disk('local')->path($templatePath);

            // Create a unique session ID
            $sessionId = Str::uuid()->toString();

            // Get editable regions
            $editableRegions = $this->pdfEditorService->getEditableRegions($templateFullPath);

            // Get page count
            $pageCount = $this->pdfEditorService->getPageCount($templateFullPath);

            // Create backup
            $backupPath = $this->pdfEditorService->createBackup($templateFullPath);

            // Initialize empty session
            $this->pdfEditorService->saveEditorSession($templatePath, [], $sessionId);

            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'pdf_path' => $templatePath,
                'original_name' => $originalName,
                'page_count' => $pageCount,
                'editable_regions' => $editableRegions,
                'backup_created' => $backupPath !== false,
                'message' => 'PDF loaded successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get PDF data for rendering in browser
     */
    public function getPdfData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $session = $this->pdfEditorService->loadEditorSession($request->input('session_id'));
            
            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $pdfPath = Storage::disk('local')->path($session['pdf_path']);
            
            if (!file_exists($pdfPath)) {
                return response()->json(['error' => 'PDF file not found'], 404);
            }

            return response()->file($pdfPath, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get PDF data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save edits to the current session
     */
    public function saveEdits(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'edits' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $edits = $request->input('edits');

            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            // Update session with new edits
            if ($this->pdfEditorService->saveEditorSession($session['pdf_path'], $edits, $sessionId)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Edits saved successfully'
                ]);
            }

            return response()->json(['error' => 'Failed to save edits'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save edits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a preview of the edited PDF
     */
    public function generatePreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $pdfPath = Storage::disk('local')->path($session['pdf_path']);
            $edits = $session['edits'] ?? [];

            $previewPath = $this->pdfEditorService->generatePreview($pdfPath, $edits);

            if (!$previewPath) {
                return response()->json(['error' => 'Failed to generate preview'], 500);
            }

            return response()->file($previewPath, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend();

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export the final edited PDF
     */
    public function exportPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'filename' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $filename = $request->input('filename', 'edited-document.pdf');
            
            Log::info('Export PDF started', [
                'session_id' => $sessionId,
                'filename' => $filename
            ]);
            
            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if (!$session) {
                Log::error('Session not found for export', ['session_id' => $sessionId]);
                return response()->json(['error' => 'Session not found'], 404);
            }

            $pdfPath = Storage::disk('local')->path($session['pdf_path']);
            $edits = $session['edits'] ?? [];
            
            Log::info('Export paths determined', [
                'pdf_path' => $pdfPath,
                'pdf_exists' => file_exists($pdfPath),
                'edits_count' => count($edits)
            ]);

            // Generate final PDF with all edits applied
            $outputFilename = 'final_' . uniqid() . '.pdf';
            $outputPath = Storage::disk('local')->path('pdf/generated/' . $outputFilename);
            
            Log::info('Output path generated', [
                'output_filename' => $outputFilename,
                'output_path' => $outputPath,
                'output_dir_exists' => file_exists(dirname($outputPath))
            ]);
            
            $applyResult = $this->pdfEditorService->applyEdits($pdfPath, $edits, $outputPath);
            
            Log::info('Apply edits completed', [
                'result' => $applyResult,
                'output_exists' => file_exists($outputPath),
                'output_size' => file_exists($outputPath) ? filesize($outputPath) : 0
            ]);
            
            if ($applyResult && file_exists($outputPath)) {
                Log::info('Starting file download', [
                    'output_path' => $outputPath,
                    'download_filename' => $filename
                ]);
                return response()->download($outputPath, $filename)->deleteFileAfterSend();
            } elseif ($applyResult && !file_exists($outputPath)) {
                Log::error('Apply edits returned true but file does not exist', [
                    'output_path' => $outputPath
                ]);
                return response()->json(['error' => 'PDF generation succeeded but output file not found'], 500);
            } else {
                Log::error('Apply edits failed', ['output_path' => $outputPath]);
                return response()->json(['error' => 'Failed to export PDF'], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to export PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available form fields for the current PDF
     */
    public function getFormFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $pdfPath = Storage::disk('local')->path($session['pdf_path']);
            $fields = $this->pdfEditorService->getFormFields($pdfPath);

            if ($fields === false) {
                return response()->json(['error' => 'Failed to get form fields'], 500);
            }

            return response()->json([
                'success' => true,
                'fields' => $fields
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get form fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a specific form field value
     */
    public function updateFormField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'field_name' => 'required|string',
            'field_value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $fieldName = $request->input('field_name');
            $fieldValue = $request->input('field_value');
            
            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if (!$session) {
                return response()->json(['error' => 'Session not found'], 404);
            }

            $edits = $session['edits'] ?? [];

            // Add or update form field edit
            $editFound = false;
            foreach ($edits as &$edit) {
                if ($edit['type'] === 'form_field' && $edit['field_name'] === $fieldName) {
                    $edit['value'] = $fieldValue;
                    $editFound = true;
                    break;
                }
            }

            if (!$editFound) {
                $edits[] = [
                    'type' => 'form_field',
                    'field_name' => $fieldName,
                    'value' => $fieldValue,
                    'timestamp' => now()->toISOString()
                ];
            }

            // Save updated edits
            if ($this->pdfEditorService->saveEditorSession($session['pdf_path'], $edits, $sessionId)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Form field updated successfully'
                ]);
            }

            return response()->json(['error' => 'Failed to update form field'], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update form field: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an editor session and cleanup files
     */
    public function deleteSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $sessionId = $request->input('session_id');
            $session = $this->pdfEditorService->loadEditorSession($sessionId);
            
            if ($session) {
                // Delete the PDF file if it exists
                if (Storage::exists($session['pdf_path'])) {
                    Storage::delete($session['pdf_path']);
                }
            }

            // Delete session file
            $sessionPath = "pdf/sessions/{$sessionId}.json";
            if (Storage::exists($sessionPath)) {
                Storage::delete($sessionPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all active editor sessions
     */
    public function listSessions()
    {
        try {
            $sessionFiles = Storage::files('pdf/sessions');
            $sessions = [];

            foreach ($sessionFiles as $sessionFile) {
                if (pathinfo($sessionFile, PATHINFO_EXTENSION) === 'json') {
                    $sessionData = json_decode(Storage::get($sessionFile), true);
                    if ($sessionData) {
                        $sessions[] = [
                            'session_id' => $sessionData['session_id'],
                            'pdf_path' => $sessionData['pdf_path'],
                            'timestamp' => $sessionData['timestamp'],
                            'edit_count' => count($sessionData['edits'] ?? [])
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'sessions' => $sessions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to list sessions: ' . $e->getMessage()
            ], 500);
        }
    }
}