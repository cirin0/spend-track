<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'group_id' => $this->group_id,
            'amount' => (float)$this->amount,
            'currency' => $this->currency,
            'converted_amount' => (float)$this->converted_amount,
            'exchange_rate' => (float)$this->exchange_rate,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d'),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'icon' => $this->category->icon,
                'color' => $this->category->color,
            ] : null,
            'can_edit' => $this->user_id === auth()->id(),
            'can_delete' => $this->user_id === auth()->id(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
