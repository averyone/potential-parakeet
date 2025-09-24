<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor - WYSIWYG</title>
    
    <!-- Load built assets directly -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-TssGocB7.css') }}">
    <link rel="stylesheet" href="{{ asset('build/assets/app-DN22hwpE.css') }}">
    <script type="module" src="{{ asset('build/assets/app-CrbzOSUa.js') }}"></script>
    
    <!-- PDF.js is included in the built assets (version 5.4.149) -->
    
    <style>
        /* Basic editor styles */
        .pdf-editor-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .pdf-editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        
        .pdf-editor-content {
            flex: 1;
            display: flex;
            overflow: hidden;
        }
        
        .pdf-viewer-pane {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            background: #e9ecef;
        }
        
        .pdf-controls {
            padding: 0.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-shrink: 0;
        }
        
        .pdf-canvas-container {
            flex: 1;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 2rem;
            position: relative;
        }
        
        .pdf-page {
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: white;
            margin-bottom: 2rem;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .pdf-page:hover {
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
        }
        
        .properties-panel {
            width: 300px;
            background: #f8f9fa;
            border-left: 1px solid #dee2e6;
            padding: 1rem;
            overflow-y: auto;
            flex-shrink: 0;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ccc;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background: #f8f9fa;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }
        
        .btn:disabled {
            background: #e9ecef;
            color: #6c757d;
            border-color: #dee2e6;
            cursor: not-allowed;
        }
        
        .btn:disabled:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: bold;
        }
        
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #007bff;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .toolbar-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
        }
        
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            max-width: 300px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast.success {
            background: #28a745;
        }
        
        .toast.error {
            background: #dc3545;
        }
        
        .toast.info {
            background: #17a2b8;
        }
    </style>
