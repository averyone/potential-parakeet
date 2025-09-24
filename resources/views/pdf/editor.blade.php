<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor - WYSIWYG</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            justify-content: between;
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
        
        .editable-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
        }
        
        .editable-field {
            position: absolute;
            border: 2px dashed #007bff;
            background: rgba(0, 123, 255, 0.1);
            cursor: pointer;
            pointer-events: auto;
            transition: all 0.2s;
        }
        
        .editable-field:hover {
            border-color: #0056b3;
            background: rgba(0, 123, 255, 0.2);
        }
        
        .editable-field.selected {
            border-style: solid;
            border-width: 2px;
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }
        
        .field-input {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
            padding: 2px 4px;
            font-size: 12px;
            resize: none;
        }
        
        .field-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body>
    <div id="pdf-editor-app" class="pdf-editor-container">
        <div class="pdf-editor-toolbar">
            <div class="toolbar-section">
                <input type="file" id="pdf-upload" accept=".pdf" style="display: none;">
                <button class="btn btn-primary" @click="$refs.pdfUpload.click()">
                    Load PDF
                </button>
                <button class="btn" @click="savePdf" :disabled="!sessionId">
                    Save
                </button>
                <button class="btn" @click="exportPdf" :disabled="!sessionId">
                    Export
                </button>
            </div>
            
            <div class="toolbar-section" v-if="currentPdf">
                <span v-text="currentPdf.original_name"></span>
                <span class="text-muted" v-text="(currentPage + 1) + ' / ' + pageCount"></span>
            </div>
            
            <div class="toolbar-section">
                <button class="btn" @click="zoomOut">-</button>
                <span v-text="Math.round(zoomLevel) + '%'"></span>
                <button class="btn" @click="zoomIn">+</button>
            </div>
        </div>
        
        <div class="pdf-editor-content">
            <div class="pdf-viewer-pane">
                <div class="pdf-controls" v-if="pageCount > 1">
                    <button class="btn" @click="previousPage" :disabled="currentPage === 0">
                        Previous
                    </button>
                    <span v-text="'Page ' + (currentPage + 1) + ' of ' + pageCount"></span>
                    <button class="btn" @click="nextPage" :disabled="currentPage === pageCount - 1">
                        Next
                    </button>
                </div>
                
                <div class="pdf-canvas-container" ref="canvasContainer">
                    <div v-if="loading" class="loading-overlay">
                        <div class="spinner"></div>
                    </div>
                    
                    <div v-if="!currentPdf && !loading" class="text-center">
                        <h3>PDF WYSIWYG Editor</h3>
                        <p>Load a PDF to start editing</p>
                        <button class="btn btn-primary" @click="$refs.pdfUpload.click()">
                            Choose PDF File
                        </button>
                    </div>
                    
                    <div v-for="(page, index) in pages" :key="index" 
                         v-show="index === currentPage" 
                         class="pdf-page"
                         :style="{ transform: `scale(${zoomLevel / 100})` }">
                        
                        <canvas :ref="`canvas-${index}`" 
                                :data-page="index"></canvas>
                        
                        <editable-overlay 
                            :fields="editableFields" 
                            :page="index"
                            :zoom="zoomLevel / 100"
                            @field-selected="onFieldSelected"
                            @field-updated="onFieldUpdated">
                        </editable-overlay>
                    </div>
                </div>
            </div>
            
            <div class="properties-panel" v-if="currentPdf">
                <h4>Properties</h4>
                
                <div v-if="selectedField" class="form-group">
                    <h5>Selected Field</h5>
                    <div class="form-group">
                        <label class="form-label">Field Name</label>
                        <input type="text" class="form-control" 
                               :value="selectedField.name" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Field Type</label>
                        <input type="text" class="form-control" 
                               :value="selectedField.type" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Value</label>
                        <textarea class="form-control" 
                                  v-model="selectedField.value"
                                  @input="updateFieldValue"
                                  rows="3"></textarea>
                    </div>
                </div>
                
                <div v-else>
                    <div class="form-group">
                        <h5>Form Fields</h5>
                        <div v-for="field in editableFields" :key="field.id" 
                             class="field-item" style="padding: 0.5rem; border: 1px solid #ccc; margin-bottom: 0.5rem; cursor: pointer;"
                             @click="selectField(field)">
                            <strong v-text="field.name"></strong>
                            <br>
                            <small class="text-muted" v-text="field.type"></small>
                            <br>
                            <small v-text="field.value || 'Empty'"></small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <h5>Document Info</h5>
                    <p><strong>Pages:</strong> <span v-text="pageCount"></span></p>
                    <p><strong>Fields:</strong> <span v-text="editableFields.length"></span></p>
                </div>
            </div>
        </div>
        
        <input ref="pdfUpload" type="file" accept=".pdf" @change="loadPdf" style="display: none;">
    </div>

    <script>
        // This will be replaced by the Vue.js app
        // We'll create the Vue components in separate files
    </script>
</body>
</html>