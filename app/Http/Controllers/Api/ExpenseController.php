<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Exception;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenseService)
    {
    }

    public function index(Request $request)
    {
        $expenses = $this->expenseService->getAllExpenses(
            auth()->id(),
            $request->query('start_date'),
            $request->query('end_date')
        );

        return response()->json([
            'data' => ExpenseResource::collection($expenses),
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
        ]);
    }

    public function show(int $id)
    {
        $expense = $this->expenseService->getExpenseById($id, auth()->id());

        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found'
            ], 404);
        }
        return response()->json(new ExpenseResource($expense));
    }

    public function getExpensesByCategory(int $categoryId)
    {
        $expenses = $this->expenseService->getExpensesByCategory(
            $categoryId,
            auth()->id()
        );

        return response()->json([
            'data' => ExpenseResource::collection($expenses),
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
        ]);
    }

    public function store(StoreExpenseRequest $request)
    {
        try {
            $expense = $this->expenseService->createExpense(
                auth()->id(),
                $request->validated()
            );

            return response()->json(
                new ExpenseResource($expense),
                201
            );
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function storeByCategory(StoreExpenseRequest $request, int $categoryId)
    {
        try {
            $data = $request->validated();
            $data['category_id'] = $categoryId;

            $expense = $this->expenseService->createExpense(
                auth()->id(),
                $data
            );

            return response()->json(
                new ExpenseResource($expense),
                201
            );
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function update(UpdateExpenseRequest $request, int $id)
    {
        try {
            $expense = $this->expenseService->updateExpense(
                $id,
                auth()->id(),
                $request->validated()
            );

            if (!$expense) {
                return response()->json([
                    'message' => 'Expense not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Expense updated successfully',
                'expense' => new ExpenseResource($expense)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(int $id)
    {
        $deleted = $this->expenseService->deleteExpense(
            $id,
            auth()->id()
        );

        if (!$deleted) {
            return response()->json([
                'message' => 'Expense not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }

    public function stats(Request $request)
    {
        $stats = $this->expenseService->getStatsByCategory(
            auth()->id(),
            $request->query('start_date'),
            $request->query('end_date')
        );

        $total = $stats->sum('total');

        $statsWithPercentage = $stats->map(function ($item) use ($total) {
            $item['percentage'] = $total > 0 ? round(($item['total'] / $total) * 100, 2) : 0;
            return $item;
        });

        return response()->json([
            'stats' => $statsWithPercentage,
            'total' => $total,
        ]);
    }
}
