<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Editor - Simple Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .pdf-editor-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        .toolbar {
            background: #f8f9fa;
            padding: 1rem;
            border: 1px solid #ccc;
            margin-bottom: 20px;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: 1px solid #ccc;
            background: white;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 10px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .status {
            margin-top: 20px;
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div id="pdf-editor-simple" class="pdf-editor-container">
        <div class="toolbar">
            <h2>PDF Editor - Simple Test</h2>
            <button class="btn btn-primary" @click="loadTest">Test Load</button>
            <button class="btn" @click="toggleLoading">Toggle Loading</button>
            <button class="btn" @click="testApi">Test API</button>
        </div>
        
        <div class="status">
            <p><strong>Vue Status:</strong> <span v-text="vueStatus"></span></p>
            <p><strong>Loading:</strong> <span v-text="loading"></span></p>
            <p><strong>Message:</strong> <span v-text="message"></span></p>
            <p><strong>API Status:</strong> <span v-text="apiStatus"></span></p>
        </div>
        
        <div v-if="loading" style="text-align: center; padding: 50px;">
            <div style="border: 4px solid #f3f3f3; border-radius: 50%; border-top: 4px solid #007bff; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            <p>Loading...</p>
        </div>
        
        <input ref="fileInput" type="file" accept=".pdf" @change="handleFileChange" style="display: none;">
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        console.log('Simple PDF Editor script loaded');
        
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM ready, checking for Vue...');
            
            if (typeof Vue === 'undefined') {
                console.error('Vue is not defined!');
                document.getElementById('pdf-editor-simple').innerHTML = '<div style="color: red; padding: 20px;"><h2>Vue.js Error</h2><p>Vue.js failed to load. Check the console for details.</p></div>';
                return;
            }
            
            console.log('Vue found, creating app...');
            
            try {
                const { createApp } = Vue;
                
                const app = createApp({
                    data() {
                        return {
                            vueStatus: 'Vue is working!',
                            loading: false,
                            message: 'Initialized successfully',
                            apiStatus: 'Not tested'
                        }
                    },
                    methods: {
                        loadTest() {
                            this.message = 'Load test clicked!';
                            this.$refs.fileInput.click();
                        },
                        toggleLoading() {
                            this.loading = !this.loading;
                            this.message = 'Loading toggled to: ' + this.loading;
                        },
                        async testApi() {
                            this.apiStatus = 'Testing...';
                            try {
                                const response = await fetch('/pdf-editor/sessions');
                                const data = await response.json();
                                this.apiStatus = 'API Working! Sessions: ' + data.sessions.length;
                                this.message = 'API test successful';
                            } catch (error) {
                                this.apiStatus = 'API Error: ' + error.message;
                                this.message = 'API test failed';
                            }
                        },
                        handleFileChange(event) {
                            const file = event.target.files[0];
                            if (file) {
                                this.message = 'File selected: ' + file.name;
                            }
                        }
                    },
                    mounted() {
                        console.log('Vue component mounted successfully');
                        this.message = 'Vue mounted and ready';
                    }
                });
                
                app.mount('#pdf-editor-simple');
                console.log('Vue app mounted successfully');
                
            } catch (error) {
                console.error('Error creating Vue app:', error);
                document.getElementById('pdf-editor-simple').innerHTML = '<div style="color: red; padding: 20px;"><h2>Vue.js Mounting Error</h2><p>' + error.message + '</p></div>';
            }
        });
    </script>
</body>
</html>