# Potential Parakeet 📄✨

**A comprehensive PDF Editor web application built with Laravel and PDF.js**

Upload, view, edit, and export PDF documents with a full-featured browser-based editor. Perfect for PDF form filling, document processing, and multi-page PDF management.

## 🚀 Features

### 🎯 **PDF Editor Interface**
- **📤 Upload & Process**: Drag-and-drop PDF upload with validation
- **👀 PDF Viewer**: Full PDF.js integration with canvas rendering
- **📄 Multi-page Navigation**: Previous/Next buttons with page indicators
- **🔍 Zoom Controls**: Zoom in/out with percentage display
- **📝 Form Field Detection**: Automatic detection of fillable form fields
- **💾 Session Management**: Track user sessions and edits
- **⬇️ PDF Export**: Download edited PDFs with applied changes
- **⚠️ Error Handling**: Comprehensive error messages and fallback viewers

### 🛠️ **Backend PDF Processing (PDFtk)**
- **🔥 Form Filling**: Fill PDF forms with dynamic data
- **🔗 PDF Merging**: Combine multiple PDFs into one document
- **✂️ PDF Splitting**: Split PDFs into individual pages
- **🔒 Encryption/Decryption**: Add/remove password protection
- **🔄 PDF Rotation**: Rotate pages in any direction
- **📊 PDF Information**: Extract metadata and document info

## ⚡ Quick Start

### Prerequisites
- **Docker Desktop** (recommended) - for containerized development
- **Node.js 18+** - for frontend asset building
- **Git** - for version control

### 🐳 Installation (Docker - Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd potential-parakeet

# Start the Docker environment
./vendor/bin/sail up -d

# Build production assets (REQUIRED for PDF editor)
npm run build

# Access the application
open http://localhost:8080
```

**That's it!** The Docker environment includes:
- ✅ PHP 8.4 with Laravel
- ✅ PDFtk-java pre-installed
- ✅ SQLite database pre-configured
- ✅ All required dependencies

### 💻 Alternative: Local Installation

```bash
# System requirements
brew install pdftk-java  # macOS
# OR
sudo apt install pdftk   # Ubuntu/Debian

# Install dependencies
composer install
npm install
npm run build

# Environment setup
cp .env.example .env
php artisan key:generate

# Start development server
php artisan serve  # Access at http://localhost:8000
```

## 🎯 Usage

### 🖼️ PDF Editor Web Interface (Primary)

**Main PDF Editor**: [http://localhost:8080/pdf-editor/fixed](http://localhost:8080/pdf-editor/fixed)

1. **Upload PDF**: Click "Load PDF" or drag & drop a PDF file
2. **View & Navigate**: Use Previous/Next buttons for multi-page PDFs
3. **Zoom**: Use +/- buttons to zoom in/out
4. **Edit Forms**: Detected form fields will appear in the properties panel
5. **Export**: Click "Export" to download the edited PDF

**Features Available**:
- ✅ **Real-time PDF rendering** with PDF.js
- ✅ **Multi-page navigation** with smooth transitions
- ✅ **Zoom controls** (50% to 300%)
- ✅ **Form field detection** and editing
- ✅ **Session management** for concurrent users
- ✅ **Export with applied edits**

### 📝 Alternative Interfaces
- **Simple Editor**: [http://localhost:8080/pdf-editor/simple](http://localhost:8080/pdf-editor/simple)
- **Vue Editor**: [http://localhost:8080/pdf-editor](http://localhost:8080/pdf-editor) (development)

### 🛠️ Programmatic Usage (Advanced)

```php
use App\Services\PdfEditorService;

// Inject the service in your controller
public function __construct(private PdfEditorService $pdfEditorService) {}

// Process a PDF upload
$sessionId = $this->pdfEditorService->createSession($pdfPath);

// Get form fields
$fields = $this->pdfEditorService->getEditableRegions($pdfPath);

