<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Login endpoint - returns JWT token
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $tokenData = $this->authService->authenticate(
            $request->input('email'),
            $request->input('password')
        );

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $tokenData,
        ], 200);
    }

    /**
     * Get authenticated user
     * 
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) $user->_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    /**
     * Refresh JWT token
     * 
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $tokenData = $this->authService->refreshToken();

        if (!$tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to refresh token',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => $tokenData,
        ], 200);
    }

    /**
     * Logout user
     * 
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $loggedOut = $this->authService->logout();

        if (!$loggedOut) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to logout',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ], 200);
    }
}
