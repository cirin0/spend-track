<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function getUserGroups(int $userId): Collection
    {
        return Group::query()->whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['owner', 'members'])->get();
    }

    public function getGroupById(int $groupId, int $userId): ?Group
    {
        return Group::query()->whereHas('members', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['owner', 'members',])->find($groupId);
    }

    public function createGroup(int $userId, array $data): Group
    {
        $group = Group::query()->create([
            'owner_id' => $userId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? null,
        ]);

        GroupMember::query()->create([
            'group_id' => $group->id,
            'user_id' => $userId,
            'role' => 'owner',
        ]);

        return $group->load(['owner', 'members']);
    }

    public function updateGroup(int $groupId, int $userId, array $data): ?Group
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isOwner($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        $group->update($data);

        return $group->load(['owner', 'members']);
    }

    public function deleteGroup(int $groupId, int $userId): bool
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isOwner($userId)) {
            throw new Exception('Unauthorized or group not found');
        }

        return $group->delete();
    }

    public function addMember(int $groupId, int $userIdToAdd, int $requesterId): GroupMember
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isOwner($requesterId)) {
            throw new Exception('Only owner can add members');
        }

        if ($group->isMember($userIdToAdd)) {
            throw new Exception('User is already a member');
        }

        $user = User::find($userIdToAdd);
        if (!$user) {
            throw new Exception('User not found');
        }

        return GroupMember::query()->create([
            'group_id' => $groupId,
            'user_id' => $userIdToAdd,
            'role' => 'member',
        ]);
    }

    public function removeMember(int $groupId, int $userIdToRemove, int $requesterId): bool
    {
        $group = Group::query()->find($groupId);

        if (!$group || !$group->isOwner($requesterId)) {
            throw new Exception('Only owner can remove members');
        }

        if ($userIdToRemove === $group->owner_id) {
            throw new Exception('Cannot remove owner from group');
        }

        return GroupMember::query()->where('group_id', $groupId)
            ->where('user_id', $userIdToRemove)
            ->delete();
    }

    public function leaveGroup(int $groupId, int $userId): bool
    {
        $group = Group::query()->find($groupId);

        if (!$group) {
            throw new Exception('Group not found');
        }

        if ($group->isOwner($userId)) {
            throw new Exception('Owner cannot leave group. Delete the group instead.');
        }

        return GroupMember::query()->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->delete();
    }
}