</head>
<body>
    <div id="pdf-editor-standalone" class="pdf-editor-container">
        <div class="pdf-editor-toolbar">
            <div class="toolbar-section">
                <button class="btn btn-primary" onclick="document.getElementById('file-upload').click()">
                    Load PDF
                </button>
                <button class="btn" onclick="window.pdfEditor && window.pdfEditor.savePdf()" id="save-btn" disabled>
                    Save
                </button>
                <button class="btn" onclick="window.pdfEditor && window.pdfEditor.exportPdf()" id="export-btn" disabled>
                    Export
                </button>
            </div>
            
            <div class="toolbar-section" id="pdf-info" style="display: none;">
                <span id="pdf-name"></span>
                <span class="text-muted" id="page-info"></span>
            </div>
            
            <div class="toolbar-section">
                <button class="btn" onclick="window.pdfEditor && window.pdfEditor.zoomOut()">-</button>
                <span id="zoom-level">100%</span>
                <button class="btn" onclick="window.pdfEditor && window.pdfEditor.zoomIn()">+</button>
            </div>
        </div>
        
        <div class="pdf-editor-content">
            <div class="pdf-viewer-pane">
                <div class="pdf-controls" id="page-controls" style="display: none;">
                    <button class="btn" onclick="window.pdfEditor && window.pdfEditor.previousPage()" id="prev-btn" disabled>
                        Previous
                    </button>
                    <span id="page-display">Page 1 of 1</span>
                    <button class="btn" onclick="window.pdfEditor && window.pdfEditor.nextPage()" id="next-btn" disabled>
                        Next
                    </button>
                </div>
                
                <div class="pdf-canvas-container">
                    <div id="loading-indicator" class="loading-overlay" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                    
                    <div id="welcome-screen" class="text-center">
                        <h3>PDF WYSIWYG Editor</h3>
                        <p>Load a PDF to start editing</p>
                        <button class="btn btn-primary" onclick="document.getElementById('file-upload').click()">
                            Choose PDF File
                        </button>
                        
                        <div style="margin-top: 2rem;">
                            <h4>System Status</h4>
                            <p id="js-status">JavaScript: Loading...</p>
                            <p id="vue-status">Mode: Checking...</p>
                            <p id="api-status">API: Not tested</p>
                            <button class="btn" onclick="testAPI()">Test API</button>
                            <p id="pdf-test-status">PDF Display: Not tested</p>
                            <button class="btn" onclick="testPdfDisplay()">Test PDF Display</button>
                            <hr style="margin: 1rem 0;">
                            <p><strong>PDF.js:</strong> <span id="pdfjs-status">Checking...</span></p>
                        </div>
                    </div>
                    
                    <div id="pdf-container" style="display: none;">
                        <div id="pdf-canvas-wrapper" style="text-align: center;">
                            <!-- PDF pages will be rendered as canvases here -->
                        </div>
                        <div id="pdf-loading" style="display: none; text-align: center; padding: 2rem;">
                            <div class="spinner"></div>
                            <p>Loading PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="properties-panel" id="properties" style="display: none;">
                <h4>Properties</h4>
                <div id="field-properties">
                    <!-- Field properties will be displayed here -->
                </div>
            </div>
        </div>
        
        <input type="file" id="file-upload" accept=".pdf" style="display: none;">
    </div>

    
    <script>
        console.log('PDF Editor - Fixed version loading...');
        
        document.getElementById('js-status').textContent = 'JavaScript: Loaded!';
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            // Hide toast
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (document.body.contains(toast)) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, duration);
        }
        
        // Test API function
        async function testAPI() {
            const statusEl = document.getElementById('api-status');
            statusEl.textContent = 'API: Testing...';
            
            try {
                const response = await fetch('/pdf-editor/sessions');
                const data = await response.json();
                statusEl.textContent = 'API: Working! Sessions: ' + data.sessions.length;
            } catch (error) {
                statusEl.textContent = 'API: Error - ' + error.message;
            }
        }
        
        // Test PDF Display function
        async function testPdfDisplay() {
            const statusEl = document.getElementById('pdf-test-status');
            statusEl.textContent = 'PDF Display: Testing...';
            
            try {
                // First upload a PDF
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Create a test PDF blob (simple test)
                const response = await fetch('/sample.pdf');
                if (!response.ok) {
                    statusEl.textContent = 'PDF Display: Error - Sample PDF not found';
                    return;
                }
                
                const pdfBlob = await response.blob();
                const formData = new FormData();
                formData.append('pdf_file', pdfBlob, 'test.pdf');
                
                // Upload PDF
                const uploadResponse = await fetch('/pdf-editor/load', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin'
                });
                
                const result = await uploadResponse.json();
                
                if (result.success) {
                    // Test PDF data endpoint
                    const pdfUrl = `/pdf-editor/data?session_id=${result.session_id}`;
                    const dataResponse = await fetch(pdfUrl, {
                        credentials: 'same-origin'
                    });
                    
                    if (dataResponse.ok) {
                        statusEl.textContent = 'PDF Display: Working! Data endpoint accessible.';
                        console.log('PDF test successful - URL:', pdfUrl);
                    } else {
                        statusEl.textContent = `PDF Display: Data endpoint error - ${dataResponse.status}`;
                    }
                } else {
                    statusEl.textContent = 'PDF Display: Upload failed - ' + (result.error || 'Unknown error');
                }
                
            } catch (error) {
                console.error('PDF display test error:', error);
                statusEl.textContent = 'PDF Display: Error - ' + error.message;
            }
        }
        
        // Test CSRF Token
        function testCSRFToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            return token;
        }
        
        // Initialize standalone PDF editor (no Vue dependency)
        function initializeStandalonePdfEditor() {
            console.log('Initializing Standalone PDF Editor (PDF.js only)');
            document.getElementById('vue-status').textContent = 'Mode: Standalone (PDF.js only)';
            
            // Check PDF.js availability (might be in module or global scope)
            const checkPdfJsAvailability = () => {
                // Check various ways PDF.js might be available
                if (typeof pdfjsLib !== 'undefined' || 
                    (window.pdfjsLib) || 
                    (window.pdfjs) ||
                    (typeof window.getDocument === 'function')) {
                    
                    document.getElementById('pdfjs-status').textContent = 'Available ✅';
                    document.getElementById('pdfjs-status').style.color = 'green';
                    console.log('✅ PDF.js library detected');
                    return true;
                } else {
                    document.getElementById('pdfjs-status').textContent = 'Checking... ⏳';
                    document.getElementById('pdfjs-status').style.color = 'orange';
                    return false;
                }
            };
            
            // Try checking multiple times since modules load asynchronously
            let attempts = 0;
            const maxAttempts = 10;
            const checkInterval = setInterval(() => {
                attempts++;
                if (checkPdfJsAvailability() || attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    if (attempts >= maxAttempts) {
                        document.getElementById('pdfjs-status').textContent = 'Not loaded ❌';
                        document.getElementById('pdfjs-status').style.color = 'red';
                        console.error('❌ PDF.js library failed to load after', attempts, 'attempts');
                    }
                }
            }, 200);
            
            console.log('✅ Standalone PDF editor ready');
        }
        
        // PDF.js-based editor functions
        window.pdfEditor = {
            zoomLevel: 100,
            
            async zoomIn() {
                if (!window.currentPdf) return;
                
                const newZoom = Math.min(window.currentPdf.zoom + 0.25, 3.0);
                await this.setZoom(newZoom);
            },
            
            async zoomOut() {
                if (!window.currentPdf) return;
                
                const newZoom = Math.max(window.currentPdf.zoom - 0.25, 0.5);
                await this.setZoom(newZoom);
            },
            
            async setZoom(zoom) {
                if (!window.currentPdf) return;
                
                window.currentPdf.zoom = zoom;
                this.zoomLevel = Math.round(zoom * 100);
                document.getElementById('zoom-level').textContent = this.zoomLevel + '%';
                
                console.log(`🔍 Zoom changed to ${this.zoomLevel}%`);
                
                // Re-render all pages with new zoom
                const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                const currentPage = window.currentPdf.currentPage;
                
                canvasWrapper.innerHTML = '';
                
                // Re-render all pages
                for (let pageNum = 1; pageNum <= window.currentPdf.totalPages; pageNum++) {
                    await renderPdfPage(window.currentPdf.pdf, pageNum);
                }
                
                // Show the current page again
                showPage(currentPage);
            },
            
            savePdf() {
                if (!window.currentPdf) {
                    showToast('No PDF loaded', 'error');
                    return;
                }
                showToast('Save functionality - Integration with form editing needed', 'info');
            },
            
            async exportPdf() {
                if (!window.currentPdf) {
                    alert('No PDF loaded');
                    return;
                }
                
                try {
                    const sessionId = window.currentPdf.sessionId;
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch('/pdf-editor/export', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            session_id: sessionId
                        }),
                        credentials: 'same-origin'
                    });
                    
                    if (response.ok) {
                        // If response is a file, create a download
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'exported-pdf.pdf';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showToast('PDF exported successfully!', 'success');
                    } else {
                        const error = await response.text();
                        showToast('Export failed: ' + error, 'error');
                    }
                } catch (error) {
                    console.error('Export error:', error);
                    showToast('Export failed: ' + error.message, 'error');
                }
            },
            
            previousPage() {
                if (!window.currentPdf || window.currentPdf.currentPage <= 1) return;
                
                const newPage = window.currentPdf.currentPage - 1;
                showPage(newPage);
                console.log(`⬅️ Previous page: ${newPage}`);
            },
            
            nextPage() {
                if (!window.currentPdf || window.currentPdf.currentPage >= window.currentPdf.totalPages) return;
                
                const newPage = window.currentPdf.currentPage + 1;
                showPage(newPage);
                console.log(`➡️ Next page: ${newPage}`);
            },
            
            renderCurrentPage() {
                // No longer needed since all pages are pre-rendered
                // Just show the current page
                if (window.currentPdf) {
                    showPage(window.currentPdf.currentPage);
                }
            },
            
            updatePageDisplay() {
                if (!window.currentPdf) return;
                
                const { currentPage, totalPages } = window.currentPdf;
                document.getElementById('page-info').textContent = `${currentPage} / ${totalPages}`;
                document.getElementById('page-display').textContent = `Page ${currentPage} of ${totalPages}`;
                
                // Update button states
                document.getElementById('prev-btn').disabled = currentPage <= 1;
                document.getElementById('next-btn').disabled = currentPage >= totalPages;
            }
        };
        
        // Function to display PDF using PDF.js
        async function displayPdf(sessionId) {
            try {
                console.log('🔄 Starting PDF display for session:', sessionId);
                
                // Check if PDF.js is available
                if (typeof pdfjsLib === 'undefined') {
                    throw new Error('PDF.js library not loaded from built assets');
                }
                
                // Set up PDF.js worker - use CDN worker version 5.4.149 to match API
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.4.149/pdf.worker.min.mjs';
                console.log('📚 PDF.js worker configured');
                
                // Get the PDF container elements
                const pdfContainer = document.getElementById('pdf-container');
                const welcomeScreen = document.getElementById('welcome-screen');
                const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                const loadingDiv = document.getElementById('pdf-loading');
                
                // Create the PDF URL
                const pdfUrl = `/pdf-editor/data?session_id=${encodeURIComponent(sessionId)}`;
                console.log('🌐 PDF URL:', pdfUrl);
                
                // Show loading state
                pdfContainer.style.display = 'block';
                welcomeScreen.style.display = 'none';
                loadingDiv.style.display = 'block';
                canvasWrapper.style.display = 'none';
                
                // Test URL accessibility first
                console.log('🧪 Testing PDF URL accessibility...');
                const testResponse = await fetch(pdfUrl, {
                    method: 'HEAD',
                    credentials: 'same-origin'
                });
                
                console.log('📋 URL Test Response:', {
                    status: testResponse.status,
                    statusText: testResponse.statusText,
                    headers: Object.fromEntries(testResponse.headers.entries()),
                    url: testResponse.url
                });
                
                if (!testResponse.ok) {
                    throw new Error(`PDF URL returned ${testResponse.status}: ${testResponse.statusText}`);
                }
                
                console.log('✅ PDF URL accessible, loading with PDF.js...');
                
                // Load the PDF document
                const loadingTask = pdfjsLib.getDocument({
                    url: pdfUrl,
                    withCredentials: true, // Include cookies for authentication
                    // Add debug info
                    verbosity: pdfjsLib.VerbosityLevel.ERRORS,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/5.4.149/cmaps/',
                    cMapPacked: true
                });
                
                console.log('⏳ PDF.js loading task created...');
                
                loadingTask.onProgress = function(progress) {
                    console.log('📈 Loading progress:', Math.round(progress.loaded / progress.total * 100) + '%');
                };
                
                const pdf = await loadingTask.promise;
                console.log('🎉 PDF loaded successfully!', {
                    numPages: pdf.numPages,
                    fingerprint: pdf.fingerprint,
                    pdfInfo: pdf._pdfInfo
                });
                
                // Clear loading state
                loadingDiv.style.display = 'none';
                canvasWrapper.style.display = 'block';
                canvasWrapper.innerHTML = '';
                
                // Store PDF reference globally for editor functions
                window.currentPdf = {
                    pdf: pdf,
                    sessionId: sessionId,
                    currentPage: 1,
                    totalPages: pdf.numPages,
                    zoom: 1.0
                };
                
                console.log('🖼️ Rendering all pages...');
                
                // Clear any existing pages
                canvasWrapper.innerHTML = '';
                
                // Render all pages (but only show the first one)
                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    await renderPdfPage(pdf, pageNum);
                }
                
                // Update UI with PDF info
                document.getElementById('pdf-name').textContent = 'PDF Document';
                document.getElementById('page-info').textContent = `1 / ${pdf.numPages}`;
                document.getElementById('pdf-info').style.display = 'flex';
                document.getElementById('save-btn').disabled = false;
                document.getElementById('export-btn').disabled = false;
                
                if (pdf.numPages > 1) {
                    document.getElementById('page-controls').style.display = 'flex';
                    document.getElementById('page-display').textContent = `Page 1 of ${pdf.numPages}`;
                    
                    // Enable/disable navigation buttons
                    window.pdfEditor.updatePageDisplay();
                }
                
                // Show only the first page initially
                showPage(1);
                
                console.log('✅ PDF rendered successfully with PDF.js');
                showToast('PDF loaded and rendered successfully!', 'success');
                
            } catch (error) {
                console.error('❌ Error displaying PDF:', error);
                console.error('❌ Error stack:', error.stack);
                
                // Show error state
                const pdfContainer = document.getElementById('pdf-container');
                const loadingDiv = document.getElementById('pdf-loading');
                const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                const pdfUrl = `/pdf-editor/data?session_id=${encodeURIComponent(sessionId)}`;
                
                loadingDiv.style.display = 'none';
                canvasWrapper.innerHTML = `
                    <div style="text-align: center; padding: 3rem; border: 2px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24; margin: 2rem;">
                        <h4>⚠️ Could not render PDF with PDF.js</h4>
                        <p><strong>Error:</strong> ${error.message}</p>
                        <details style="margin: 1rem 0; text-align: left;">
                            <summary>Debug Information</summary>
                            <pre style="background: #fff; padding: 1rem; border-radius: 4px; overflow: auto;">${error.stack || 'No stack trace available'}</pre>
                        </details>
                        <p>Fallback options:</p>
                        <button onclick="debugPdfUrl('${sessionId}')" class="btn" style="margin: 0.25rem;">Debug URL</button>
                        <a href="${pdfUrl}" target="_blank" class="btn btn-primary" style="margin: 0.25rem;">Open in New Tab</a>
                        <br><br>
                        <div style="margin-top: 1rem;">
                            <h5>Alternative Viewer:</h5>
                            <iframe 
                                src="${pdfUrl}" 
                                width="100%" 
                                height="600px"
                                style="border: 1px solid #ccc;"
                                title="PDF Fallback Viewer"
                                onload="console.log('✅ Iframe loaded successfully')"
                                onerror="console.error('❌ Iframe failed to load')">
                                Your browser doesn't support iframes. <a href="${pdfUrl}" target="_blank">Click here to view the PDF</a>.
                            </iframe>
                        </div>
                    </div>
                `;
                
                pdfContainer.style.display = 'block';
                document.getElementById('welcome-screen').style.display = 'none';
                
                showToast('PDF.js failed to load PDF. Using fallback viewer.', 'error', 5000);
            }
        }
        
        // Function to render a specific PDF page
        async function renderPdfPage(pdf, pageNumber) {
            try {
                console.log(`🖼️ Rendering page ${pageNumber} of ${pdf.numPages}`);
                
                const page = await pdf.getPage(pageNumber);
                const viewport = page.getViewport({ scale: window.currentPdf?.zoom || 1.0 });
                
                console.log(`📜 Page info:`, {
                    pageNumber,
                    viewport: {
                        width: viewport.width,
                        height: viewport.height,
                        scale: viewport.scale
                    }
                });
                
                // Create canvas
                const canvas = document.createElement('canvas');
                const context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                
                console.log(`🎨 Canvas created:`, {
                    width: canvas.width,
                    height: canvas.height
                });
                
                // Style the canvas
                canvas.style.display = pageNumber === 1 ? 'block' : 'none'; // Only show first page initially
                canvas.style.margin = '0 auto 2rem auto';
                canvas.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                canvas.style.border = '1px solid #ddd';
                canvas.className = 'pdf-page-canvas';
                canvas.id = `pdf-page-${pageNumber}`;
                canvas.dataset.pageNumber = pageNumber;
                
                // Add to container first so we can see it
                const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                canvasWrapper.appendChild(canvas);
                
                console.log(`🔄 Starting page render...`);
                
                // Render the page
                const renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                
                const renderTask = page.render(renderContext);
                
                renderTask.onContinue = function (cont) {
                    console.log('📈 Render continuing...');
                    cont();
                };
                
                await renderTask.promise;
                
                console.log(`✅ Page ${pageNumber} rendered successfully to canvas`);
                
                // Verify the canvas has content
                const imageData = context.getImageData(0, 0, Math.min(canvas.width, 100), Math.min(canvas.height, 100));
                const hasContent = Array.from(imageData.data).some(pixel => pixel !== 255 && pixel !== 0);
                
                console.log(`🔍 Canvas content check:`, {
                    hasContent,
                    canvasSize: `${canvas.width}x${canvas.height}`,
                    visible: canvas.style.display !== 'none'
                });
                
                return canvas;
                
            } catch (error) {
                console.error(`❌ Error rendering page ${pageNumber}:`, error);
                console.error(`❌ Error details:`, {
                    name: error.name,
                    message: error.message,
                    stack: error.stack
                });
                
                // Show error in the canvas wrapper
                const canvasWrapper = document.getElementById('pdf-canvas-wrapper');
                const errorDiv = document.createElement('div');
                errorDiv.innerHTML = `
                    <div style="text-align: center; padding: 2rem; border: 2px solid #dc3545; border-radius: 8px; background: #f8d7da; color: #721c24; margin: 1rem;">
                        <h5>⚠️ Failed to render page ${pageNumber}</h5>
                        <p><strong>Error:</strong> ${error.message}</p>
                    </div>
                `;
                canvasWrapper.appendChild(errorDiv);
                
                throw error;
            }
        }
        
        // File upload handler
        document.getElementById('file-upload').addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('File selected:', file.name);
                document.getElementById('loading-indicator').style.display = 'flex';
                document.getElementById('welcome-screen').style.display = 'none';
                
                try {
                    // Get CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    // Create FormData for file upload
                    const formData = new FormData();
                    formData.append('pdf_file', file);
                    
                    // Upload PDF
                    const response = await fetch('/pdf-editor/load', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        console.log('PDF loaded successfully:', result);
                        // Show success and update UI
                        document.getElementById('loading-indicator').style.display = 'none';
                        document.getElementById('pdf-name').textContent = result.original_name;
                        document.getElementById('page-info').textContent = `1 / ${result.page_count}`;
                        document.getElementById('pdf-info').style.display = 'flex';
                        document.getElementById('save-btn').disabled = false;
                        document.getElementById('export-btn').disabled = false;
                        
                        if (result.page_count > 1) {
                            document.getElementById('page-controls').style.display = 'flex';
                            document.getElementById('page-display').textContent = `Page 1 of ${result.page_count}`;
                        }
                        
                        // Show properties panel if there are form fields
                        if (result.editable_regions.form_fields.length > 0) {
                            document.getElementById('properties').style.display = 'block';
                            const propertiesDiv = document.getElementById('field-properties');
                            propertiesDiv.innerHTML = '<h5>Form Fields Found</h5><p>' + result.editable_regions.form_fields.length + ' editable fields detected.</p>';
                        }
                        
                        // Store session ID for debugging
                        window.currentSessionId = result.session_id;
                        console.log('Stored session ID:', result.session_id);
                        
                        // Display the PDF
                        displayPdf(result.session_id);
                        
                        showToast(`PDF loaded successfully! Found ${result.editable_regions.form_fields.length} form fields.`, 'success');
                        
                    } else {
                        throw new Error(result.error || 'Failed to load PDF');
                    }
                    
                } catch (error) {
                    console.error('Error loading PDF:', error);
                    document.getElementById('loading-indicator').style.display = 'none';
                    document.getElementById('welcome-screen').style.display = 'block';
                    showToast('Failed to load PDF: ' + error.message, 'error');
                }
            }
        });
        
        // Initialize standalone editor
        setTimeout(initializeStandalonePdfEditor, 100);
        
        console.log('PDF Editor initialized');
        
        // Global debug function for browser console
        window.debugPdfUrl = function(sessionId) {
            const sid = sessionId || window.currentSessionId;
            if (!sid) {
                console.error('No session ID available. Upload a PDF first.');
                return;
            }
            
            const url = `/pdf-editor/data?session_id=${sid}`;
            console.log('Testing PDF URL:', url);
            
            fetch(url, { 
                credentials: 'same-origin',
                method: 'GET'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', Object.fromEntries(response.headers.entries()));
                if (response.ok) {
                    console.log('✅ PDF URL is accessible!');
                    window.open(url, '_blank');
                } else {
                    console.error('❌ PDF URL returned error:', response.status, response.statusText);
                    return response.text().then(text => {
                        console.log('Error response body:', text);
                    });
                }
            })
            .catch(error => {
                console.error('❌ Network error accessing PDF URL:', error);
            });
        };
        
        console.log('Debug function available: debugPdfUrl(sessionId)');
        console.log('After uploading a PDF, call debugPdfUrl() in console to test the URL.');
        
        // Function to show specific page
        function showPage(pageNumber) {
            if (!window.currentPdf) return;
            
            const totalPages = window.currentPdf.totalPages;
            if (pageNumber < 1 || pageNumber > totalPages) return;
            
            console.log(`📄 Showing page ${pageNumber} of ${totalPages}`);
            
            // Hide all pages
            const allPages = document.querySelectorAll('.pdf-page-canvas');
            allPages.forEach(canvas => {
                canvas.style.display = 'none';
            });
            
            // Show the requested page
            const pageCanvas = document.getElementById(`pdf-page-${pageNumber}`);
            if (pageCanvas) {
                pageCanvas.style.display = 'block';
                window.currentPdf.currentPage = pageNumber;
                
                // Update page display
                window.pdfEditor.updatePageDisplay();
            } else {
                console.error(`Page ${pageNumber} canvas not found`);
            }
        }
    </script>
</body>
</html>