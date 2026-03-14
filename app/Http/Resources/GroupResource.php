<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
                'avatar' => $this->owner->avatar ? Storage::disk('r2')->url($this->owner->avatar) : null,
            ],
            'members_count' => $this->members->count(),
            'members' => $this->when(
                $this->relationLoaded('members'),
                function () {
                    return $this->members->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => $member->name,
                            'email' => $member->email,
                            'avatar' => $member->avatar ? Storage::disk('r2')->url($member->avatar) : null,
                            'role' => $member->pivot->role,
                            'joined_at' => $member->pivot->joined_at,
                        ];
                    });
                }
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
