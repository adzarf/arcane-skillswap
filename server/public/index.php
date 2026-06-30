<?php
declare(strict_types=1);

use App\Bootstrap\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

\Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$app = AppFactory::createApp();
$app->run();
