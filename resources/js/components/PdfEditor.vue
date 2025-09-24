<template>
  <div>
    <div class="pdf-editor-toolbar">
      <div class="toolbar-section">
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
        <span>{{ currentPdf.original_name }}</span>
        <span class="text-muted">{{ currentPage + 1 }} / {{ pageCount }}</span>
      </div>
      
      <div class="toolbar-section">
        <button class="btn" @click="zoomOut">-</button>
        <span>{{ Math.round(zoomLevel) }}%</span>
        <button class="btn" @click="zoomIn">+</button>
      </div>
    </div>
    
    <div class="pdf-editor-content">
      <div class="pdf-viewer-pane">
        <div class="pdf-controls" v-if="pageCount > 1">
          <button class="btn" @click="previousPage" :disabled="currentPage === 0">
            Previous
          </button>
          <span>Page {{ currentPage + 1 }} of {{ pageCount }}</span>
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
            
            <EditableOverlay 
              :fields="editableFields" 
              :page="index"
              :zoom="zoomLevel / 100"
              @field-selected="onFieldSelected"
              @field-updated="onFieldUpdated">
            </EditableOverlay>
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
              <strong>{{ field.name }}</strong>
              <br>
              <small class="text-muted">{{ field.type }}</small>
              <br>
              <small>{{ field.value || 'Empty' }}</small>
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <h5>Document Info</h5>
          <p><strong>Pages:</strong> {{ pageCount }}</p>
          <p><strong>Fields:</strong> {{ editableFields.length }}</p>
        </div>
      </div>
    </div>
    
    <input ref="pdfUpload" type="file" accept=".pdf" @change="loadPdf" style="display: none;">
  </div>
</template>

<script>
import * as pdfjsLib from 'pdfjs-dist'

// Configure PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.mjs'

