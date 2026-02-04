<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService)
    {
    }

    public function index(Request $request)
    {
        $categories = $this->categoryService->getAllCategories(
            $request->user()->id
        );

        return response()->json([
            'default' => CategoryResource::collection($categories['default']),
            'user' => CategoryResource::collection($categories['user']),
            'all' => CategoryResource::collection($categories['all']),
        ]);
    }

    public function show(Request $request, string $type, int $id)
    {
        $category = $this->categoryService->getCategoryById(
            $id,
            $type,
            $request->user()->id
        );

        if ($category) {
            return new CategoryResource($category);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function showBySlug(string $slug)
    {
        $category = $this->categoryService->getCategoryBySlug($slug);

        if ($category) {
            return new CategoryResource($category);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->createCategory(
            $request->user()->id,
            $request->validated()
        );

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $updated = $this->categoryService->updateCategory(
            $id,
            $request->user()->id,
            $request->validated()
        );

        if ($updated) {
            return response()->json([
                'message' => 'Category updated successfully',
                'category' => new CategoryResource($updated)
            ]);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = $this->categoryService->deleteCategory(
            $id,
            $request->user()->id
        );

        if ($deleted) {
            return response()->json(['message' => 'Category deleted successfully'], 204);
        } else {
            return response()->json(['message' => 'Category not found, not owned by user, or cannot be deleted'], 404);
        }
    }
}
