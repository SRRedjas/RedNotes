<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Embed extends Model
{
    protected $fillable = [
        'title', 'html',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeMatching(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where('title', 'like', "%{$term}%");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
