<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->email, $request->password);

        if (!$result) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $result['token'],
            'expires_at' => $result['expires_at'],
            'user' => new UserResource($result['user']),
        ]);
    }

    /**
     * @unauthenticated
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->name, $request->email, $request->password);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user)
        ], 201);
    }

    public function me()
    {
        return new UserResource(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->authService->updateProfile(
            auth()->user(),
            $request->validated(),
            $request->file('avatar')
        );

        return response()->json([
            'message' => 'Профіль успішно оновлено',
            'user' => new UserResource($user)
        ]);
    }

}
