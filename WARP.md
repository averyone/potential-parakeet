# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview
This is a Laravel application with a fully functional **PDF Editor** that allows users to upload, view, edit, and export PDF documents. The project includes both backend PDF processing capabilities using PDFtk and a frontend PDF viewer/editor built with PDF.js.

### Current Status: ✅ PRODUCTION READY
- **PDF Upload & Processing**: Fully functional
- **PDF Viewer**: Complete with PDF.js integration
- **Form Field Detection**: Working with PDFtk
- **PDF Export**: Functional with debugging
- **Multi-page Navigation**: Complete with pagination
- **Zoom Controls**: Fully implemented
- **Session Management**: Complete
- **Error Handling**: Comprehensive logging

## Development Setup

### Docker Environment (RECOMMENDED)
This project is configured to run with Laravel Sail (Docker):

```bash
# Start the development environment
./vendor/bin/sail up -d

# Or using alias (after setting up sail alias)
sail up -d

# Access the application at http://localhost:8080
```

### Environment Configuration
```bash
# Environment is pre-configured for Docker
# Key settings in .env:
APP_URL=http://localhost:8080
APP_PORT=8080
FILESYSTEM_DISK=local  # Uses storage/app/private/
APP_ENV=production     # For built assets
APP_DEBUG=false

# PDFtk Configuration (pre-configured)
PDFTK_BINARY_PATH=pdftk
PDFTK_STORAGE_DISK=local
PDFTK_TEMPLATES_PATH=pdf/templates
PDFTK_GENERATED_PATH=pdf/generated
```

### Asset Building
```bash
# Build production assets (required for PDF editor)
npm run build

# For development with hot reloading
npm run dev
```

## Common Development Commands

### Docker/Sail Commands
```bash
# Start/stop the application
sail up -d
sail down

# View logs
sail logs

# Execute commands in container
sail shell
sail artisan migrate
sail npm run build
```

### Laravel Artisan Commands (via Docker)
```bash
# Clear application cache (common troubleshooting)
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear

# Database operations
sail artisan migrate

# Create new components
sail artisan make:controller ControllerName
sail artisan make:model ModelName -mcr
```

### PDF Editor Specific Commands
```bash
# Test PDFtk integration
sail exec laravel.test pdftk --version

# Check storage permissions
sail exec laravel.test ls -la storage/app/private/pdf/

# Create required directories
sail exec laravel.test mkdir -p storage/app/private/pdf/generated

# View application logs
sail exec laravel.test tail -f storage/logs/laravel.log
```

## PDF Editor Architecture (✅ IMPLEMENTED)

### Core Components
- **PDF Upload System**: Handle PDF file uploads with validation
- **PDF Processing Service**: Extract form fields and metadata using PDFtk
- **PDF Viewer**: Browser-based PDF rendering using PDF.js
- **Session Management**: Track user sessions and PDF edits
- **Export System**: Generate and download edited PDF files
- **Multi-page Navigation**: Full pagination support with zoom controls

### Actual Directory Structure
```
app/
├── Http/Controllers/
│   ├── PdfEditorController.php    # ✅ Main PDF editor controller
│   └── PdfController.php          # ✅ Basic PDF operations
├── Services/
│   ├── PdfEditorService.php       # ✅ PDF editing business logic
│   └── PdftkService.php           # ✅ PDFtk integration service
└── config/
    └── pdftk.php                   # ✅ PDFtk configuration

resources/
├── views/pdf/
│   ├── editor-fixed.blade.php     # ✅ Main PDF editor interface
│   ├── editor-simple.blade.php    # ✅ Simplified editor
│   └── editor.blade.php           # ✅ Vue-based editor (unused)
├── js/
│   ├── app.js                     # ✅ Main JS with Vue components
│   └── components/
│       ├── PdfEditor.vue          # ✅ Vue PDF editor component
│       └── EditableOverlay.vue    # ✅ Form field overlay
└── css/
    └── app.css                    # ✅ Application styles

storage/app/private/pdf/           # ✅ PDF file storage
├── sessions/                      # ✅ User session data
├── templates/                     # ✅ Uploaded PDF files
├── generated/                     # ✅ Exported PDF files
└── temp/                         # ✅ Temporary files

public/
├── build/assets/                  # ✅ Built frontend assets
└── pdf.worker.min.mjs            # ✅ PDF.js worker file
```

