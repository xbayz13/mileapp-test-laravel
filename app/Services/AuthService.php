<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user and generate JWT token
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function register(string $name, string $email, string $password): ?array
    {
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $token = JWTAuth::fromUser($user);
            $expiresIn = config('jwt.ttl') * 60;

            return [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'user' => [
                    'id' => (string) $user->_id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Authenticate user and generate JWT token
     *
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        $token = JWTAuth::fromUser($user);
        $expiresIn = config('jwt.ttl') * 60;
        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $expiresIn,
            'user' => [
                'id' => (string) $user->_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];
    }

    /**
     * Get authenticated user
     *
     * @return User|null
     */
    public function getAuthenticatedUser(): ?User
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Refresh JWT token
     *
     * @return array|null
     */
    public function refreshToken(): ?array
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
            $expiresIn = config('jwt.ttl') * 60;

            return [
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Logout user (invalidate token)
     *
     * @return bool
     */
    public function logout(): bool
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
