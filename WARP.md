# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview
This is a Laravel application designed for PDF form-filling functionality. The project is currently in early development stage.

## Development Setup

### Initial Laravel Installation
```bash
# Install Laravel via Composer (when setting up the project)
composer create-project laravel/laravel . --prefer-dist

# Or if using Laravel installer
laravel new .
```

### Environment Setup
```bash
# Copy environment file and configure
cp .env.example .env
php artisan key:generate

# Set up database connection in .env file
# Configure PDF-related settings
```

### Dependencies Installation
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (if using frontend assets)
npm install
```

## Common Development Commands

### Laravel Artisan Commands
```bash
# Start development server
php artisan serve

# Run database migrations
php artisan migrate

# Create new migration
php artisan make:migration create_table_name

# Create new model with migration and controller
php artisan make:model ModelName -mcr

# Create new controller
php artisan make:controller ControllerName

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter TestClassName

# Run tests with coverage
php artisan test --coverage
```

### Asset Management
```bash
# Compile assets for development
npm run dev

# Watch for changes during development
npm run watch

# Compile assets for production
npm run build
```

## PDF Form-Filling Architecture

### Key Components (to be developed)
- **PDF Processing Service**: Handle PDF file uploads and form detection
- **Form Field Mapping**: Map form fields to database models
- **Data Population Engine**: Fill PDF forms with dynamic data
- **Export/Download System**: Generate and serve completed PDF forms

### Expected Directory Structure
```
app/
├── Http/Controllers/
│   ├── PdfController.php      # Handle PDF operations
│   └── FormController.php     # Manage form data
├── Models/
│   ├── PdfTemplate.php        # PDF template model
│   └── FormData.php          # Form data model
├── Services/
│   ├── PdfService.php         # Core PDF processing logic
│   └── FormFillerService.php  # Form filling operations
└── Console/Commands/          # Artisan commands for PDF processing

database/
├── migrations/                # Database schema
└── seeders/                  # Sample data

resources/
├── views/
│   └── pdf/                  # PDF-related views
└── js/                       # Frontend assets

storage/
├── app/
│   ├── pdfs/                 # Uploaded PDF templates
│   └── generated/            # Generated PDF files
└── logs/                     # Application logs

tests/
├── Feature/
│   └── PdfProcessingTest.php
└── Unit/
    └── PdfServiceTest.php
```

### Recommended Packages
- `mikehaertl/php-pdftk` - PDF form filling and manipulation (✅ **INSTALLED**)
- `barryvdh/laravel-dompdf` or `mpdf/mpdf` - PDF generation
- `setasign/fpdf` or `tecnickcom/tcpdf` - PDF manipulation
- `spatie/laravel-pdf` - Modern PDF handling
- `league/flysystem` - File storage abstraction

## Database Considerations
The application will likely need tables for:
- `pdf_templates` - Store PDF template metadata
- `form_fields` - Define form field mappings
- `form_submissions` - Track completed forms
- `users` - User management (if multi-user)

## PDFtk Integration (✅ CONFIGURED)

### Installed Components
- **PDFtk Binary**: `pdftk-java` v3.3.3 installed via Homebrew
- **PHP Package**: `mikehaertl/php-pdftk` v0.14.0
- **Laravel Service**: `App\Services\PdftkService` with comprehensive PDF operations
- **Configuration**: `config/pdftk.php` with customizable settings
- **Controller**: `App\Http\Controllers\PdfController` with API endpoints

### Available PDF Operations
1. **Form Field Detection**: Extract all form fields from PDF templates
2. **Form Filling**: Fill PDF forms with dynamic data
3. **PDF Merging**: Combine multiple PDFs into one
4. **PDF Splitting**: Split PDFs into individual pages
5. **PDF Encryption/Decryption**: Add/remove password protection
6. **PDF Rotation**: Rotate pages in any direction
7. **PDF Information**: Extract metadata and document info

### API Endpoints
```bash
# Get form fields from PDF
POST /pdf/form-fields

# Fill PDF form with data
POST /pdf/fill-form

# Merge multiple PDFs
POST /pdf/merge

# Split PDF into pages
POST /pdf/split

# Get PDF information
POST /pdf/info
```

### Usage Examples

#### Using the Service in Controllers
```php
use App\Services\PdftkService;

public function __construct(PdftkService $pdftkService)
{
    $this->pdftkService = $pdftkService;
}

// Fill a form
$result = $this->pdftkService->fillForm(
    '/path/to/template.pdf',
    ['field1' => 'value1', 'field2' => 'value2']
);

// Get form fields
$fields = $this->pdftkService->getFormFields('/path/to/form.pdf');

// Merge PDFs
$merged = $this->pdftkService->merge([
    '/path/to/file1.pdf',
    '/path/to/file2.pdf'
]);
```

#### Using with Laravel Storage
```php
// Fill form using Storage paths
$result = $this->pdftkService->fillFormFromStorage(
    'pdf/templates/template.pdf',
    ['name' => 'John Doe', 'email' => 'john@example.com'],
    'pdf/generated/filled-form.pdf'
);
```

### Testing
Run PDFtk integration tests:
```bash
php artisan test --filter=PdftkServiceTest
```

### Storage Structure
```
storage/app/
├── pdf/
│   ├── templates/          # PDF template files
│   ├── generated/          # Generated/filled PDF files
│   └── temp/              # Temporary processing files
└── temp/                  # General temporary files
```

## Environment Variables
Key environment variables to configure:
- `PDFTK_BINARY_PATH` - Path to PDFtk binary (default: 'pdftk')
- `PDFTK_STORAGE_DISK` - Storage disk for PDF files (default: 'local')
- `PDFTK_TEMPLATES_PATH` - Templates directory (default: 'pdf/templates')
- `PDFTK_GENERATED_PATH` - Generated files directory (default: 'pdf/generated')
- `PDFTK_TEMP_PATH` - Temporary files directory (default: 'pdf/temp')
- `PDFTK_MAX_FILE_SIZE` - Maximum file size in bytes (default: 10MB)
- `PDFTK_FLATTEN_FORMS` - Flatten forms after filling (default: true)
- `PDFTK_LOG_ERRORS` - Enable error logging (default: true)
