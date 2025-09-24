# 🔧 PDF Editor Troubleshooting Guide

## 🚨 **Current Issue: Web Interface Spinning**

The spinning issue is likely caused by Vue.js failing to load or mount properly. Here's how to diagnose and fix it:

## 🔍 **Diagnostic Steps**

### Step 1: Test Simple Version
Visit the simple test page to isolate the issue:
```
http://localhost:8080/pdf-editor/simple
```

This page will show:
- ✅ Whether Vue.js is loading
- ✅ Whether the API is working
- ✅ Basic functionality test

### Step 2: Check Browser Console
1. Open Developer Tools (F12)
2. Go to Console tab
3. Look for errors related to:
   - Vue.js loading
   - PDF.js worker
   - Network requests

### Step 3: Verify Services
```bash
# Check if containers are running
docker-compose ps

# Check Laravel logs
docker-compose logs laravel.test --tail=20

# Test API directly
curl http://localhost:8080/pdf-editor/sessions
```

## 🛠️ **Quick Fixes**

### Fix 1: Rebuild Assets
```bash
docker-compose exec laravel.test npm run build
```

### Fix 2: Copy PDF.js Worker
```bash
docker-compose exec laravel.test cp node_modules/pdfjs-dist/build/pdf.worker.min.mjs public/pdf.worker.min.mjs
```

### Fix 3: Clear Browser Cache
- Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
- Clear all browser cache
- Try incognito/private window

### Fix 4: Check File Permissions
```bash
docker-compose exec laravel.test chmod -R 755 storage/
docker-compose exec laravel.test chmod -R 755 public/build/
```

### Fix 5: Restart Everything
```bash
docker-compose down
docker-compose up -d
```

## 🐛 **Common Issues & Solutions**

### Vue.js Not Loading
**Symptoms:** Blank page, spinning indefinitely
**Solution:**
```bash
# Check if Vue files exist
docker-compose exec laravel.test ls -la resources/js/components/

# Rebuild with verbose output
docker-compose exec laravel.test npm run build -- --verbose
```

### PDF.js Worker Missing
**Symptoms:** PDF loading fails after file upload
**Solution:**
```bash
# Verify worker file exists
curl -I http://localhost:8080/pdf.worker.min.mjs

# If not found, copy it:
docker-compose exec laravel.test cp node_modules/pdfjs-dist/build/pdf.worker.min.mjs public/
```

### API Endpoints Not Working
**Symptoms:** Network errors, 404s
**Solution:**
```bash
# Check routes
docker-compose exec laravel.test php artisan route:list | grep pdf-editor

# Test API
curl -X GET http://localhost:8080/pdf-editor/sessions
```

### Database Issues
**Symptoms:** Server errors, migration failures
**Solution:**
```bash
# Run migrations
docker-compose exec laravel.test php artisan migrate --force

# Check database
docker-compose exec laravel.test php artisan tinker
# In tinker: DB::connection()->getPdo();
```

## 🧪 **Testing Commands**

### Test the Complete Stack
```bash
# 1. Test basic connectivity
curl -I http://localhost:8080

# 2. Test PDF editor page
curl -I http://localhost:8080/pdf-editor

# 3. Test simple version
curl -I http://localhost:8080/pdf-editor/simple

# 4. Test API
curl http://localhost:8080/pdf-editor/sessions

# 5. Test asset loading
curl -I http://localhost:8080/pdf.worker.min.mjs
```

### Test Individual Components
```bash
# Test PDFtk
docker-compose exec laravel.test pdftk --version

# Test Node/NPM
docker-compose exec laravel.test node --version
docker-compose exec laravel.test npm --version

# Test Vue components exist
docker-compose exec laravel.test ls -la resources/js/components/

# Test compiled assets
docker-compose exec laravel.test ls -la public/build/assets/
```

## 📋 **Debug Checklist**

- [ ] Docker containers running (`docker-compose ps`)
- [ ] Laravel serving on port 8080 (`curl -I http://localhost:8080`)
- [ ] Assets compiled (`ls public/build/assets/`)
- [ ] PDF.js worker accessible (`curl -I http://localhost:8080/pdf.worker.min.mjs`)
- [ ] Vue.js components exist (`ls resources/js/components/`)
- [ ] API endpoints working (`curl http://localhost:8080/pdf-editor/sessions`)
- [ ] Browser console clear of errors
- [ ] Simple test page working (`http://localhost:8080/pdf-editor/simple`)

## 🚀 **Complete Reset (Nuclear Option)**

If all else fails, completely reset:

```bash
# Stop everything
docker-compose down

# Remove all containers and volumes
docker-compose down -v
docker system prune -f

# Rebuild from scratch
docker-compose build --no-cache
docker-compose up -d

# Reinstall everything
docker-compose exec laravel.test composer install --optimize-autoloader
docker-compose exec laravel.test npm install
docker-compose exec laravel.test npm run build
docker-compose exec laravel.test php artisan migrate --force
docker-compose exec laravel.test mkdir -p storage/app/pdf/{templates,generated,sessions,temp}
docker-compose exec laravel.test cp node_modules/pdfjs-dist/build/pdf.worker.min.mjs public/
```

## 🆘 **Get Help**

If you're still having issues:

1. **Check the simple test page:** http://localhost:8080/pdf-editor/simple
2. **Check browser console** for JavaScript errors
3. **Check Docker logs:** `docker-compose logs laravel.test`
4. **Verify all services** using the checklist above

## 📞 **Support URLs**

- **Main PDF Editor:** http://localhost:8080/pdf-editor
- **Simple Test:** http://localhost:8080/pdf-editor/simple  
- **Vue Debug:** http://localhost:8080/debug-vue
- **API Test:** http://localhost:8080/pdf-editor/sessions
- **Laravel Welcome:** http://localhost:8080