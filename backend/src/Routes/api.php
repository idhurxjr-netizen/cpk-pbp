<?php
declare(strict_types=1);

require_once __DIR__ . '/../Controllers/HealthController.php';

$router->get('/api/health', [new HealthController($config), 'index']);