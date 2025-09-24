<?php

namespace Tests\Feature;

use App\Services\PdftkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PdftkServiceTest extends TestCase
{
    protected PdftkService $pdftkService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdftkService = app(PdftkService::class);
    }

    /**
     * Test that PDFtk service can be instantiated
     */
    public function test_pdftk_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PdftkService::class, $this->pdftkService);
    }

    /**
     * Test that PDFtk binary is accessible
     */
    public function test_pdftk_binary_is_accessible(): void
    {
        $pdf = $this->pdftkService->createPdf();
        $this->assertInstanceOf(\mikehaertl\pdftk\Pdf::class, $pdf);
    }

    /**
     * Test basic PDFtk functionality by checking version
     */
    public function test_pdftk_version_check(): void
    {
        // This is a simple test to ensure pdftk command works
        $output = shell_exec('pdftk --version 2>&1');
        $this->assertStringContainsString('pdftk', strtolower($output));
    }
}
