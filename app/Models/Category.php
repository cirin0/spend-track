<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'icon',
        'color',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeUser($query, int $userId)
    {
        return $query->where('user_id', $userId)->where('is_default', false);
    }

    public function scopeAvailableFor($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_default', true)
                ->orWhere('user_id', $userId);
        });
    }
}
