#!/bin/bash

# PDF WYSIWYG Editor - Docker Startup Script
# This script sets up and starts the PDF editor in Docker containers

echo "🐳 Starting PDF WYSIWYG Editor in Docker..."

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker Desktop first."
    exit 1
fi

echo "🔨 Building Docker containers..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

echo "📦 Installing Composer dependencies..."
docker-compose exec laravel.test composer install --optimize-autoloader

echo "🎨 Installing Node.js dependencies..."
docker-compose exec laravel.test npm install

echo "🏗️  Building frontend assets..."
docker-compose exec laravel.test npm run build

echo "📁 Creating storage directories..."
docker-compose exec laravel.test mkdir -p storage/app/pdf/{templates,generated,sessions,temp}

echo "📄 Running database migrations..."
docker-compose exec laravel.test php artisan migrate --force

echo "🔍 Testing PDFtk installation..."
docker-compose exec laravel.test pdftk --version

echo ""
echo "✅ PDF WYSIWYG Editor is now running!"
echo ""
echo "🌐 Access the editor at: http://localhost:8080/pdf-editor"
echo "📊 API endpoints available at: http://localhost:8080/pdf-editor/*"
echo ""
echo "📋 Useful Docker commands:"
echo "   View logs:     docker-compose logs -f"
echo "   Stop services: docker-compose down"
echo "   Shell access:  docker-compose exec laravel.test bash"
echo "   Run tests:     docker-compose exec laravel.test php artisan test"
echo ""
echo "📁 Test PDF generation:"
echo "   Open: file://$(pwd)/create_test_pdf.html"
echo "   Print to PDF and upload to the editor"
echo ""