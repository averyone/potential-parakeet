@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-2">PDF Form Processing Tools</h2>
                <p class="text-gray-600">Upload, analyze, fill, and manipulate PDF forms with ease.</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- PDF Form Analysis -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        📋 Form Field Analysis
                    </h3>
                    <p class="text-gray-600 mb-6">Upload a PDF to discover its form fields and structure.</p>
                    
                    <form id="analyze-form" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select PDF File
                            </label>
                            <input type="file" name="pdf_file" accept=".pdf" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="analyze-btn-text">Analyze Form Fields</span>
                            <span class="analyze-loading hidden">Analyzing...</span>
                        </button>
                    </form>

                    <!-- Results Container -->
                    <div id="field-results" class="hidden mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">Discovered Form Fields:</h4>
                        <div id="field-list" class="space-y-2"></div>
                        <button id="fill-form-btn" 
                                class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Fill This Form
                        </button>
                    </div>
                </div>
            </div>

            <!-- PDF Form Filling -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        ✍️ Form Filling
                    </h3>
                    <p class="text-gray-600 mb-6">Fill PDF forms with custom data and download the result.</p>
                    
                    <div id="form-filling-section" class="hidden">
                        <form id="fill-form" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="hidden" name="original_pdf" id="original_pdf_data">
                            
                            <div id="dynamic-fields" class="space-y-4">
                                <!-- Dynamic form fields will be inserted here -->
                            </div>
                            
                            <button type="submit" 
                                    class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="fill-btn-text">Fill & Download PDF</span>
                                <span class="fill-loading hidden">Processing...</span>
                            </button>
                        </form>
                    </div>

                    <div id="form-filling-placeholder">
                        <p class="text-gray-500 italic">First analyze a PDF to enable form filling.</p>
                    </div>
                </div>
            </div>

            <!-- PDF Merging -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        🔗 PDF Merging
                    </h3>
                    <p class="text-gray-600 mb-6">Combine multiple PDF files into a single document.</p>
                    
                    <form id="merge-form" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select PDF Files (2 or more)
                            </label>
                            <input type="file" name="pdf_files[]" accept=".pdf" multiple required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple files</p>
                        </div>
                        <button type="submit" 
                                class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="merge-btn-text">Merge PDFs</span>
                            <span class="merge-loading hidden">Merging...</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- PDF Splitting -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        ✂️ PDF Splitting
                    </h3>
                    <p class="text-gray-600 mb-6">Split a PDF into individual pages.</p>
                    
                    <form id="split-form" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select PDF File
                            </label>
                            <input type="file" name="pdf_file" accept=".pdf" required
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                        </div>
                        <button type="submit" 
                                class="w-full bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="split-btn-text">Split PDF</span>
                            <span class="split-loading hidden">Splitting...</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- PDF Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        ℹ️ PDF Information
                    </h3>
                    <p class="text-gray-600 mb-6">Get detailed metadata and information about a PDF file.</p>
                    
                    <form id="info-form" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Select PDF File
                                </label>
                                <input type="file" name="pdf_file" accept=".pdf" required
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" 
                                        class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span class="info-btn-text">Get Info</span>
                                    <span class="info-loading hidden">Loading...</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- PDF Info Results -->
                    <div id="pdf-info-results" class="hidden mt-6">
                        <h4 class="font-semibold text-gray-900 mb-3">PDF Information:</h4>
                        <div id="pdf-info-content" class="bg-gray-50 p-4 rounded-lg">
                            <!-- PDF info will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript for handling forms and AJAX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let analyzedPdfData = null;

    // Helper function to show loading state
    function setLoadingState(form, isLoading) {
        const button = form.querySelector('button[type="submit"]');
        if (!button) return;
        
        // Try multiple selectors to find the text and loading spans
        const textSpan = button.querySelector('.analyze-btn-text, .fill-btn-text, .merge-btn-text, .split-btn-text, .info-btn-text, [class$="-btn-text"]');
        const loadingSpan = button.querySelector('.analyze-loading, .fill-loading, .merge-loading, .split-loading, .info-loading, [class$="-loading"]');
        
        
        if (isLoading) {
            button.disabled = true;
            if (textSpan) {
                textSpan.classList.add('hidden');
            }
            if (loadingSpan) {
                loadingSpan.classList.remove('hidden');
            }
        } else {
            button.disabled = false;
            if (textSpan) {
                textSpan.classList.remove('hidden');
            }
            if (loadingSpan) {
                loadingSpan.classList.add('hidden');
            }
        }
    }

    // Helper function to download blob
    function downloadBlob(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    }

    // Analyze Form Fields
    document.getElementById('analyze-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        setLoadingState(form, true);
        
        try {
            const response = await fetch('/api/pdf/form-fields', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                displayFormFields(data.fields);
                // Store the uploaded file data for form filling
                analyzedPdfData = formData.get('pdf_file');
            } else {
                alert('Error: ' + (data.error || 'Failed to analyze PDF'));
            }
        } catch (error) {
            console.error('Analysis error:', error);
            alert('Error: ' + error.message);
        } finally {
            setLoadingState(form, false);
        }
    });

    // Display form fields
    function displayFormFields(fields) {
        const resultsDiv = document.getElementById('field-results');
        const fieldList = document.getElementById('field-list');
        
        fieldList.innerHTML = '';
        
        if (fields && fields.length > 0) {
            fields.forEach(field => {
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'p-3 bg-gray-50 rounded border';
                fieldDiv.innerHTML = `
                    <div class="font-medium text-gray-900">${field.name || field.FieldName || field.fieldName || 'Unknown Field'}</div>
                    <div class="text-sm text-gray-600">Type: ${field.type || field.FieldType || field.fieldType || 'Unknown'}</div>
                    ${field.value || field.FieldValue || field.fieldValue ? `<div class="text-sm text-gray-600">Current: ${field.value || field.FieldValue || field.fieldValue}</div>` : ''}
                `;
                fieldList.appendChild(fieldDiv);
            });
            resultsDiv.classList.remove('hidden');
            
            // Enable form filling
            setupFormFilling(fields);
        } else {
            fieldList.innerHTML = '<div class="text-gray-500 italic">No form fields found in this PDF.</div>';
            resultsDiv.classList.remove('hidden');
        }
    }

    // Setup form filling interface
    function setupFormFilling(fields) {
        const dynamicFields = document.getElementById('dynamic-fields');
        const formFillingSection = document.getElementById('form-filling-section');
        const placeholder = document.getElementById('form-filling-placeholder');
        
        dynamicFields.innerHTML = '';
        
        fields.forEach(field => {
            const fieldName = field.name || field.FieldName || field.fieldName || 'unknown';
            const fieldType = field.type || field.FieldType || field.fieldType || 'text';
            const fieldValue = field.value || field.FieldValue || field.fieldValue || '';
            
            const fieldDiv = document.createElement('div');
            fieldDiv.innerHTML = `
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    ${fieldName}
                </label>
                <input type="text" name="form_data[${fieldName}]" value="${fieldValue}" 
                       placeholder="Enter value for ${fieldName}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            `;
            dynamicFields.appendChild(fieldDiv);
        });
        
        formFillingSection.classList.remove('hidden');
        placeholder.classList.add('hidden');
    }

    // Fill Form Button
    document.getElementById('fill-form-btn').addEventListener('click', function() {
        document.getElementById('form-filling-section').scrollIntoView({ behavior: 'smooth' });
    });

    // Fill Form Submission
    document.getElementById('fill-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!analyzedPdfData) {
            alert('Please analyze a PDF first');
            return;
        }
        
        const form = this;
        const formData = new FormData(form);
        formData.append('pdf_file', analyzedPdfData);
        setLoadingState(form, true);
        
        try {
            const response = await fetch('/api/pdf/fill-form', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const blob = await response.blob();
                downloadBlob(blob, 'filled-form.pdf');
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert('Error: ' + (errorData.error || `Request failed with status ${response.status}`));
            }
        } catch (error) {
            console.error('Form filling error:', error);
            alert('Error: ' + error.message);
        } finally {
            setLoadingState(form, false);
        }
    });

    // Merge PDFs
    document.getElementById('merge-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        const files = formData.getAll('pdf_files[]');
        
        if (files.length < 2) {
            alert('Please select at least 2 PDF files to merge');
            return;
        }
        
        setLoadingState(form, true);
        
        try {
            const response = await fetch('/api/pdf/merge', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const blob = await response.blob();
                downloadBlob(blob, 'merged.pdf');
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert('Error: ' + (errorData.error || 'Failed to merge PDFs'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            setLoadingState(form, false);
        }
    });

    // Split PDF
    document.getElementById('split-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        setLoadingState(form, true);
        
        try {
            const response = await fetch('/api/pdf/split', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                const blob = await response.blob();
                downloadBlob(blob, 'split-pages.zip');
            } else {
                const errorData = await response.json().catch(() => ({}));
                alert('Error: ' + (errorData.error || 'Failed to split PDF'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            setLoadingState(form, false);
        }
    });

    // Get PDF Info
    document.getElementById('info-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        setLoadingState(form, true);
        
        try {
            const response = await fetch('/api/pdf/info', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                displayPdfInfo(data.info);
            } else {
                alert('Error: ' + (data.error || 'Failed to get PDF info'));
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            setLoadingState(form, false);
        }
    });

    // Display PDF information
    function displayPdfInfo(info) {
        const resultsDiv = document.getElementById('pdf-info-results');
        const contentDiv = document.getElementById('pdf-info-content');
        
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        
        for (const [key, value] of Object.entries(info)) {
            html += `
                <div>
                    <div class="font-medium text-gray-900">${key}</div>
                    <div class="text-gray-600">${value || 'N/A'}</div>
                </div>
            `;
        }
        
        html += '</div>';
        contentDiv.innerHTML = html;
        resultsDiv.classList.remove('hidden');
    }
});
</script>
@endsection