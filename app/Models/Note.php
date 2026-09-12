<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    protected $fillable = [
        'title', 'user_id', 'content', 'slug', 'visibility',
    ];

    
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(fn ($q) => $q->where('user_id', $userId)->orWhere('visibility', 'public'));
    }

    public function scopeMatching(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(fn ($q) => $q->where('title', 'like', "%{$term}%")->orWhere('content', 'like', "%{$term}%"));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }


    public function outgoingLinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'source_note_id', 'target_note_id');
    }

   
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'target_note_id', 'source_note_id');
    }

    
    public static function tree(iterable $notes): array
    {
        $tree = [];

        foreach ($notes as $note) {
            $cursor = &$tree;

            $segments = explode('/', $note->title);
            $last = array_key_last($segments);

            foreach ($segments as $i => $segment) {
                $cursor[$segment] ??= ['label' => $segment, 'note' => null, 'children' => []];

                if ($i === $last) {
                    $cursor[$segment]['note'] = $note;
                }

                $cursor = &$cursor[$segment]['children'];
            }
        }

        return $tree;
    }
}