export default {
  name: 'PdfEditor',
  data() {
    return {
      // PDF document state
      currentPdf: null,
      pdfDocument: null,
      pages: [],
      currentPage: 0,
      pageCount: 0,
      zoomLevel: 100,
      
      // Editor state
      sessionId: null,
      editableFields: [],
      selectedField: null,
      loading: false,
      
      // Canvas rendering
      canvasContexts: []
    }
  },
  
  mounted() {
    // Set up CSRF token for API requests
    const token = document.head.querySelector('meta[name="csrf-token"]')
    if (token) {
      window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
    }
  },
  
  methods: {
    async loadPdf(event) {
      const file = event.target.files[0]
      if (!file) return
      
      this.loading = true
      
      try {
        // Upload PDF to server
        const formData = new FormData()
        formData.append('pdf_file', file)
        
        const response = await fetch('/pdf-editor/load', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        })
        
        const result = await response.json()
        
        if (!result.success) {
          throw new Error(result.error || 'Failed to load PDF')
        }
        
        // Store session information
        this.sessionId = result.session_id
        this.currentPdf = {
          original_name: result.original_name,
          pdf_path: result.pdf_path
        }
        this.pageCount = result.page_count
        this.editableFields = result.editable_regions.form_fields || []
        
        // Load and render PDF
        await this.renderPdf()
        
      } catch (error) {
        console.error('Error loading PDF:', error)
        alert('Failed to load PDF: ' + error.message)
      } finally {
        this.loading = false
      }
    },
    
    async renderPdf() {
      try {
        // Get PDF data from server
        const response = await fetch(`/pdf-editor/data?session_id=${this.sessionId}`)
        const pdfData = await response.arrayBuffer()
        
        // Load PDF with PDF.js
        const loadingTask = pdfjsLib.getDocument({ data: pdfData })
        this.pdfDocument = await loadingTask.promise
        
        // Initialize pages array
        this.pages = Array.from({ length: this.pageCount }, (_, i) => ({ pageNum: i + 1 }))
        
        // Wait for next tick to ensure canvas elements exist
        await this.$nextTick()
        
        // Render all pages
        for (let i = 0; i < this.pageCount; i++) {
          await this.renderPage(i)
        }
        
      } catch (error) {
        console.error('Error rendering PDF:', error)
      }
    },
    
    async renderPage(pageIndex) {
      try {
        const page = await this.pdfDocument.getPage(pageIndex + 1)
        const canvas = this.$refs[`canvas-${pageIndex}`]?.[0] || this.$refs[`canvas-${pageIndex}`]
        
        if (!canvas) {
          console.warn(`Canvas not found for page ${pageIndex}`)
          return
        }
        
        const context = canvas.getContext('2d')
        const viewport = page.getViewport({ scale: 1.5 })
        
        canvas.height = viewport.height
        canvas.width = viewport.width
        
        const renderContext = {
          canvasContext: context,
          viewport: viewport
        }
        
        await page.render(renderContext).promise
        
      } catch (error) {
        console.error(`Error rendering page ${pageIndex}:`, error)
      }
    },
    
    // Navigation methods
    nextPage() {
      if (this.currentPage < this.pageCount - 1) {
        this.currentPage++
      }
    },
    
    previousPage() {
      if (this.currentPage > 0) {
        this.currentPage--
      }
    },
    
    // Zoom methods
    zoomIn() {
      this.zoomLevel = Math.min(this.zoomLevel + 25, 300)
    },
    
    zoomOut() {
      this.zoomLevel = Math.max(this.zoomLevel - 25, 50)
    },
    
    // Field editing methods
    onFieldSelected(field) {
      this.selectedField = field
    },
    
    async onFieldUpdated(field, value) {
      try {
        const response = await fetch('/pdf-editor/update-field', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            session_id: this.sessionId,
            field_name: field.name,
            field_value: value
          })
        })
        
        const result = await response.json()
        
        if (!result.success) {
          throw new Error(result.error || 'Failed to update field')
        }
        
        // Update local field value
        field.value = value
        
      } catch (error) {
        console.error('Error updating field:', error)
        alert('Failed to update field: ' + error.message)
      }
    },
    
    selectField(field) {
      this.selectedField = field
    },
    
    async updateFieldValue() {
      if (this.selectedField) {
        await this.onFieldUpdated(this.selectedField, this.selectedField.value)
      }
    },
    
    // Save and export methods
    async savePdf() {
      if (!this.sessionId) return
      
      try {
        const response = await fetch('/pdf-editor/save', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            session_id: this.sessionId,
            edits: this.getEdits()
          })
        })
        
        const result = await response.json()
        
        if (!result.success) {
          throw new Error(result.error || 'Failed to save PDF')
        }
        
        alert('PDF saved successfully!')
        
      } catch (error) {
        console.error('Error saving PDF:', error)
        alert('Failed to save PDF: ' + error.message)
      }
    },
    
    async exportPdf() {
      if (!this.sessionId) return
      
      try {
        const response = await fetch('/pdf-editor/export', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            session_id: this.sessionId,
            filename: this.currentPdf.original_name.replace('.pdf', '_edited.pdf')
          })
        })
        
        if (response.ok) {
          const blob = await response.blob()
          const url = window.URL.createObjectURL(blob)
          const a = document.createElement('a')
          a.style.display = 'none'
          a.href = url
          a.download = this.currentPdf.original_name.replace('.pdf', '_edited.pdf')
          document.body.appendChild(a)
          a.click()
          window.URL.revokeObjectURL(url)
          document.body.removeChild(a)
        } else {
          const result = await response.json()
          throw new Error(result.error || 'Failed to export PDF')
        }
        
      } catch (error) {
        console.error('Error exporting PDF:', error)
        alert('Failed to export PDF: ' + error.message)
      }
    },
    
    getEdits() {
      // Collect all edits from the current session
      const edits = []
      
      for (const field of this.editableFields) {
        if (field.value) {
          edits.push({
            type: 'form_field',
            field_name: field.name,
            value: field.value,
            timestamp: new Date().toISOString()
          })
        }
      }
      
      return edits
    }
  }
}
</script>

<style scoped>
.text-center {
  text-align: center;
}

.text-muted {
  color: #6c757d;
}

.toolbar-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
</style>