<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{

    public function login(string $email, string $password): ?array
    {
        if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        return [
            'user' => auth()->user(),
            'token' => $token,
            'expires_at' => auth()->factory()->getTTL() * 60
        ];
    }

    public function register(string $name, string $email, string $password): User
    {

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function updateProfile(User $user, array $data, $avatarFile = null): User
    {
        if ($avatarFile) {
            if ($user->avatar && Storage::disk('r2')->exists($user->avatar)) {
                Storage::disk('r2')->delete($user->avatar);
            }

            $path = $avatarFile->store('avatars', 'r2');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return $user->fresh();
    }

}
