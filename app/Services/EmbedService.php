<?php

namespace App\Services;

use App\Models\Embed;
use App\Models\User;
use Illuminate\Support\Str;

class EmbedService
{
    /**
     * Permissions granted to an embedded page. There is deliberately no
     * "allow-same-origin": the page runs its own JavaScript, so it must stay
     * on an opaque origin where it cannot reach the app's cookies, storage
     * or authenticated endpoints.
     */
    public const SANDBOX = 'allow-scripts allow-forms allow-modals';

    /**
     * Reports the page's height to the host so the iframe can grow to fit
     * (handled in app.js). A sandboxed frame cannot be measured from outside.
     */
    private const RESIZE_SCRIPT = '<script>(function(){function s(){parent.postMessage({rednotesEmbedHeight:document.documentElement.scrollHeight},"*")}new ResizeObserver(s).observe(document.documentElement);addEventListener("load",s)})()</script>';

    public function createEmbed(User $user, array $data): Embed
    {
        $validated = $this->validate($data);

        $embed = new Embed($validated);
        $embed->user_id = $user->id;
        $embed->slug = $this->uniqueSlug(Str::slug($validated['title']) ?: 'embed');
        $embed->save();

        return $embed;
    }

    /**
     * The slug is kept on rename: notes reference embeds by it
     * ({{embed:slug}}), so changing it would silently break them.
     */
    public function updateEmbed(Embed $embed, array $data): bool
    {
        if (! $this->canEdit($embed)) {
            return false;
        }

        return $embed->fill($this->validate($data))->save();
    }

    public function deleteEmbed(Embed $embed): bool
    {
        if (! $this->canEdit($embed)) {
            return false;
        }

        return $embed->delete();
    }

    /**
     * Anyone may view an embed, but only its owner may change the code.
     */
    public function canEdit(Embed $embed): bool
    {
        return auth()->check() && $embed->user_id === auth()->id();
    }

    /**
     * The full HTML document served inside the iframe.
     */
    public function document(Embed $embed): string
    {
        $html = (string) $embed->html;
        $position = strripos($html, '</body>');

        return $position === false
            ? $html.self::RESIZE_SCRIPT
            : substr_replace($html, self::RESIZE_SCRIPT, $position, 0);
    }

    private function validate(array $data): array
    {
        return validator($data, [
            'title' => 'required|string|max:255',
            'html' => 'nullable|string|max:1000000',
        ])->validate();
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $suffix = 2;

        while (Embed::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
