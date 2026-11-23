<?php
declare(strict_types=1);

$allowedOrigins = [
    'http://127.0.0.1:5500',
    'http://localhost:5500',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}

header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    $router = require __DIR__ . '/../bootstrap/app.php';

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

    $router->dispatch($method, $path);
} catch (Throwable $e) {
    error_log($e->getMessage());

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => [
            'message' => 'Terjadi kesalahan server.',
        ],
    ]);
}