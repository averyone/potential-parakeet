# Potential Parakeet

A Laravel 12 application for PDF form filling and manipulation using PDFtk.

## Features

🔥 **PDF Form Filling**: Fill PDF forms with dynamic data
📄 **Form Field Detection**: Extract form fields from PDF templates  
🔗 **PDF Merging**: Combine multiple PDFs into one document
✂️ **PDF Splitting**: Split PDFs into individual pages
🔒 **PDF Encryption/Decryption**: Add/remove password protection
🔄 **PDF Rotation**: Rotate pages in any direction
📊 **PDF Information**: Extract metadata and document info

## Quick Start

### Prerequisites

- PHP 8.2+
- Laravel 12
- PDFtk (installed via `brew install pdftk-java`)

### Installation

#### Option 1: Docker (Recommended)

```bash
# Start Docker containers (includes PHP, MySQL, PDFtk)
./vendor/bin/sail up -d

# Install dependencies inside Docker
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# Run database migrations
./vendor/bin/sail artisan migrate
```

#### Option 2: Local Installation

```bash
# Install dependencies locally
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Install PDFtk (macOS)
brew install pdftk-java

# Start development server
php artisan serve
```

### Basic Usage

```php
use App\Services\PdftkService;

// Inject the service in your controller
public function __construct(private PdftkService $pdftkService) {}

// Fill a PDF form
$filledPdf = $this->pdftkService->fillForm(
    storage_path('app/pdf/templates/form.pdf'),
    ['name' => 'John Doe', 'email' => 'john@example.com']
);
```

## API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/pdf/form-fields` | POST | Extract form fields from PDF |
| `/pdf/fill-form` | POST | Fill PDF form with data |
| `/pdf/merge` | POST | Merge multiple PDFs |
| `/pdf/split` | POST | Split PDF into pages |
| `/pdf/info` | POST | Get PDF metadata |

## Access URLs

- **Laravel App (Docker)**: http://localhost:8080
- **Laravel App (Local)**: http://localhost:8000
- **Database (Docker)**: localhost:3307
- **Database (Local)**: localhost:3306

## Testing

```bash
# Docker commands
./vendor/bin/sail test
./vendor/bin/sail test --filter=PdftkServiceTest
./vendor/bin/sail exec laravel.test pdftk --version

# Local commands
php artisan test
php artisan test --filter=PdftkServiceTest
pdftk --version
```

## 🌐 Web Interface

The application includes a comprehensive web interface for PDF processing:

- **Main Interface**: http://localhost:8080/pdf (Docker) or http://localhost:8000/pdf (Local)
- **Features**: Form analysis, PDF filling, merging, splitting, and information extraction
- **User-Friendly**: Drag & drop file uploads, real-time processing, automatic downloads
- **Responsive**: Works on desktop, tablet, and mobile devices

### Quick Web Interface Guide
1. **Analyze PDF**: Upload a PDF to discover its form fields
2. **Fill Forms**: Enter data and download completed forms
3. **Merge PDFs**: Combine multiple PDFs into one document
4. **Split PDFs**: Extract individual pages from multi-page documents
5. **Get Info**: View detailed PDF metadata and properties

## Documentation

- **Web Interface**: See `WEB_INTERFACE_README.md` for complete web interface guide
- **Docker Setup**: See `DOCKER_README.md` for complete Docker setup and usage guide
- **PDFtk Integration**: See `PDFTK_README.md` for detailed PDFtk integration documentation
- **Development**: Check `WARP.md` for development guidelines and project structure
- **Configuration**: Review `config/pdftk.php` for customization options

## Storage Structure

```
storage/app/
├── pdf/
│   ├── templates/          # PDF template files
│   ├── generated/          # Generated/filled PDFs
│   └── temp/              # Temporary processing files
└── temp/                  # General temporary files
```

## Technology Stack

- **Backend**: Laravel 12, PHP 8.2
- **PDF Processing**: PDFtk (pdftk-java), mikehaertl/php-pdftk
- **Testing**: PHPUnit
- **Frontend**: Vite, Laravel Mix

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
