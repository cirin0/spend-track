<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupCategory;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class GroupCategoryService
{
    public function getGroupCategories(int $groupId, int $userId): Collection
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isMember($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        return GroupCategory::query()->where('group_id', $groupId)->get();
    }

    public function createCategory(int $groupId, int $userId, array $data): GroupCategory
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isMember($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        return GroupCategory::query()->create([
            'group_id' => $groupId,
            'name' => $data['name'],
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
        ]);
    }

    public function updateCategory(int $categoryId, int $userId, array $data): ?GroupCategory
    {
        $category = GroupCategory::query()->find($categoryId);

        if (!$category) {
            throw new Exception('Category not found');
        }

        $group = $category->group;

        if (!$group->isMember($userId)) {
            throw new Exception('Unauthorized');
        }

        $category->update($data);

        return $category;
    }

    public function deleteCategory(int $categoryId, int $userId): bool
    {
        $category = GroupCategory::with('group')->find($categoryId);

        if (!$category) {
            throw new Exception('Category not found');
        }

        if (!$category->group->isOwner($userId)) {
            throw new Exception('Only owner can delete categories');
        }

        if ($category->expenses()->exists()) {
            throw new Exception('Cannot delete category with existing expenses');
        }

        return $category->delete();
    }

}
