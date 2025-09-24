<?php

namespace App\Http\Controllers;

use App\Services\PdftkService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PdfController extends Controller
{
    protected PdftkService $pdftkService;

    public function __construct(PdftkService $pdftkService)
    {
        $this->pdftkService = $pdftkService;
    }

    /**
     * Display form for PDF operations
     */
    public function index()
    {
        return view('pdf.index');
    }

    /**
     * Get form fields from an uploaded PDF
     */
    public function getFormFields(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Store the uploaded file temporarily
            $path = $request->file('pdf_file')->store('temp', 'local');
            $fullPath = Storage::path($path);

            // Get form fields
            $fields = $this->pdftkService->getFormFields($fullPath);

            // Clean up temporary file
            Storage::delete($path);

            if ($fields === false) {
                return response()->json(['error' => 'Failed to extract form fields'], 500);
            }

            return response()->json([
                'success' => true,
                'fields' => $fields,
                'message' => 'Form fields extracted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fill a PDF form with provided data
     */
    public function fillForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
            'form_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Store the template file
            $templatePath = $request->file('pdf_file')->store('pdf/templates', 'local');
            $templateFullPath = Storage::path($templatePath);

            // Fill the form
            $filledContent = $this->pdftkService->fillForm(
                $templateFullPath,
                $request->input('form_data')
            );

            if ($filledContent === false) {
                return response()->json(['error' => 'Failed to fill PDF form'], 500);
            }

            // Clean up template file
            Storage::delete($templatePath);

            // Return the filled PDF as download
            return response($filledContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="filled-form.pdf"',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Merge multiple PDF files
     */
    public function mergePdfs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_files' => 'required|array|min:2',
            'pdf_files.*' => 'file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $tempPaths = [];
            $fullPaths = [];

            // Store all uploaded files temporarily
            foreach ($request->file('pdf_files') as $file) {
                $path = $file->store('temp', 'local');
                $tempPaths[] = $path;
                $fullPaths[] = Storage::path($path);
            }

            // Merge PDFs
            $mergedContent = $this->pdftkService->merge($fullPaths);

            // Clean up temporary files
            foreach ($tempPaths as $path) {
                Storage::delete($path);
            }

            if ($mergedContent === false) {
                return response()->json(['error' => 'Failed to merge PDF files'], 500);
            }

            // Return the merged PDF as download
            return response($mergedContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="merged.pdf"',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Split a PDF into individual pages
     */
    public function splitPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Store the uploaded file temporarily
            $path = $request->file('pdf_file')->store('temp', 'local');
            $fullPath = Storage::path($path);
            
            // Create output directory
            $splitDirName = 'split_' . uniqid();
            $outputDir = Storage::disk('local')->path('temp/' . $splitDirName);

            // Split the PDF
            $splitFiles = $this->pdftkService->split($fullPath, $outputDir);

            // Clean up original file
            Storage::delete($path);

            if ($splitFiles === false) {
                return response()->json(['error' => 'Failed to split PDF'], 500);
            }

            // Create a ZIP file containing all pages
            $zipFilename = 'split_pages_' . uniqid() . '.zip';
            $zipPath = Storage::disk('local')->path('temp/' . $zipFilename);
            $zip = new \ZipArchive();
            
            if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
                foreach ($splitFiles as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                // Clean up split files
                foreach ($splitFiles as $file) {
                    unlink($file);
                }
                rmdir($outputDir);

                // Return ZIP file
                return response()->download($zipPath, 'split_pages.zip')->deleteFileAfterSend();
            }

            return response()->json(['error' => 'Failed to create ZIP file'], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get PDF information/metadata
     */
    public function getPdfInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Store the uploaded file temporarily
            $path = $request->file('pdf_file')->store('temp', 'local');
            $fullPath = Storage::path($path);

            // Get PDF info
            $info = $this->pdftkService->getInfo($fullPath);

            // Clean up temporary file
            Storage::delete($path);

            if ($info === false) {
                return response()->json(['error' => 'Failed to get PDF information'], 500);
            }

            return response()->json([
                'success' => true,
                'info' => $info,
                'message' => 'PDF information retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}
