<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Model;

class CategoryService
{
    public function getAllCategories(int $userId): array
    {
        $defaultCategories = Category::query()->default()->get();
        $userCategories = Category::query()->user($userId)->get();
        $allCategories = Category::query()->availableFor($userId)->get();

        return [
            'default' => $defaultCategories,
            'user' => $userCategories,
            'all' => $allCategories,
        ];
    }

    public function getCategoryById(int $id, int $userId): ?Model
    {
        return Category::query()
            ->where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('is_default', true)
                    ->orWhere('user_id', $userId);
            })
            ->with(['expenses' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->orderBy('date', 'desc');
            }])
            ->first();
    }

    public function getCategoryBySlug(string $slug, int $userId): ?Model
    {
        return Category::query()
            ->where('slug', $slug)
            ->where(function ($query) use ($userId) {
                $query->where('is_default', true)
                    ->orWhere('user_id', $userId);
            })
            ->with(['expenses' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->orderBy('date', 'desc');
            }])
            ->first();
    }

    public function createCategory(int $userId, array $data)
    {
        return Category::query()->create([
            'user_id' => $userId,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
            'is_default' => false,
        ]);
    }

    public function updateCategory($id, int $userId, array $data): ?Model
    {
        $category = Category::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('is_default', false)
            ->first();

        if (!$category) {
            return null;
        }

        $category->update($data);
        return $category;
    }

    public function deleteCategory($id, int $userId): bool
    {
        $category = Category::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('is_default', false)
            ->first();

        if (!$category) {
            return false;
        }

        $hasExpenses = Expense::query()
            ->where('category_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($hasExpenses) {
            return false;
        }

        return $category->delete();
    }
}
