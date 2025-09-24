import './bootstrap';
import { createApp } from 'vue';
import PdfEditor from './components/PdfEditor.vue';
import EditableOverlay from './components/EditableOverlay.vue';

// Initialize Vue.js app for PDF editor
const pdfEditorElement = document.getElementById('pdf-editor-app');
if (pdfEditorElement) {
    const app = createApp(PdfEditor);
    app.component('EditableOverlay', EditableOverlay);
    app.mount('#pdf-editor-app');
}

// PDF Application JavaScript Utilities (legacy support)
class PDFApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupFileValidation();
        this.setupProgressIndicators();
        this.setupNotifications();
    }

    // File validation for PDF uploads
    setupFileValidation() {
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file' && e.target.accept === '.pdf') {
                this.validatePdfFile(e.target);
            }
        });
    }

    validatePdfFile(input) {
        const files = Array.from(input.files);
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        files.forEach(file => {
            if (file.type !== 'application/pdf') {
                this.showNotification('Please select only PDF files.', 'error');
                input.value = '';
                return;
            }
            
            if (file.size > maxSize) {
                this.showNotification('File size must be less than 10MB.', 'error');
                input.value = '';
                return;
            }
        });
    }

    // Progress indicators for form submissions
    setupProgressIndicators() {
        document.addEventListener('submit', (e) => {
            if (e.target.matches('form[enctype="multipart/form-data"]')) {
                this.showProgress(e.target);
            }
        });
    }

    showProgress(form) {
        const button = form.querySelector('button[type="submit"]');
        if (button && !button.disabled) {
            const originalText = button.textContent;
            button.innerHTML = '<div class="loading-spinner mr-2"></div>Processing...';
            button.disabled = true;
            
            // Reset after 30 seconds as fallback
            setTimeout(() => {
                if (button.disabled) {
                    button.textContent = originalText;
                    button.disabled = false;
                }
            }, 30000);
        }
    }

    // Notification system
    setupNotifications() {
        // Auto-hide notifications after 5 seconds
        document.querySelectorAll('[x-data*="show: true"]').forEach(notification => {
            setTimeout(() => {
                if (notification.style.display !== 'none') {
                    notification.style.display = 'none';
                }
            }, 5000);
        });
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const bgColor = type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-blue-500';
        
        notification.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg alert-slide-in`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Utility method to format file sizes
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Utility method to truncate text
    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
}

// Initialize the PDF application when DOM is ready (for non-Vue pages)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('pdf-editor-app')) {
            new PDFApp();
        }
    });
} else {
    if (!document.getElementById('pdf-editor-app')) {
        new PDFApp();
    }
}

// Export for global access if needed
window.PDFApp = PDFApp;
