# Docker Setup Guide

This Laravel application includes a complete Docker setup using Laravel Sail with custom PDFtk support.

## Prerequisites

- **Docker Desktop**: Install from [docker.com](https://www.docker.com/products/docker-desktop)
- **Docker Compose**: Usually included with Docker Desktop

## Quick Start

### 1. Start the Application

```bash
# Build and start all services
./sail up -d

# Or using the vendor command directly
./vendor/bin/sail up -d
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
./sail composer install

# Install Node.js dependencies
./sail npm install
```

### 3. Set Up the Database

```bash
# Run database migrations
./sail artisan migrate

# (Optional) Seed the database
./sail artisan db:seed
```

### 4. Access the Application

- **Laravel App**: http://localhost
- **Database**: localhost:3306 (from host machine)

## Available Services

### Laravel Application
- **Container**: `laravel.test`
- **Port**: 80 (host) → 80 (container)
- **Features**:
  - PHP 8.4
  - Laravel 12
  - PDFtk for PDF manipulation
  - Node.js for asset compilation
  - Xdebug (configurable)

### MySQL Database
- **Container**: `mysql`
- **Port**: 3306 (host) → 3306 (container)
- **Credentials**:
  - Database: `laravel`
  - Username: `sail`
  - Password: `password`

## Common Commands

### Application Management
```bash
# Start services in background
./sail up -d

# Stop services
./sail down

# View logs
./sail logs

# Access container shell
./sail shell

# Run Artisan commands
./sail artisan migrate
./sail artisan test
```

### Development
```bash
# Install PHP packages
./sail composer install
./sail composer require package-name

# Install Node packages
./sail npm install
./sail npm run dev
./sail npm run build

# Run tests
./sail test
./sail artisan test --filter=PdftkServiceTest
```

### Database Operations
```bash
# Run migrations
./sail artisan migrate

# Fresh migration with seeding
./sail artisan migrate:fresh --seed

# Access MySQL CLI
./sail mysql

# Backup database
./sail exec mysql mysqldump -u sail -ppassword laravel > backup.sql
```

### PDFtk Operations
```bash
# Verify PDFtk installation
./sail exec laravel.test pdftk --version

# Test PDFtk service
./sail artisan test --filter=PdftkServiceTest

# Access PDFtk directly
./sail exec laravel.test pdftk input.pdf output output.pdf
```

## Configuration

### Environment Variables

Key Docker/Sail environment variables in `.env`:

```env
# Port Configuration
APP_PORT=80                    # Laravel application port
VITE_PORT=5173                # Vite development server port
FORWARD_DB_PORT=3306          # Database port forwarding

# Docker Configuration  
WWWGROUP=1000                 # Web server group ID
WWWUSER=1337                  # Web server user ID

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql                 # Docker service name
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# PDFtk Configuration
PDFTK_BINARY_PATH=pdftk       # PDFtk binary path in container
```

### Custom Dockerfile

The application uses a custom Dockerfile (`docker/8.4/Dockerfile`) that extends Laravel Sail with:
- PDFtk binary installation
- Java Runtime Environment (required for PDFtk)
- PDF processing capabilities

## Development Workflow

### 1. Start Development Environment
```bash
./sail up -d
```

### 2. Install Dependencies
```bash
./sail composer install
./sail npm install
```

### 3. Asset Compilation (with hot reload)
```bash
./sail npm run dev
```

### 4. Run Tests
```bash
./sail test
```

## Troubleshooting

### Port Conflicts
If ports 80 or 3306 are already in use:

1. Stop other services using those ports
2. Or change ports in `.env`:
   ```env
   APP_PORT=8080
   FORWARD_DB_PORT=33060
   ```

### Permission Issues
```bash
# Fix storage permissions
./sail exec laravel.test chown -R sail:sail storage bootstrap/cache
```

### PDFtk Issues
```bash
# Verify PDFtk installation
./sail exec laravel.test pdftk --version

# Check Java installation
./sail exec laravel.test java -version

# Test PDF operations
./sail artisan test --filter=PdftkServiceTest
```

### Container Issues
```bash
# Rebuild containers
./sail build --no-cache

# Reset everything
./sail down -v
./sail up -d --build
```

## Performance Optimization

### Production Build
```bash
# Build production assets
./sail npm run build

# Optimize Laravel
./sail artisan optimize
./sail artisan config:cache
./sail artisan route:cache
./sail artisan view:cache
```

### Database Optimization
```bash
# Optimize database
./sail artisan optimize:clear
./sail artisan migrate --force
```

## File Structure

```
/
├── docker/
│   └── 8.4/
│       ├── Dockerfile          # Custom Dockerfile with PDFtk
│       ├── start-container     # Container startup script
│       ├── supervisord.conf    # Process supervisor config
│       └── php.ini            # PHP configuration
├── docker-compose.yml         # Docker Compose configuration
├── sail                      # Sail wrapper script
└── .env                     # Environment configuration
```

## Security Notes

- Default credentials are for development only
- Change all passwords for production
- Use secure database passwords
- Restrict network access in production
- Use HTTPS in production environments

---

**Need Help?** 
- Check the [Laravel Sail documentation](https://laravel.com/docs/sail)
- Review the main project README.md
- Run `./sail --help` for available commands