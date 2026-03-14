<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'currency',
        'converted_amount',
        'exchange_rate',
        'description',
        'date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'converted_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'date' => 'date'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
