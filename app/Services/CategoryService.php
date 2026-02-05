<?php

namespace App\Services;

use App\Models\DefaultCategory;
use App\Models\Expense;
use App\Models\User;
use App\Models\UserCategory;
use Exception;
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
            return DefaultCategory::query()
                ->with(['expenses' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->orderBy('date', 'desc');
                }])
                ->find($id);
        }

        return UserCategory::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->with(['expenses' => function ($query) {
                $query->orderBy('date', 'desc');
            }])
            ->first();
    }

    public function getCategoryBySlug(string $slug, int $userId): ?Model
    {
        return DefaultCategory::query()
            ->where('slug', $slug)
            ->with(['expenses' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->orderBy('date', 'desc');
            }])
            ->first();
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
        $category = UserCategory::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$category) {
            throw new Exception('Category not found');
        }

        $hasExpenses = Expense::query()->where('category_id', $id)
            ->where('category_type', 'user')
            ->where('user_id', $userId)
            ->exists();

        if ($hasExpenses) {
            throw new Exception('Cannot delete category with associated expenses');
        }

        return $category->delete();
    }
}
