<?php
// router.php

// Serve static files directly
if (file_exists(__DIR__ . '/public' . $_SERVER['REQUEST_URI']) && is_file(__DIR__ . '/public' . $_SERVER['REQUEST_URI'])) {
    return false; // Let PHP serve it as is
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// API Routes
if (strpos($uri, '/api/') === 0) {
    $endpoint = str_replace('/api/', '', $uri);
    // Remove query string
    $parts = explode('/', $endpoint);
    $resource = $parts[0];

    $file = __DIR__ . '/api/' . $resource . '.php';

    if (file_exists($file)) {
        header('Content-Type: application/json');
        require $file;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API Endpoint not found']);
    }
    exit;
}

// Fallback to index.html for SPA
if (file_exists(__DIR__ . '/public/index.html')) {
    readfile(__DIR__ . '/public/index.html');
} else {
    // If not built yet or for dev
    if ($uri === '/install.php') {
        require __DIR__ . '/install.php';
    } else {
        echo "Frontend not found. Please build the frontend.";
    }
}
