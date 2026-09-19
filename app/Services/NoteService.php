<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NoteService
{
    public function __construct(private MarkdownService $markdown) {}

    public function createNote(User $user, array $data): Note
    {

        $validated = validator($data, [
            'title' => 'required|string',
            'content' => 'nullable|string',
            'visibility' => 'nullable|in:private,public',
        ])->validate();

        $note = new Note([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'visibility' => $validated['visibility'] ?? 'private',
        ]);
        $note->user_id = $user->id;
        $note->slug = $this->uniqueSlug($this->slugify($validated['title']), $note);
        $note->save();

        $this->syncReferences($note);

        return $note;
    }

    public function updateNote(Note $note, array $data): bool
    {

        if (! $this->canEdit($note)) {
            return false;
        }

        $validated = validator($data, [
            'title' => 'required|string',
            'content' => 'nullable',
            'visibility' => 'nullable|in:private,public',

        ])->validate();

        $note->title = $validated['title'];
        $note->content = $validated['content'] ?? null;

        // Only the owner can change a page's visibility.
        if (array_key_exists('visibility', $validated) && $note->user_id === auth()->id()) {
            $note->visibility = $validated['visibility'];
        }

        $note->slug = $this->uniqueSlug($this->slugify($validated['title']), $note);

        $updated = $note->save();

        $this->syncReferences($note);

        return $updated;

    }

    public function deleteNote(Note $note): bool
    {
        if ($note->user_id != auth()->id()) {
            return false;
        }

        return $note->delete();
    }

    /**
     * Whether the current user may edit this note's content: the owner,
     * or any logged-in user (it's a public wiki page) may edit; only the
     * owner may delete it or change its visibility. Guests may only read.
     */
    public function canEdit(Note $note): bool
    {
        return auth()->check() && ($note->user_id === auth()->id() || $note->visibility === 'public');
    }

    /**
     * Notes visible to the user (their own, of any visibility, plus every
     * public page) whose title or content matches the search term.
     *
     * @return Collection<int, Note>
     */
    public function search(User $user, ?string $term): Collection
    {
        return Note::query()
            ->visibleTo($user->id)
            ->matching($term)
            ->latest('updated_at')
            ->get();
    }

    /**
     * Keep the note's #tags and [[wikilinks]] in sync with its content.
     * The content is the source of truth for both.
     */
    private function syncReferences(Note $note): void
    {
        $content = (string) $note->content;

        // #tags -> tags / note_tag pivot. Public notes share a global tag
        // namespace so every editor lands on the same tag; private notes
        // keep the existing per-user scoping.
        $tagIds = collect($this->markdown->extractTags($content))
            ->map(function (string $name) use ($note) {
                if ($note->visibility === 'public') {
                    return Tag::firstOrCreate(
                        ['name' => $name, 'visibility' => 'public'],
                        ['user_id' => $note->user_id, 'color' => '#0047AB'],
                    )->id;
                }

                return Tag::firstOrCreate(
                    ['user_id' => $note->user_id, 'name' => $name, 'visibility' => 'private'],
                    ['color' => '#0047AB'],
                )->id;
            })
            ->all();

        $note->tags()->sync($tagIds);

        // [[wikilinks]] -> note_links pivot. A link can resolve to any of
        // the author's own notes or to any public page, regardless of author.
        $linkIds = Note::query()
            ->visibleTo($note->user_id)
            ->whereIn('title', $this->markdown->extractWikilinks($content))
            ->where('id', '!=', $note->id)
            ->pluck('id')
            ->all();

        $note->outgoingLinks()->sync($linkIds);
    }

    /**
     * Turn a title into a slug, preserving "/" namespace separators
     * (e.g. "Proyectos/RedNotes" -> "proyectos/red-notes").
     */
    private function slugify(string $title): string
    {
        return collect(explode('/', $title))
            ->map(fn (string $segment) => Str::slug($segment) ?: 'nota')
            ->implode('/');
    }

    /**
     * Make sure the slug is unique within the note's own scope (private
     * notes only need to be unique per owner, public pages globally),
     * appending "-2", "-3", ... on collision.
     */
    private function uniqueSlug(string $base, Note $note): string
    {
        $slug = $base;
        $suffix = 2;

        while ($this->slugTaken($slug, $note)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function slugTaken(string $slug, Note $note): bool
    {
        $query = Note::query()->where('slug', $slug)->where('id', '!=', $note->id ?? 0);

        return $note->visibility === 'public'
            ? $query->where('visibility', 'public')->exists()
            : $query->where('user_id', $note->user_id)->where('visibility', 'private')->exists();
    }
}
