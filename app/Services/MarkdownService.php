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

    /**
     * Render markdown content to safe HTML, turning [[wikilinks]] into note
     * links and #tags into tag links before handing off to CommonMark.
     */
    public function toHtml(string $content, ?int $userId = null): string
    {
        $userId ??= auth()->id();

        // title => id map for the user's notes (last wins on duplicate titles).
        $notesByTitle = Note::query()
            ->where('user_id', $userId)
            ->pluck('id', 'title');

        $content = preg_replace_callback(self::WIKILINK, function (array $m) use ($notesByTitle) {
            $title = trim($m[1]);
            $id = $notesByTitle[$title] ?? null;

            return $id
                ? '[' . $title . '](' . url('/notes/' . $id) . ')'
                : '[' . $title . '](' . url('/notes') . ')';
        }, $content);

        $content = preg_replace_callback(self::TAG, function (array $m) {
            return '[#' . $m[1] . '](' . url('/tags') . ')';
        }, $content);

        return Str::markdown($content);
    }
}