// Apply edits and export
$this->pdfEditorService->applyEdits($pdfPath, $edits, $outputPath);
```

## 🔌 API Endpoints

### 🎯 PDF Editor API (Primary)

| Endpoint | Method | Purpose | Status |
|----------|--------|---------|--------|
| `/pdf-editor/fixed` | GET | Main PDF editor interface | ✅ Live |
| `/pdf-editor/load` | POST | Upload and process PDF | ✅ Functional |
| `/pdf-editor/data?session_id=...` | GET | Get PDF content for rendering | ✅ Functional |
| `/pdf-editor/export` | POST | Export edited PDF | ✅ Functional |
| `/pdf-editor/save` | POST | Save current edits | ✅ Functional |
| `/pdf-editor/sessions` | GET | List user sessions | ✅ Functional |
| `/pdf-editor/update-field` | POST | Update form field value | ✅ Functional |

### 🛠️ PDFtk API (Legacy/Advanced)

| Endpoint | Method | Purpose | Status |
|----------|--------|---------|--------|
| `/pdf/form-fields` | POST | Extract form fields from PDF | ✅ Available |
| `/pdf/fill-form` | POST | Fill PDF form with data | ✅ Available |
| `/pdf/merge` | POST | Merge multiple PDFs | ✅ Available |
| `/pdf/split` | POST | Split PDF into pages | ✅ Available |
| `/pdf/info` | POST | Get PDF metadata | ✅ Available |

## 🌐 Access URLs

### 🎯 **PDF Editor Interfaces**
- **Main PDF Editor**: [http://localhost:8080/pdf-editor/fixed](http://localhost:8080/pdf-editor/fixed) ⭐ **Recommended**
- **Simple Editor**: [http://localhost:8080/pdf-editor/simple](http://localhost:8080/pdf-editor/simple)
- **Vue Editor**: [http://localhost:8080/pdf-editor](http://localhost:8080/pdf-editor) (development)

### 🛠️ **Application Access**
- **Laravel App (Docker)**: [http://localhost:8080](http://localhost:8080) ⭐ **Default**
- **Laravel App (Local)**: [http://localhost:8000](http://localhost:8000)
- **Database (Docker)**: `localhost:3307` (MySQL)
- **Database (Local)**: `localhost:3306` (MySQL)

## ⚙️ Testing & Debugging

```bash
# Docker commands (recommended)
sail test                                    # Run all tests
sail test --filter=PdftkServiceTest         # Run specific tests
sail exec laravel.test pdftk --version      # Test PDFtk installation
sail logs                                    # View application logs

# PDF Editor specific debugging
sail exec laravel.test tail -f storage/logs/laravel.log  # Monitor PDF processing
sail exec laravel.test ls -la storage/app/private/pdf/   # Check file storage

# Local commands
php artisan test
pdftk --version
```

## 📱 Screenshots

### PDF Editor Interface

**Main Editor View**:
- 📤 **Upload Area**: Drag & drop PDF files
- 🖼️ **PDF Viewer**: Canvas-based PDF rendering with PDF.js
- 🔄 **Navigation**: Previous/Next buttons with page indicators
- 🔍 **Zoom Controls**: Zoom in/out with percentage display
- 💾 **Export**: Download edited PDFs

**Features in Action**:
1. ✅ **Upload PDF**: Instant processing and form field detection
2. ✅ **Multi-page Navigation**: Smooth page transitions
3. ✅ **Zoom & View**: Responsive PDF rendering
4. ✅ **Form Editing**: Interactive form field modification
5. ✅ **Export**: Download PDFs with applied changes

**Browser Compatibility**: ✅ Chrome, ✅ Firefox, ✅ Safari, ✅ Edge

## 📚 Documentation

- **Development Guide**: See `WARP.md` for comprehensive development guidelines ⭐
- **PDFtk Integration**: Review `config/pdftk.php` for PDFtk configuration
- **API Documentation**: All endpoints documented above with status indicators
- **Troubleshooting**: Check `WARP.md` for common issues and solutions

## 📁 Storage Structure

```
storage/app/private/pdf/       # ✅ PDF file storage (secure)
├── sessions/              # ✅ User session data (JSON)
├── templates/             # ✅ Uploaded PDF files
├── generated/             # ✅ Exported PDF files
└── temp/                 # ✅ Temporary processing files

public/
├── build/assets/          # ✅ Built frontend assets (Vite)
└── pdf.worker.min.mjs    # ✅ PDF.js worker file

resources/
├── views/pdf/            # ✅ PDF editor Blade templates
├── js/components/        # ✅ Vue.js components
└── css/                 # ✅ Application styles
```

## 💻 Technology Stack

### **Backend**
- **Laravel 11**: PHP framework with Sail (Docker)
- **PHP 8.4**: Latest PHP version with modern features
- **PDFtk-java**: PDF manipulation and form processing
- **SQLite**: Lightweight database for sessions

### **Frontend**
- **PDF.js 5.4.149**: Browser-based PDF rendering
- **Vue.js 3.5.21**: Frontend framework (optional)
- **Vite**: Modern build tool and dev server
- **Vanilla JS**: Primary PDF editor implementation

### **DevOps**
- **Docker/Sail**: Containerized development environment
- **NPM**: Package management and build scripts

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
