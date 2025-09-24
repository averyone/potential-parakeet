<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vue.js Debug Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <h1>Vue.js Debug Test</h1>
    
    <div id="vue-test" style="padding: 20px; border: 2px solid #ccc; margin: 20px;">
        <h2>Vue Test Component</h2>
        <p>Message: <span v-text="message"></span></p>
        <button @click="updateMessage">Click me</button>
        <p>Counter: <span v-text="counter"></span></p>
    </div>

    <div id="pdf-editor-app" style="padding: 20px; border: 2px solid #333; margin: 20px;">
        <h2>PDF Editor App</h2>
        <p>Loading: <span v-text="loading"></span></p>
        <p>Session ID: <span v-text="sessionId || 'None'"></span></p>
        <button class="btn btn-primary" @click="testFunction">Test PDF Editor</button>
    </div>
    
    <script>
        // Simple Vue test without components
        if (window.Vue) {
            const { createApp } = window.Vue;
            
            createApp({
                data() {
                    return {
                        message: 'Vue is working!',
                        counter: 0
                    }
                },
                methods: {
                    updateMessage() {
                        this.counter++;
                        this.message = `Vue updated ${this.counter} times!`;
                    }
                }
            }).mount('#vue-test');
        } else {
            document.getElementById('vue-test').innerHTML = '<p style="color: red;">Vue.js not loaded</p>';
        }
    </script>
</body>
</html>