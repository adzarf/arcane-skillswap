<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Helpers\JwtHelper;
use App\Helpers\ResponseHelper;

class AuthMiddleware implements Middleware
{
    private JwtHelper $jwt;

    public function __construct(JwtHelper $jwt)
    {
        $this->jwt = $jwt;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $auth = $request->getHeaderLine('Authorization');

        // Debug: log what we actually received
        error_log('AuthMiddleware - Authorization header: ' . substr($auth, 0, 50));
        error_log('AuthMiddleware - Method: ' . $request->getMethod());
        error_log('AuthMiddleware - Path: ' . $request->getUri()->getPath());

        if (! $auth || ! str_starts_with($auth, 'Bearer ')) {
            $response = new \Slim\Psr7\Response();
            return ResponseHelper::json($response, false, 'Unauthorized - no bearer: ' . substr($auth, 0, 30), null, [])->withStatus(401);
        }

        $token = substr($auth, 7);
        try {
            $claims = $this->jwt->decode($token);
            $request = $request->withAttribute('jwt', $claims);
            return $handler->handle($request);
        } catch (\Throwable $e) {
            $response = new \Slim\Psr7\Response();
            return ResponseHelper::json($response, false, 'Invalid token: ' . $e->getMessage() . ' | token_start: ' . substr($token, 0, 30), null, [])->withStatus(401);
        }
    }
}