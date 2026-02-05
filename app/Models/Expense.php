<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'category_type',
        'amount',
        'description',
        'date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function defaultCategory(): BelongsTo
    {
        return $this->belongsTo(DefaultCategory::class, 'category_id');
    }

    public function userCategory(): BelongsTo
    {
        return $this->belongsTo(UserCategory::class, 'category_id');
    }

    public function getCategoryAttribute()
    {
        return $this->category_type === 'default'
            ? $this->defaultCategory
            : $this->userCategory;
    }
}
