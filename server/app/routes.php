<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });
};

$app->get('/debug-env', function (Request $request, Response $response) {
    $data = [
        'JWT_ISSUER' => $_ENV['JWT_ISSUER'] ?? getenv('JWT_ISSUER') ?: 'NOT SET',
        'JWT_AUDIENCE' => $_ENV['JWT_AUDIENCE'] ?? getenv('JWT_AUDIENCE') ?: 'NOT SET',
        'JWT_SECRET_SET' => !empty($_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET')),
        'JWT_ACCESS_TTL' => $_ENV['JWT_ACCESS_TTL'] ?? getenv('JWT_ACCESS_TTL') ?: 'NOT SET',
    ];
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});