<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupCategory extends Model
{
    protected $fillable = [
        'group_id',
        'name',
        'icon',
        'color'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(GroupExpense::class, 'category_id');
    }
}
