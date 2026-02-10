<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\InviteMemberRequest;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Services\GroupService;
use Exception;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    public function __construct(protected GroupService $groupService)
    {
    }

    public function index(): JsonResponse
    {
        $groups = $this->groupService->getUserGroups(auth()->id());

        return response()->json([
            'data' => GroupResource::collection($groups),
        ]);
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = $this->groupService->createGroup(
            auth()->id(),
            $request->validated()
        );

        return response()->json(new GroupResource($group), 201);
    }

    public function show(int $id): JsonResponse
    {

        $group = $this->groupService->getGroupById($id, auth()->id());

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        return response()->json(new GroupResource($group));
    }

    public function update(UpdateGroupRequest $request, int $id): JsonResponse
    {
        try {
            $group = $this->groupService->updateGroup(
                $id,
                auth()->id(),
                $request->validated()
            );

            return response()->json(new GroupResource($group));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->groupService->deleteGroup($id, auth()->id());

            return response()->json(['message' => 'Group deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        }
    }

    public function addMember(InviteMemberRequest $request, int $id): JsonResponse
    {
        try {
            $this->groupService->addMember(
                $id,
                $request->user_id,
                auth()->id()
            );

            $group = $this->groupService->getGroupById($id, auth()->id());

            return response()->json(new GroupResource($group), 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function removeMember(int $id, int $userId): JsonResponse
    {
        try {
            $this->groupService->removeMember($id, $userId, auth()->id());

            return response()->json(['message' => 'Member removed successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function leave(int $id): JsonResponse
    {
        try {
            $this->groupService->leaveGroup($id, auth()->id());

            return response()->json(['message' => 'Left group successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
