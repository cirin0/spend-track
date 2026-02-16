<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupExpenseRequest;
use App\Http\Resources\GroupExpenseResource;
use App\Services\GroupExpenseService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupExpenseController extends Controller
{
    public function __construct(protected GroupExpenseService $expenseService)
    {
    }

    public function index(Request $request, int $groupId): JsonResponse
    {
        try {
            $expenses = $this->expenseService->getGroupExpenses(
                $groupId,
                auth()->id(),
                $request->query('start_date'),
                $request->query('end_date')
            );

            return response()->json([
                'data' => GroupExpenseResource::collection($expenses),
                'total' => $expenses->sum('amount'),
                'count' => $expenses->count(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function store(StoreGroupExpenseRequest $request, int $groupId): JsonResponse
    {
        try {
            $expense = $this->expenseService->createExpense(
                $groupId,
                auth()->id(),
                $request->validated()
            );

            return response()->json(new GroupExpenseResource($expense), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function update(StoreGroupExpenseRequest $request, int $groupId, int $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->updateExpense(
                $id,
                auth()->id(),
                $request->validated()
            );

            return response()->json(new GroupExpenseResource($expense));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function destroy(int $groupId, int $id): JsonResponse
    {
        try {
            $this->expenseService->deleteExpense($id, auth()->id());

            return response()->json(['message' => 'Expense deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function stats(Request $request, int $groupId): JsonResponse
    {
        try {
            $stats = $this->expenseService->getGroupStats(
                $groupId,
                auth()->id(),
                $request->query('start_date'),
                $request->query('end_date')
            );

            return response()->json($stats);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }
}
