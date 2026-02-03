<?php

namespace App\Services;

use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{

    public function login(string $email, string $password)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !password_verify($password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ]);
        }

        if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
            return response()->json([
                'message' => 'Invalid credentials'
            ]);
        }

        return [
            'user' => $user,
            'token' => $token,
            'expires_at' => auth()->factory()->getTTL() * 60
        ];
    }

    public function register(string $name, string $email, string $password)
    {

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }
}
