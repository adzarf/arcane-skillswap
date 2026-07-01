<?php
declare(strict_types=1);

// Handle CORS at the PHP entry point — before Slim routing runs.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::createMutable(__DIR__ . '/..')->safeLoad();

// Load AppFactory directly to avoid autoloader path issues
require_once __DIR__ . '/../bootstrap/AppFactory.php';

$app = \App\Bootstrap\AppFactory::createApp();
$app->run();