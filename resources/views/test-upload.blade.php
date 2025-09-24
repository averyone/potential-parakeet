<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Upload Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; }
        .btn:hover { background: #0056b3; }
        .result { margin-top: 10px; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .log { background: #f8f9fa; color: #495057; border: 1px solid #dee2e6; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>PDF Upload Test & Debug</h1>
    <p>This page will help diagnose PDF upload issues step by step.</p>
    
    <div class="test-section">
        <h3>1. Test CSRF Token</h3>
        <button class="btn" onclick="testCSRF()">Test CSRF</button>
        <div id="csrf-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>2. Test API Connectivity</h3>
        <button class="btn" onclick="testAPI()">Test API</button>
        <div id="api-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>3. Test PDF Upload</h3>
        <input type="file" id="pdf-file" accept=".pdf" style="margin-bottom: 10px;" />
        <br>
        <button class="btn" onclick="testUpload()">Upload PDF</button>
        <button class="btn" onclick="downloadSample()" style="margin-left: 10px; background: #28a745;">Download Sample PDF</button>
        <div id="upload-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>4. Debug Information</h3>
        <div id="debug-info" class="info">
            <p><strong>Current URL:</strong> <span id="current-url"></span></p>
            <p><strong>CSRF Token:</strong> <span id="csrf-token-display"></span></p>
            <p><strong>Fetch Available:</strong> <span id="fetch-available"></span></p>
            <p><strong>Laravel Environment:</strong> {{ app()->environment() }}</p>
            <p><strong>Debug Mode:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</p>
        </div>
    </div>
    
    <div class="test-section">
        <h3>5. Detailed Logs</h3>
        <button class="btn" onclick="clearLogs()">Clear Logs</button>
        <div id="detailed-logs" class="log">Logs will appear here...\n</div>
    </div>

    <script>
        // Logging function
        function log(message) {
            const timestamp = new Date().toISOString().split('T')[1].split('.')[0];
            const logDiv = document.getElementById('detailed-logs');
            logDiv.textContent += `[${timestamp}] ${message}\n`;
            logDiv.scrollTop = logDiv.scrollHeight;
            console.log(message);
        }
        
        function clearLogs() {
            document.getElementById('detailed-logs').textContent = 'Logs cleared...\n';
        }
        
        // Initialize debug info
        document.getElementById('current-url').textContent = window.location.href;
        document.getElementById('fetch-available').textContent = typeof fetch !== 'undefined' ? 'Yes' : 'No';
        
        // Get CSRF token
        function getCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            document.getElementById('csrf-token-display').textContent = token ? token.substring(0, 20) + '...' : 'Not found';
            return token;
        }
        
        // Initialize
        const csrfToken = getCSRFToken();
        log('Page loaded, CSRF token: ' + (csrfToken ? 'Found' : 'Missing'));
        
        async function testCSRF() {
            const resultDiv = document.getElementById('csrf-result');
            log('Testing CSRF token...');
            
            try {
                const token = getCSRFToken();
                if (!token) {
                    throw new Error('CSRF token not available in meta tag');
                }
                
                // Test CSRF token by making a request to our endpoint
                const response = await fetch('/csrf-token');
                const data = await response.json();
                
                if (data.token && token === data.token) {
                    resultDiv.innerHTML = '<div class="success">✅ CSRF Token is valid and working</div>';
                    log('CSRF token test: SUCCESS');
                } else {
                    resultDiv.innerHTML = '<div class="error">❌ CSRF token mismatch</div>';
                    log('CSRF token test: MISMATCH - meta: ' + token.substring(0,10) + '... vs endpoint: ' + data.token?.substring(0,10) + '...');
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="error">❌ CSRF Error: ' + error.message + '</div>';
                log('CSRF token test: ERROR - ' + error.message);
            }
        }
        
        async function testAPI() {
            const resultDiv = document.getElementById('api-result');
            log('Testing API connectivity...');
            
            try {
                const response = await fetch('/pdf-editor/sessions');
                const data = await response.json();
                
                if (response.ok) {
                    resultDiv.innerHTML = '<div class="success">✅ API Working: Found ' + data.sessions.length + ' sessions</div>';
                    log('API test: SUCCESS - ' + JSON.stringify(data));
                } else {
                    throw new Error('API returned ' + response.status);
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="error">❌ API Error: ' + error.message + '</div>';
                log('API test: ERROR - ' + error.message);
            }
        }
        
        async function testUpload() {
            const resultDiv = document.getElementById('upload-result');
            const fileInput = document.getElementById('pdf-file');
            
            if (!fileInput.files[0]) {
                resultDiv.innerHTML = '<div class="error">❌ Please select a PDF file first</div>';
                log('Upload test: No file selected');
                return;
            }
            
            const file = fileInput.files[0];
            resultDiv.innerHTML = 'Uploading ' + file.name + '...';
            log('Starting upload for file: ' + file.name + ' (Size: ' + file.size + ' bytes, Type: ' + file.type + ')');
            
            try {
                const token = getCSRFToken();
                if (!token) {
                    throw new Error('No CSRF token available');
                }
                
                const formData = new FormData();
                formData.append('pdf_file', file);
                
                log('Making request to /pdf-editor/load with CSRF token: ' + token.substring(0, 10) + '...');
                
                const response = await fetch('/pdf-editor/load', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                log('Response received - Status: ' + response.status + ' ' + response.statusText);
                
                const responseText = await response.text();
                log('Raw response: ' + responseText.substring(0, 500) + (responseText.length > 500 ? '...' : ''));
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    throw new Error('Invalid JSON response: ' + responseText.substring(0, 200));
                }
                
                if (response.ok && result.success) {
                    resultDiv.innerHTML = '<div class="success">✅ Upload successful!<br>' + 
                        'Session ID: ' + result.session_id + '<br>' +
                        'Original Name: ' + result.original_name + '<br>' +
                        'Page Count: ' + result.page_count + '<br>' +
                        'Form Fields: ' + (result.editable_regions?.form_fields?.length || 0) + '<br>' +
                        'Backup Created: ' + (result.backup_created ? 'Yes' : 'No') + '</div>';
                    log('Upload SUCCESS: ' + JSON.stringify(result));
                } else {
                    throw new Error(result.error || 'Upload failed with status ' + response.status);
                }
                
            } catch (error) {
                log('Upload ERROR: ' + error.message);
                resultDiv.innerHTML = '<div class="error">❌ Upload Error: ' + error.message + '</div>';
            }
        }
        
        function downloadSample() {
            log('Downloading sample PDF...');
            window.open('/sample.pdf', '_blank');
        }
        
        // Auto-run some tests on page load
        setTimeout(() => {
            log('Running auto-tests...');
            testCSRF();
        }, 1000);
    </script>
</body>
</html>