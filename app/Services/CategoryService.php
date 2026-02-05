<?php

namespace App\Services;

use App\Models\DefaultCategory;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Database\Eloquent\Model;

class CategoryService
{
    public function getAllCategories(int $userId): array
    {

        $user = User::with('customCategories')->find($userId);

        return [
            'default' => DefaultCategory::all(),
            'user' => $user->customCategories,
            'all' => DefaultCategory::all()->merge($user->customCategories),
        ];

    }

    public function getCategoryById(int $id, string $type, int $userId): ?Model
    {
        if ($type === 'default') {
            return DefaultCategory::query()->find($id);
        }

        return UserCategory::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getCategoryBySlug(string $slug): ?Model
    {
        return DefaultCategory::query()->where('slug', $slug)->first();
    }

    public function createCategory(int $userId, array $data)
    {
        return UserCategory::query()->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
        ]);
    }

    public function updateCategory($id, int $userId, array $data): ?Model
    {
        $category = UserCategory::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($category) {
            $category->update($data);
            return $category;
        } else {
            return null;
        }
    }

    public function deleteCategory($id, int $userId): bool
    {
//        if (!$this->canDeleteCategory($id, $userId)) {
//            return false;
//        }

        $category = UserCategory::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($category) {
            return $category->delete();
        } else {
            return false;
        }
    }

    private function canDeleteCategory($id, int $userId): bool
    {
        $category = UserCategory::query()->where('id', $id)
            ->where('user_id', $userId)
            ->withCount('expenses')
            ->first();

        return $category && $category->expenses_count === 0;
    }
}
