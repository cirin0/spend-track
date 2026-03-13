<?php

namespace App\Services;

use App\Models\User;
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
            // Видалити старий аватар якщо існує
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }

            // Зберегти новий аватар
            $path = $avatarFile->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $user->update($data);

        return $user->fresh();
    }

}
