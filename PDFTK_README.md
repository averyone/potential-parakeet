# PDFtk Integration for Laravel

This Laravel application includes a comprehensive PDFtk integration for PDF form filling and manipulation.

## ✅ Installation Complete

The following components have been successfully installed and configured:

- **PDFtk Binary**: `pdftk-java` v3.3.3 (via Homebrew)
- **PHP Package**: `mikehaertl/php-pdftk` v0.14.0
- **Laravel Service**: `App\Services\PdftkService`
- **Configuration**: `config/pdftk.php`
- **API Controller**: `App\Http\Controllers\PdfController`
- **Routes**: `/pdf/*` endpoints
- **Tests**: PHPUnit tests for service validation

## Quick Start

### 1. Basic Usage in Controllers

```php
use App\Services\PdftkService;

class YourController extends Controller 
{
    public function __construct(private PdftkService $pdftkService) {}

    public function fillForm()
    {
        // Fill a PDF form
        $filledPdf = $this->pdftkService->fillForm(
            storage_path('app/pdf/templates/form.pdf'),
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'date' => date('Y-m-d')
            ]
        );

        return response($filledPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="filled-form.pdf"'
        ]);
    }
}
```

### 2. Available API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/pdf/form-fields` | POST | Extract form fields from PDF |
| `/pdf/fill-form` | POST | Fill PDF form with data |
| `/pdf/merge` | POST | Merge multiple PDFs |
| `/pdf/split` | POST | Split PDF into pages |
| `/pdf/info` | POST | Get PDF metadata |

### 3. Testing PDFtk Integration

```bash
# Run PDFtk-specific tests
php artisan test --filter=PdftkServiceTest

# Verify PDFtk binary is working
pdftk --version
```

## Service Methods

### Core Operations

- `fillForm($templatePath, $data, $outputPath = null)` - Fill PDF forms
- `getFormFields($pdfPath)` - Extract form field information  
- `merge($pdfPaths, $outputPath = null)` - Combine multiple PDFs
- `split($pdfPath, $outputDir)` - Split PDF into individual pages
- `encrypt($pdfPath, $userPassword, $ownerPassword, $outputPath)` - Add password protection
- `decrypt($pdfPath, $password, $outputPath)` - Remove password protection
- `rotate($pdfPath, $rotation, $pages, $outputPath)` - Rotate PDF pages
- `getInfo($pdfPath)` - Get PDF metadata

### Laravel Storage Integration

- `fillFormFromStorage($templateStoragePath, $data, $outputStoragePath)` - Work with Laravel Storage

## Configuration

Edit `config/pdftk.php` to customize:

- Binary path
- Storage locations  
- File size limits
- Security settings
- Error handling

## Example: Complete Form Filling Workflow

```php
// 1. Get form fields to understand the structure
$fields = $this->pdftkService->getFormFields($templatePath);

// 2. Prepare data based on field names
$formData = [];
foreach ($fields as $field) {
    $fieldName = $field['FieldName'];
    $formData[$fieldName] = $this->getUserInput($fieldName);
}

// 3. Fill the form
$filledPdf = $this->pdftkService->fillForm($templatePath, $formData);

// 4. Save or return the result
if ($filledPdf) {
    Storage::put('pdf/generated/filled-' . time() . '.pdf', $filledPdf);
}
```

## Troubleshooting

### Common Issues

1. **PDFtk not found**: Ensure `pdftk-java` is installed and accessible
2. **Permission errors**: Check storage directory permissions
3. **Memory issues**: Adjust PHP memory limits for large PDFs
4. **Form field issues**: Use `getFormFields()` to inspect field names

### Debugging

Enable detailed logging in `.env`:
```env
PDFTK_LOG_ERRORS=true
LOG_LEVEL=debug
```

Check logs at `storage/logs/laravel.log` for detailed error information.

## Storage Structure

```
storage/app/
├── pdf/
│   ├── templates/          # Store your PDF templates here
│   ├── generated/          # Filled/processed PDFs
│   └── temp/              # Temporary processing files
└── temp/                  # General temporary files
```

---

**Need help?** Check the full documentation in `/WARP.md` or run the test suite to verify everything is working correctly.