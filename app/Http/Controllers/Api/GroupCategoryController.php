<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupCategoryRequest;
use App\Http\Requests\Group\UpdateGroupCategoryRequest;
use App\Http\Resources\GroupCategoryResource;
use App\Services\GroupCategoryService;
use Exception;
use Illuminate\Http\JsonResponse;

class GroupCategoryController extends Controller
{
    public function __construct(protected GroupCategoryService $categoryService)
    {
    }

    public function index(int $groupId): JsonResponse
    {
        try {
            $categories = $this->categoryService->getGroupCategories($groupId, auth()->id());

            return response()->json([
                'data' => GroupCategoryResource::collection($categories),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function store(StoreGroupCategoryRequest $request, int $groupId): JsonResponse
    {
        try {
            $category = $this->categoryService->createCategory(
                $groupId,
                auth()->id(),
                $request->validated()
            );

            return response()->json(new GroupCategoryResource($category), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function update(UpdateGroupCategoryRequest $request, int $groupId, int $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory(
                $id,
                auth()->id(),
                $request->validated()
            );

            return response()->json(new GroupCategoryResource($category));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }


    public function destroy(int $groupId, int $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory($id, auth()->id());

            return response()->json(['message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
