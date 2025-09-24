<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDFtk Binary Path
    |--------------------------------------------------------------------------
    |
    | Path to the PDFtk binary. By default, it assumes 'pdftk' is available 
    | in the system PATH. You can specify a full path if needed.
    |
    */
    'binary' => env('PDFTK_BINARY_PATH', 'pdftk'),

    /*
    |--------------------------------------------------------------------------
    | PDF Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for storing PDF files. These settings determine where
    | PDF templates and generated files are stored.
    |
    */
    'storage' => [
        // Disk to use for PDF storage (defined in filesystems.php)
        'disk' => env('PDFTK_STORAGE_DISK', 'local'),
        
        // Directory paths within the storage disk
        'templates_path' => env('PDFTK_TEMPLATES_PATH', 'pdf/templates'),
        'generated_path' => env('PDFTK_GENERATED_PATH', 'pdf/generated'),
        'temp_path' => env('PDFTK_TEMP_PATH', 'pdf/temp'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Processing Options
    |--------------------------------------------------------------------------
    |
    | Default options for PDF processing operations.
    |
    */
    'options' => [
        // Maximum file size for PDF uploads (in bytes)
        'max_file_size' => env('PDFTK_MAX_FILE_SIZE', 10 * 1024 * 1024), // 10MB
        
        // Allowed MIME types for PDF files
        'allowed_mime_types' => [
            'application/pdf',
        ],
        
        // Default permissions for generated files
        'file_permissions' => 0644,
        
        // Whether to flatten forms after filling (removes ability to edit)
        'flatten_forms' => env('PDFTK_FLATTEN_FORMS', true),
        
        // Whether to enable appearance generation for form fields
        'need_appearances' => env('PDFTK_NEED_APPEARANCES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Configuration for error handling and logging.
    |
    */
    'error_handling' => [
        // Whether to log PDFtk errors
        'log_errors' => env('PDFTK_LOG_ERRORS', true),
        
        // Log channel to use (defined in logging.php)
        'log_channel' => env('PDFTK_LOG_CHANNEL', 'single'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration for PDF processing.
    |
    */
    'security' => [
        // Default encryption settings
        'default_user_password' => env('PDFTK_DEFAULT_USER_PASSWORD'),
        'default_owner_password' => env('PDFTK_DEFAULT_OWNER_PASSWORD'),
        
        // PDF permissions (when encrypting)
        'permissions' => [
            'print' => true,
            'modify' => false,
            'copy' => true,
            'annotate' => false,
        ],
    ],
];