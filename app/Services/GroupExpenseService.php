<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\GroupExpense;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class GroupExpenseService
{
    public function getGroupExpenses(int $groupId, int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isMember($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        $query = GroupExpense::with(['user', 'category'])
            ->where('group_id', $groupId)
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    public function createExpense(int $groupId, int $userId, array $data): GroupExpense
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isMember($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        // Only validate category if it's provided and not null
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            $category = GroupCategory::query()->where('id', $data['category_id'])
                ->where('group_id', $groupId)
                ->first();

            if (!$category) {
                throw new Exception('Category not found or does not belong to this group');
            }
        }

        return GroupExpense::query()->create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'converted_amount' => $data['converted_amount'],
            'exchange_rate' => $data['exchange_rate'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
        ]);
    }

    public function updateExpense(int $expenseId, int $userId, array $data): ?GroupExpense
    {
        $expense = GroupExpense::with('group')->find($expenseId);

        if (!$expense) {
            throw new Exception('Expense not found');
        }

        // Allow expense author or group owner to update
        $isOwner = $expense->group->owner_id === $userId;
        if ($expense->user_id !== $userId && !$isOwner) {
            throw new Exception('Only expense author or group owner can update it');
        }

        // Only validate category if it's provided and not null
        if (isset($data['category_id']) && $data['category_id'] !== null) {
            $category = GroupCategory::query()->where('id', $data['category_id'])
                ->where('group_id', $expense->group_id)
                ->first();

            if (!$category) {
                throw new Exception('Category not found or does not belong to this group');
            }
        }

        $expense->update($data);

        return $expense->load(['user', 'category']);
    }

    public function deleteExpense(int $expenseId, int $userId): bool
    {
        $expense = GroupExpense::with('group')->find($expenseId);

        if (!$expense) {
            throw new Exception('Expense not found');
        }

        // Allow expense author or group owner to delete
        $isOwner = $expense->group->owner_id === $userId;
        if ($expense->user_id !== $userId && !$isOwner) {
            throw new Exception('Only expense author or group owner can delete it');
        }

        return $expense->delete();
    }

    public function getGroupStats(int $groupId, int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isMember($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        $query = GroupExpense::query()->where('group_id', $groupId);

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $categoryStats = $query->clone()
            ->selectRaw('category_id, SUM(converted_amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->map(function ($stat) {
                $category = $stat->category_id ? GroupCategory::query()->find($stat->category_id) : null;
                return [
                    'category' => $category,
                    'total' => (float)$stat->total,
                    'count' => $stat->count,
                ];
            });

        $userStats = $query->clone()
            ->selectRaw('user_id, SUM(converted_amount) as total, COUNT(*) as count')
            ->groupBy('user_id')
            ->get()
            ->map(function ($stat) {
                $user = User::find($stat->user_id);
                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'total' => (float)$stat->total,
                    'count' => $stat->count,
                ];
            });

        $total = $query->sum('converted_amount');

        $categoryStats = $categoryStats->map(function ($item) use ($total) {
            $item['percentage'] = $total > 0 ? round(($item['total'] / $total) * 100, 2) : 0;
            return $item;
        });

        $userStats = $userStats->map(function ($item) use ($total) {
            $item['percentage'] = $total > 0 ? round(($item['total'] / $total) * 100, 2) : 0;
            return $item;
        });

        return [
            'total' => (float)$total,
            'count' => $query->count(),
            'by_category' => $categoryStats,
            'by_user' => $userStats,
        ];
    }
}
