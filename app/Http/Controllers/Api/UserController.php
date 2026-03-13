<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'ILIKE', "%{$query}%")
                    ->orWhere('email', 'ILIKE', "%{$query}%");
            })
            ->where('id', '!=', auth()->id())
            ->limit(10)
            ->get();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }
}
