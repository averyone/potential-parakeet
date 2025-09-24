# 🐳 Docker Setup for PDF WYSIWYG Editor

This document explains how to run the PDF WYSIWYG Editor in Docker containers.

## 📋 Prerequisites

1. **Docker Desktop** installed and running
2. **Docker Compose** (included with Docker Desktop)
3. At least **2GB** of free RAM
4. **Port 8080** available on your machine

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)
```bash
./docker-start.sh
```

### Option 2: Manual Setup
```bash
# 1. Build containers
docker-compose build

# 2. Start services
docker-compose up -d

# 3. Install dependencies
docker-compose exec laravel.test composer install --optimize-autoloader
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build

# 4. Set up database and storage
docker-compose exec laravel.test php artisan migrate --force
docker-compose exec laravel.test mkdir -p storage/app/pdf/{templates,generated,sessions,temp}
```

## 🌐 Access Points

- **PDF Editor**: http://localhost:8080/pdf-editor
- **Laravel App**: http://localhost:8080
- **MySQL**: localhost:3307 (if using MySQL instead of SQLite)

## 📁 Project Structure in Container

```
/var/www/html/
├── app/
│   ├── Http/Controllers/PdfEditorController.php
│   └── Services/PdfEditorService.php
├── resources/
│   ├── js/components/
│   │   ├── PdfEditor.vue
│   │   └── EditableOverlay.vue
│   └── views/pdf/editor.blade.php
├── storage/app/pdf/
│   ├── templates/     # Uploaded PDF templates
│   ├── generated/     # Generated/edited PDFs
│   ├── sessions/      # Editor sessions
│   └── temp/          # Temporary files
└── public/build/      # Compiled assets
```

## 🔧 Docker Commands

### Container Management
```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f laravel.test
```

### Development Commands
```bash
# Access container shell
docker-compose exec laravel.test bash

# Run Artisan commands
docker-compose exec laravel.test php artisan migrate
docker-compose exec laravel.test php artisan route:list

# Run tests
docker-compose exec laravel.test php artisan test

# Install new Composer packages
docker-compose exec laravel.test composer require package/name

# Install new NPM packages
docker-compose exec laravel.test npm install package-name

# Build assets for development (with hot reload)
docker-compose exec laravel.test npm run dev

# Build assets for production
docker-compose exec laravel.test npm run build
```

### PDF Editor Specific Commands
```bash
# Test PDFtk installation
docker-compose exec laravel.test pdftk --version

# Clear PDF cache/sessions
docker-compose exec laravel.test rm -rf storage/app/pdf/sessions/*
docker-compose exec laravel.test rm -rf storage/app/pdf/temp/*

# Check storage permissions
docker-compose exec laravel.test ls -la storage/app/pdf/
```

## 🔍 Troubleshooting

### Container Won't Start
1. Check if Docker is running: `docker info`
2. Check port availability: `lsof -i :8080`
3. View container logs: `docker-compose logs laravel.test`

### PDFtk Issues
1. Verify installation: `docker-compose exec laravel.test pdftk --version`
2. Check file permissions in storage directory
3. Ensure uploaded PDFs are valid

### Frontend Issues
1. Rebuild assets: `docker-compose exec laravel.test npm run build`
2. Clear browser cache
3. Check browser console for JavaScript errors

### Storage Issues
```bash
# Fix storage permissions
docker-compose exec laravel.test chown -R sail:sail storage/
docker-compose exec laravel.test chmod -R 755 storage/
```

## 📝 Environment Variables

Key variables in `.env` file:
```env
# Application
APP_URL=http://localhost:8080
APP_PORT=8080

# Database (using SQLite by default)
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite

# PDFtk Configuration
PDFTK_BINARY_PATH=pdftk
PDFTK_STORAGE_DISK=local
PDFTK_TEMPLATES_PATH=pdf/templates
PDFTK_GENERATED_PATH=pdf/generated
PDFTK_TEMP_PATH=pdf/temp
PDFTK_MAX_FILE_SIZE=10485760
```

## 🧪 Testing the Editor

1. **Generate Test PDF**:
   - Open `create_test_pdf.html` in your browser
   - Print to PDF (Ctrl+P / Cmd+P)
   - Save the PDF

2. **Upload to Editor**:
   - Visit http://localhost:8080/pdf-editor
   - Click "Load PDF"
   - Upload your test PDF
   - Edit form fields
   - Export the result

3. **API Testing**:
   ```bash
   # List sessions
   curl http://localhost:8080/pdf-editor/sessions
   
   # Test basic connectivity
   curl -I http://localhost:8080/pdf-editor
   ```

## 🔄 Updates and Maintenance

### Updating Dependencies
```bash
# Update Composer packages
docker-compose exec laravel.test composer update

# Update NPM packages
docker-compose exec laravel.test npm update

# Rebuild containers after major updates
docker-compose build --no-cache
```

### Database Operations
```bash
# Run migrations
docker-compose exec laravel.test php artisan migrate

# Rollback migrations
docker-compose exec laravel.test php artisan migrate:rollback

# Fresh migration (WARNING: Drops all data)
docker-compose exec laravel.test php artisan migrate:fresh
```

## 📊 Performance Optimization

### Production Build
```bash
# Optimize for production
docker-compose exec laravel.test composer install --no-dev --optimize-autoloader
docker-compose exec laravel.test php artisan config:cache
docker-compose exec laravel.test php artisan route:cache
docker-compose exec laravel.test php artisan view:cache
docker-compose exec laravel.test npm run build
```

### Memory Limits
If you encounter memory issues, increase Docker's memory allocation:
- Docker Desktop > Settings > Resources > Memory > 4GB+

## 🆘 Support

For issues specific to:
- **Docker setup**: Check Docker Desktop logs and this documentation
- **Laravel issues**: Run `docker-compose logs laravel.test`
- **PDF processing**: Verify PDFtk with `docker-compose exec laravel.test pdftk --version`
- **Frontend issues**: Check browser console and rebuild assets

## 📚 Additional Resources

- [Laravel Sail Documentation](https://laravel.com/docs/sail)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PDFtk Documentation](https://www.pdflabs.com/tools/pdftk-the-pdf-toolkit/)
- [Vue.js Documentation](https://vuejs.org/guide/)
- [PDF.js Documentation](https://mozilla.github.io/pdf.js/)