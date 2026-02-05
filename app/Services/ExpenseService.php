<?php

namespace App\Services;

use App\Models\DefaultCategory;
use App\Models\Expense;
use App\Models\UserCategory;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ExpenseService
{
    public function getAllExpenses(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Expense::with(['defaultCategory', 'userCategory'])
            ->where('user_id', $userId)
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->get()->map(function ($expense) {
            $expense->category = $expense->category_type === 'default'
                ? $expense->defaultCategory
                : $expense->userCategory;
            return $expense;
        });
    }

    public function getExpenseById(int $id, int $userId): ?Expense
    {
        $expense = Expense::with(['defaultCategory', 'userCategory'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($expense) {
            $expense->category = $expense->category_type === 'default'
                ? $expense->defaultCategory
                : $expense->userCategory;
        }

        return $expense;
    }

    public function getExpensesByCategory(int $categoryId, string $categoryType, int $userId): Collection
    {
        return Expense::with(['defaultCategory', 'userCategory'])
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('category_type', $categoryType)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function createExpense(int $userId, array $data): Expense
    {
        $this->validateCategory($data['category_id'], $data['category_type'], $userId);

        return Expense::query()->create([
            'user_id' => $userId,
            'category_id' => $data['category_id'],
            'category_type' => $data['category_type'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
        ]);
    }

    private function validateCategory(int $categoryId, string $categoryType, int $userId): void
    {
        if ($categoryType === 'default') {
            $exists = DefaultCategory::query()->where('id', $categoryId)->exists();
            if (!$exists) {
                throw new Exception('Default category not found');
            }
        } else {
            $exists = UserCategory::query()->where('id', $categoryId)
                ->where('user_id', $userId)
                ->exists();
            if (!$exists) {
                throw new Exception('User category not found or does not belong to user');
            }
        }
    }

    public function updateExpense(int $id, int $userId, array $data): ?Expense
    {
        $expense = Expense::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$expense) {
            return null;
        }

        if (isset($data['category_id']) && isset($data['category_type'])) {
            $this->validateCategory($data['category_id'], $data['category_type'], $userId);
        }

        $expense->update($data);

        return $expense;
    }

    public function deleteExpense(int $id, int $userId): bool
    {
        $expense = Expense::query()->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        return $expense ? $expense->delete() : false;
    }


    public function getStatsByCategory(int $userId, ?string $startDate = null, ?string $endDate = null)
    {
        $query = Expense::query()->where('user_id', $userId)
            ->selectRaw('category_id, category_type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id', 'category_type');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $stats = $query->get();

        return $stats->map(function ($stat) {
            if ($stat->category_type === 'default') {
                $category = DefaultCategory::query()->find($stat->category_id);
            } else {
                $category = UserCategory::query()->find($stat->category_id);
            }

            return [
                'category' => $category,
                'total' => $stat->total,
                'count' => $stat->count,
                'percentage' => 0
            ];
        });
    }
}
