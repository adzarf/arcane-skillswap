<?php
declare(strict_types=1);

namespace App\Bootstrap;

use DI\ContainerBuilder;
use Slim\Factory\AppFactory as SlimAppFactory;

class AppFactory
{
    public static function createApp(): \Psr\Http\Server\RequestHandlerInterface
    {
        // Build PHP-DI Container
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../app/dependencies.php');
        $container = $containerBuilder->build();

        SlimAppFactory::setContainer($container);
        $app = SlimAppFactory::create();

        // Register middleware and routes
        $settings = $container->get('settings');

        // Error middleware
        $app->addErrorMiddleware($settings['displayErrorDetails'] ?? true, true, true);

        // Load routes
        $routes = __DIR__ . '/../src/Routes/api.php';
        if (file_exists($routes)) {
            (require $routes)($app);
        }

        return $app;
    }
}
