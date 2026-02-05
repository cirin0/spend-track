<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->when(isset($this->slug), $this->slug),
            'icon' => $this->icon,
            'color' => $this->color,
            'user_id' => $this->when(isset($this->user_id), $this->user_id),
            'type' => $this->when(isset($this->user_id), 'user', 'default'),
            'expenses' => $this->when($this->relationLoaded('expenses'), function () {
                return ExpenseResource::collection($this->expenses);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
