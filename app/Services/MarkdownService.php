<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Str;

class MarkdownService
{
    /**
     * Pattern for [[wikilink]] titles.
     */
    private const WIKILINK = '/\[\[([^\]\[]+)\]\]/';

    /**
     * Pattern for #tags (a # not preceded by a word char, then letters/numbers/_/-).
     * Markdown headings ("# Title") are safe because they have a space after #.
     */
    private const TAG = '/(?<!\w)#([\p{L}\p{N}_-]+)/u';

    /**
     * Extract the unique titles referenced via [[...]] in the content.
     *
     * @return array<int, string>
     */
    public function extractWikilinks(string $content): array
    {
        preg_match_all(self::WIKILINK, $content, $matches);

        return collect($matches[1])
            ->map(fn (string $title) => trim($title))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Extract the unique tag names referenced via #tag in the content.
     *
     * @return array<int, string>
     */
    public function extractTags(string $content): array
    {
        preg_match_all(self::TAG, $content, $matches);

        return collect($matches[1])
            ->unique()
            ->values()
            ->all();
    }

 
    public function toHtml(string $content, ?int $userId = null): string
    {
        $userId = $userId ?? (int) auth()->id();

        
        $notes = Note::query()
            ->visibleTo($userId)
            ->get(['id', 'title', 'slug', 'visibility'])
            ->keyBy('title');

        $content = preg_replace_callback(self::WIKILINK, function (array $m) use ($notes) {
            $title = trim($m[1]);
            $note = $notes[$title] ?? null;

            return $note
                ? '['.$title.']('.url($this->pathFor($note)).')'
                : '['.$title.']('.url('/notes').')';
        }, $content);

        $content = preg_replace_callback(self::TAG, function (array $m) {
            return '[#'.$m[1].']('.url('/tags').')';
        }, $content);

        return Str::markdown($content);
    }

    private function pathFor(Note $note): string
    {
        return $note->visibility === 'public'
            ? '/wiki/'.$note->slug
            : '/notes/'.$note->slug;
    }
}
