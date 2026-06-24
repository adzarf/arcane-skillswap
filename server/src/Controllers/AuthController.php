<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Services\AuthService;
use App\Helpers\ResponseHelper;

class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();

        // minimal validation
        if (empty($data['email']) || empty($data['password'])) {
            return ResponseHelper::json($response, false, 'Validation failed', null, ['email' => 'Email and password required']);
        }

        try {
            $result = $this->authService->register($data);
            return ResponseHelper::json($response, true, 'Registration successful', ['user' => $result['user'], 'access_token' => $result['access_token'], 'refresh_token' => $result['refresh_token']]);
        } catch (\Exception $e) {
            return ResponseHelper::json($response, false, $e->getMessage(), null, []);
        }
    }

    public function login(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        try {
            $result = $this->authService->login($data['email'] ?? '', $data['password'] ?? '');
            return ResponseHelper::json($response, true, 'Login successful', ['user' => $result['user'], 'access_token' => $result['access_token'], 'refresh_token' => $result['refresh_token']]);
        } catch (\Exception $e) {
            return ResponseHelper::json($response, false, $e->getMessage(), null, []);
        }
    }

    public function refresh(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        try {
            $result = $this->authService->refresh($data['refresh_token'] ?? '');
            return ResponseHelper::json($response, true, 'Token refreshed', ['access_token' => $result['access_token']]);
        } catch (\Exception $e) {
            return ResponseHelper::json($response, false, $e->getMessage(), null, []);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        $data = (array)$request->getParsedBody();
        $token = $data['refresh_token'] ?? '';
        if ($token) {
            $this->authService->revokeRefreshToken($token);
        }

        return ResponseHelper::json($response, true, 'Logged out', (object)[]);
    }
}
