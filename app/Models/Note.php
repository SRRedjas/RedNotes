<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    protected $fillable = [
        "title","user_id","content",
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function tags(): BelongsToMany

    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Notes that THIS note links to (via [[wikilinks]]).
     */
    public function outgoingLinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'source_note_id', 'target_note_id');
    }

    /**
     * Notes that link TO this note (backlinks / linked references).
     */
    public function backlinks(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_links', 'target_note_id', 'source_note_id');
    }


}
