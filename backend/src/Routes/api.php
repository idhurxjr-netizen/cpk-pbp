<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/HealthController.php';
require_once __DIR__ . '/../Controllers/AuthController.php';

$router->get('/api/health', [new HealthController($config), 'index']);

$auth = new AuthController($config);

$router->post('/api/auth/register', [$auth, 'register']);
$router->post('/api/auth/login', [$auth, 'login']);
$router->get('/api/me', [$auth, 'me']);
$router->delete('/api/me', [$auth, 'deleteMe']);