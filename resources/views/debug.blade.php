<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Debug PDF Form Analysis</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .result { background: #f5f5f5; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
        button { padding: 10px 20px; margin: 10px 0; }
        input[type="file"] { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Debug PDF Form Analysis</h1>
    
    <form id="debugForm">
        <div>
            <label>Select PDF file:</label><br>
            <input type="file" id="pdfFile" accept=".pdf" required>
        </div>
        <button type="submit">Test Analysis</button>
        <button type="button" id="refreshToken">Refresh CSRF Token</button>
    </form>
    
    <div class="result">
        <strong>Current CSRF Token:</strong> <span id="currentToken">{{ csrf_token() }}</span>
    </div>

    <div id="results"></div>

    <script>
        document.getElementById('debugForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const resultsDiv = document.getElementById('results');
            const fileInput = document.getElementById('pdfFile');
            
            if (!fileInput.files[0]) {
                resultsDiv.innerHTML = '<div class="result error">Please select a PDF file</div>';
                return;
            }

            resultsDiv.innerHTML = '<div class="result">Analyzing... Please wait</div>';

            try {
                const formData = new FormData();
                formData.append('pdf_file', fileInput.files[0]);

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                console.log('Making request to /pdf/form-fields');
                console.log('CSRF Token:', csrfToken);
                console.log('File name:', fileInput.files[0].name);
                console.log('File size:', fileInput.files[0].size);
                console.log('File type:', fileInput.files[0].type);

                const response = await fetch('/pdf/form-fields', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);

                if (response.status === 419) {
                    resultsDiv.innerHTML = '<div class="result error">CSRF token expired. Please refresh the page and try again.</div>';
                    return;
                }

                if (!response.ok) {
                    const errorText = await response.text();
                    console.log('Error response text:', errorText);
                    
                    // Try to parse as JSON to get validation errors
                    try {
                        const errorData = JSON.parse(errorText);
                        if (errorData.error) {
                            throw new Error(`Validation Error: ${JSON.stringify(errorData.error)}`);
                        }
                    } catch (parseError) {
                        // Not JSON, show raw error
                        console.log('Could not parse error as JSON:', parseError);
                    }
                    
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    let html = '<div class="result success"><h3>Analysis Successful!</h3>';
                    html += `<p>Found ${data.fields.length} form fields:</p><ul>`;
                    data.fields.forEach(field => {
                        html += `<li><strong>${field.name}</strong> (${field.type})`;
                        if (field.value) html += ` - Value: "${field.value}"`;
                        if (field.options && field.options.length > 0) {
                            html += ` - Options: ${field.options.join(', ')}`;
                        }
                        html += '</li>';
                    });
                    html += '</ul></div>';
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = `<div class="result error">Analysis failed: ${data.error || 'Unknown error'}</div>`;
                }
            } catch (error) {
                console.error('Analysis error:', error);
                resultsDiv.innerHTML = `<div class="result error">Error: ${error.message}</div>`;
            }
        });

        // CSRF token refresh functionality
        document.getElementById('refreshToken').addEventListener('click', async function() {
            try {
                const response = await fetch('/debug');
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newToken = doc.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                // Update the meta tag
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', newToken);
                
                // Update the display
                document.getElementById('currentToken').textContent = newToken;
                
                console.log('CSRF token refreshed:', newToken);
                document.getElementById('results').innerHTML = '<div class="result success">CSRF token refreshed successfully!</div>';
            } catch (error) {
                console.error('Token refresh error:', error);
                document.getElementById('results').innerHTML = '<div class="result error">Failed to refresh CSRF token</div>';
            }
        });
    </script>
</body>
</html>