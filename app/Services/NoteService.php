<?php

namespace App\Services;


use App\Models\Note;
use App\Models\Tag;
use App\Models\User;

class NoteService{

    public function __construct(private MarkdownService $markdown)
    {
    }


    public function createNote(User $user ,array $data): Note
    {

        $validated = validator($data, [
            "title"   => "required|string",
            "content" => "nullable|string",
        ])->validate();

        $note = $user->notes()->create($validated);

        $this->syncReferences($note);

        return $note;
    }


    public function updateNote(Note $note,  array $data): bool
    {

        if($note->user_id != auth()->id()){
            return false;
        }


        $validated = validator($data, [
            "title" => "required|string",
            "content" => "nullable"

        ])->validate();

        $updated = $note->update($validated);

        $this->syncReferences($note);

        return $updated;

    }

    public function deleteNote(Note $note): bool
    {
        if($note->user_id != auth()->id()){
            return false;
        }
        return $note->delete();
    }

    /**
     * Keep the note's #tags and [[wikilinks]] in sync with its content.
     * The content is the source of truth for both.
     */
    private function syncReferences(Note $note): void
    {
        $content = (string) $note->content;

        // #tags -> tags / note_tag pivot
        $tagIds = collect($this->markdown->extractTags($content))
            ->map(fn (string $name) => Tag::firstOrCreate(
                ['user_id' => $note->user_id, 'name' => $name],
                ['color' => '#0047AB'],
            )->id)
            ->all();

        $note->tags()->sync($tagIds);

        // [[wikilinks]] -> note_links pivot (only resolved titles for this user)
        $linkIds = Note::query()
            ->where('user_id', $note->user_id)
            ->whereIn('title', $this->markdown->extractWikilinks($content))
            ->where('id', '!=', $note->id)
            ->pluck('id')
            ->all();

        $note->outgoingLinks()->sync($linkIds);
    }

}
