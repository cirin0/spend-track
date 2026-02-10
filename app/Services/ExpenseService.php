<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ExpenseService
{
    public function getAllExpenses(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = Expense::with('category')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    public function getExpenseById(int $id, int $userId): ?Expense
    {
        return Expense::with('category')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getExpensesByCategory(int $categoryId, int $userId): Collection
    {
        return Expense::with('category')
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->orderBy('date', 'desc')
            ->get();
    }

    public function createExpense(int $userId, array $data): Expense
    {
        if (isset($data['category_id'])) {
            $this->validateCategory($data['category_id'], $userId);
        }

        return Expense::query()->create([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'],
        ]);
    }

    private function validateCategory(int $categoryId, int $userId): void
    {
        $exists = Category::query()
            ->where('id', $categoryId)
            ->where(function ($query) use ($userId) {
                $query->where('is_default', true)
                    ->orWhere('user_id', $userId);
            })
            ->exists();

        if (!$exists) {
            throw new Exception('Category not found or not available for user');
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

        if (isset($data['category_id'])) {
            $this->validateCategory($data['category_id'], $userId);
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
        $query = Expense::query()
            ->where('user_id', $userId)
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $stats = $query->get();

        return $stats->map(function ($stat) {
            $category = $stat->category_id ? Category::query()->find($stat->category_id) : null;

            return [
                'category' => $category,
                'total' => $stat->total,
                'count' => $stat->count,
                'percentage' => 0
            ];
        });
    }
}
