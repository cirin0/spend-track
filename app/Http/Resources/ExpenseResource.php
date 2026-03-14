<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => (float)$this->amount,
            'currency' => $this->currency,
            'converted_amount' => (float)$this->converted_amount,
            'exchange_rate' => (float)$this->exchange_rate,
            'description' => $this->description,
            'date' => $this->date->format('Y-m-d'),
            'category' => [
                'id' => $this->category_id,
                'name' => $this->category?->name,
                'icon' => $this->category?->icon,
                'color' => $this->category?->color,
                'slug' => $this->category?->slug ?? null,
                'is_default' => $this->category?->is_default ?? false,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
