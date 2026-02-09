<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use App\Traits\SlugGenerationTrait;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use SlugGenerationTrait;

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

    public function show(Request $request, int $id)
    {
        $category = $this->categoryService->getCategoryById(
            $id,
            $request->user()->id
        );

        if ($category) {
            return new CategoryResource($category);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function showBySlug(Request $request, string $slug)
    {
        $category = $this->categoryService->getCategoryBySlug(
            $slug,
            $request->user()->id
        );

        if ($category) {
            return new CategoryResource($category);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        $slug = self::createOriginalSlug($request->name, Category::class);

        $category = $this->categoryService->createCategory(
            $request->user()->id,
            $request->validated() + ['slug' => $slug]
        );

        return new CategoryResource($category);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryService->updateCategory(
            $id,
            $request->user()->id,
            $request->validated()
        );

        if (!$category) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => new CategoryResource($category)
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $deleted = $this->categoryService->deleteCategory($id, $request->user()->id);

        if (!$deleted) {
            return response()->json([
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json(['message' => 'Category deleted successfully']);
    }
}
