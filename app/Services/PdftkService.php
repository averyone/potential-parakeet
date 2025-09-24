<?php

namespace App\Services;

use mikehaertl\pdftk\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PdftkService
{
    /**
     * Create a new PDF instance
     */
    public function createPdf(string|array|null $files = null): Pdf
    {
        $pdf = new Pdf($files);
        
        // Set the path to pdftk binary using the command property
        $pdf->command = 'pdftk';
        
        return $pdf;
    }

    /**
     * Fill a PDF form with data
     */
    public function fillForm(string $templatePath, array $data, ?string $outputPath = null): string|false
    {
        try {
            Log::info('PDFtk fillForm called', [
                'template_path' => $templatePath,
                'output_path' => $outputPath,
                'data_keys' => array_keys($data),
                'template_exists' => file_exists($templatePath)
            ]);
            
            $pdf = $this->createPdf($templatePath);
            
            // Fill form fields
            $pdf->fillForm($data)
                ->needAppearances()
                ->flatten();
            
            if ($outputPath) {
                // Save to specific path
                Log::info('Attempting to save PDF to output path');
                if ($pdf->saveAs($outputPath)) {
                    Log::info('PDF saved successfully', [
                        'output_path' => $outputPath,
                        'file_exists' => file_exists($outputPath),
                        'file_size' => file_exists($outputPath) ? filesize($outputPath) : 0
                    ]);
                    return $outputPath;
                } else {
                    Log::error('PDFtk saveAs failed', [
                        'error' => $pdf->getError(),
                        'output_path' => $outputPath,
                        'output_dir_exists' => file_exists(dirname($outputPath)),
                        'output_dir_writable' => is_writable(dirname($outputPath))
                    ]);
                }
            } else {
                // Return PDF content as string
                $content = $pdf->toString();
                Log::info('PDF content generated', ['content_length' => strlen($content)]);
                return $content;
            }
            
            Log::error('PDFtk form filling failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk form filling exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get form fields from a PDF
     */
    public function getFormFields(string $pdfPath): array|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            $fields = $pdf->getDataFields();
            
            if ($fields === false) {
                Log::error('Failed to get form fields: ' . $pdf->getError());
                return false;
            }
            
            // Convert DataFields object to array
            $fieldsArray = [];
            if ($fields && method_exists($fields, '__toString')) {
                // Parse the data fields string output
                $dataString = (string) $fields;
                $lines = explode("\n", $dataString);
                
                $currentField = null;
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    if (strpos($line, 'FieldName: ') === 0) {
                        $currentField = substr($line, 11);
                        if (!isset($fieldsArray[$currentField])) {
                            $fieldsArray[$currentField] = [
                                'name' => $currentField,
                                'type' => 'Text',
                                'value' => '',
                                'options' => []
                            ];
                        }
                    } elseif ($currentField && strpos($line, 'FieldType: ') === 0) {
                        $fieldsArray[$currentField]['type'] = substr($line, 11);
                    } elseif ($currentField && strpos($line, 'FieldValue: ') === 0) {
                        $fieldsArray[$currentField]['value'] = substr($line, 12);
                    } elseif ($currentField && strpos($line, 'FieldStateOption: ') === 0) {
                        $fieldsArray[$currentField]['options'][] = substr($line, 18);
                    }
                }
            }
            
            return array_values($fieldsArray); // Return indexed array
            
        } catch (\Exception $e) {
            Log::error('PDFtk get form fields exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Merge multiple PDFs
     */
    public function merge(array $pdfPaths, ?string $outputPath = null): string|false
    {
        try {
            $pdf = $this->createPdf($pdfPaths);
            $pdf->cat(1, 'end');
            
            if ($outputPath) {
                if ($pdf->saveAs($outputPath)) {
                    return $outputPath;
                }
            } else {
                return $pdf->toString();
            }
            
            Log::error('PDFtk merge failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk merge exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Split PDF into individual pages
     */
    public function split(string $pdfPath, string $outputDir): array|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            
            // Create output directory if it doesn't exist
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            $outputPattern = rtrim($outputDir, '/') . '/page_%02d.pdf';
            
            if ($pdf->burst($outputPattern)) {
                // Return array of created files
                $files = glob(rtrim($outputDir, '/') . '/page_*.pdf');
                return $files;
            }
            
            Log::error('PDFtk split failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk split exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Add password protection to PDF
     */
    public function encrypt(string $pdfPath, string $userPassword, ?string $ownerPassword = null, ?string $outputPath = null): string|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            
            if ($ownerPassword) {
                $pdf->passwordProtection($userPassword, $ownerPassword);
            } else {
                $pdf->passwordProtection($userPassword);
            }
            
            if ($outputPath) {
                if ($pdf->saveAs($outputPath)) {
                    return $outputPath;
                }
            } else {
                return $pdf->toString();
            }
            
            Log::error('PDFtk encryption failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk encryption exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove password protection from PDF
     */
    public function decrypt(string $pdfPath, string $password, ?string $outputPath = null): string|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            $pdf->inputPw($password);
            
            if ($outputPath) {
                if ($pdf->saveAs($outputPath)) {
                    return $outputPath;
                }
            } else {
                return $pdf->toString();
            }
            
            Log::error('PDFtk decryption failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk decryption exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rotate PDF pages
     */
    public function rotate(string $pdfPath, string $rotation, string $pages = '1-end', ?string $outputPath = null): string|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            
            // Rotation can be 'left', 'right', 'down', or degrees (90, 180, 270)
            $pdf->rotate($pages . $rotation);
            
            if ($outputPath) {
                if ($pdf->saveAs($outputPath)) {
                    return $outputPath;
                }
            } else {
                return $pdf->toString();
            }
            
            Log::error('PDFtk rotation failed: ' . $pdf->getError());
            return false;
            
        } catch (\Exception $e) {
            Log::error('PDFtk rotation exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get PDF information/metadata
     */
    public function getInfo(string $pdfPath): array|false
    {
        try {
            $pdf = $this->createPdf($pdfPath);
            $data = $pdf->getData();
            
            if ($data === false) {
                Log::error('Failed to get PDF info: ' . $pdf->getError());
                return false;
            }
            
            // Convert InfoFields object to array if needed
            if (is_object($data)) {
                // Parse the InfoFields object to extract useful information
                $infoArray = [];
                
                // Try to convert to string and parse
                $dataString = (string) $data;
                $lines = explode("\n", $dataString);
                
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    
                    // Parse key-value pairs like "InfoBegin\nInfoKey: Title\nInfoValue: Document Title"
                    if (strpos($line, 'InfoKey: ') === 0) {
                        $key = substr($line, 9);
                        continue;
                    }
                    if (strpos($line, 'InfoValue: ') === 0 && isset($key)) {
                        $value = substr($line, 11);
                        $infoArray[$key] = $value;
                        unset($key);
                        continue;
                    }
                    
                    // Handle other common PDF info patterns
                    if (strpos($line, ': ') !== false) {
                        [$k, $v] = explode(': ', $line, 2);
                        $infoArray[$k] = $v;
                    }
                }
                
                // Add some default values if parsing failed
                if (empty($infoArray)) {
                    $infoArray = [
                        'Title' => 'Unknown',
                        'NumberOfPages' => 1,
                        'PageMediaDimensions' => 'Unknown'
                    ];
                }
                
                return $infoArray;
            }
            
            return is_array($data) ? $data : [];
            
        } catch (\Exception $e) {
            Log::error('PDFtk get info exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper method to work with Laravel Storage
     */
    public function fillFormFromStorage(string $templateStoragePath, array $data, ?string $outputStoragePath = null): string|false
    {
        $templatePath = Storage::path($templateStoragePath);
        
        if ($outputStoragePath) {
            $outputPath = Storage::path($outputStoragePath);
            return $this->fillForm($templatePath, $data, $outputPath);
        } else {
            return $this->fillForm($templatePath, $data);
        }
    }
}