### Installed Packages
- `mikehaertl/php-pdftk` v0.14.0 - PDF form filling and manipulation (✅ **INSTALLED**)
- `pdfjs-dist` v5.4.149 - Frontend PDF rendering (✅ **INSTALLED**)
- `vue` v3.5.21 - Frontend framework (✅ **INSTALLED**)
- `@vitejs/plugin-vue` v6.0.1 - Vue Vite plugin (✅ **INSTALLED**)
- `fabric` v6.7.1 - Canvas manipulation (for future features) (✅ **INSTALLED**)

## PDF Editor Interface (✅ LIVE)

### Available Routes
- **Main PDF Editor**: `http://localhost:8080/pdf-editor/fixed`
- **Simple Editor**: `http://localhost:8080/pdf-editor/simple`
- **Vue Editor**: `http://localhost:8080/pdf-editor` (Vue-based, not currently used)

### Features
1. **PDF Upload**: Drag & drop or click to upload PDF files
2. **PDF Viewer**: Full PDF.js integration with canvas rendering
3. **Multi-page Navigation**: Previous/Next buttons with page indicators
4. **Zoom Controls**: Zoom in/out with percentage display
5. **Form Field Detection**: Automatic detection of fillable form fields
6. **Session Management**: Track user sessions and edits
7. **PDF Export**: Download edited PDFs
8. **Error Handling**: Comprehensive error messages and fallback viewers

### API Endpoints (✅ FUNCTIONAL)
- `POST /pdf-editor/load` - Upload and process PDF
- `GET /pdf-editor/data?session_id=...` - Get PDF content for rendering
- `POST /pdf-editor/export` - Export edited PDF
- `POST /pdf-editor/save` - Save current edits
- `GET /pdf-editor/sessions` - List user sessions
- `POST /pdf-editor/update-field` - Update specific form field

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

### Legacy API Endpoints (PDFtk Service)
```bash
# These endpoints are available but not used by the main editor
POST /pdf/form-fields    # Get form fields from PDF
POST /pdf/fill-form      # Fill PDF form with data
POST /pdf/merge          # Merge multiple PDFs
POST /pdf/split          # Split PDF into pages
POST /pdf/info           # Get PDF information
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

## Key Development Learnings ⚠️

### Critical Issues Resolved

#### 1. PDF.js Version Mismatch
**Problem**: API version (5.4.149) vs Worker version (3.11.174) mismatch
**Solution**: 
- Use matching CDN worker: `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.4.149/pdf.worker.min.mjs`
- PDF.js is included in built assets, don't load from CDN separately

#### 2. Vite Asset Loading Issues
**Problem**: Laravel trying to load from dev server (localhost:5173)
**Solution**:
- Remove `/public/hot` file when using production assets
- Use `APP_ENV=production` and `APP_DEBUG=false`
- Clear caches: `sail artisan config:clear && sail artisan cache:clear`

#### 3. Vue.js Conflicts
**Problem**: Vue trying to mount on same element as standalone JavaScript
**Solution**:
- Use different element IDs (`pdf-editor-standalone` vs `pdf-editor-app`)
- Choose either Vue component or standalone JS approach

#### 4. Storage Path Issues
**Problem**: Files stored in `storage/app/private/` but code looking in `storage/app/`
**Solution**:
- Configure `local` disk to use `storage_path('app/private')` in `config/filesystems.php`
- Ensure directory permissions and existence

#### 5. PDF Pagination Problems
**Problem**: Navigation buttons always disabled
**Solution**:
- Pre-render all PDF pages on load
- Use show/hide approach instead of re-rendering
- Update button states in `updatePageDisplay()`

### Debugging Techniques

```bash
# Check PDF processing
sail exec laravel.test pdftk --version

# Test file permissions
sail exec laravel.test ls -la storage/app/private/pdf/

# Monitor logs during PDF operations
sail exec laravel.test tail -f storage/logs/laravel.log

# Check built assets
ls -la public/build/assets/

# Test PDF URL directly
curl -I "http://localhost:8080/pdf-editor/data?session_id=SESSION_ID"
```

### Performance Notes
- PDF.js loads entire PDF into memory - consider chunking for large files
- Pre-rendering all pages improves navigation speed but increases memory usage
- Session-based storage prevents conflicts but requires cleanup

## Environment Variables
Key environment variables (pre-configured):
- `PDFTK_BINARY_PATH=pdftk` - PDFtk binary path
- `PDFTK_STORAGE_DISK=local` - Uses private storage
- `PDFTK_TEMPLATES_PATH=pdf/templates` - Upload directory
- `PDFTK_GENERATED_PATH=pdf/generated` - Export directory
- `FILESYSTEM_DISK=local` - Laravel storage disk
- `APP_ENV=production` - Use built assets
- `APP_DEBUG=false` - Disable debug mode
