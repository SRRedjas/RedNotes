<?php

use App\Models\Note;
use App\Services\MarkdownService;
use App\Services\NoteService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

new class extends Component
{
    public int $noteId;

    public string $title = '';

    public ?string $content = '';

    public string $visibility = 'public';

    public bool $isOwner = false;

    public function mount(string $slug): void
    {
        $note = Note::where('visibility', 'public')->where('slug', $slug)->firstOrFail();

        $this->noteId = $note->id;
        $this->title = $note->title;
        $this->content = $note->content ?? '';
        $this->visibility = $note->visibility;
        $this->isOwner = $note->user_id === auth()->id();
    }

    public function save(NoteService $service): void
    {
        $note = Note::findOrFail($this->noteId);
        $previousSlug = $note->slug;

        $saved = $service->updateNote($note, [
            'title' => $this->title,
            'content' => $this->content,
            'visibility' => $this->isOwner ? $this->visibility : null,
        ]);

        abort_unless($saved, 403);

        LivewireAlert::title(__('Page saved'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(2500)
            ->show();

        // Renaming a page changes its slug, or the owner made it private,
        // so follow it to wherever it lives now.
        if ($note->slug !== $previousSlug || $note->visibility !== 'public') {
            $this->redirect(
                $note->visibility === 'public' ? route('wiki.show', $note) : route('notes.show', $note),
                navigate: true,
            );
        }
    }

    public function delete(NoteService $service)
    {
        $service->deleteNote(Note::findOrFail($this->noteId));

        return $this->redirect(route('wiki'), navigate: true);
    }

    public function with(): array
    {
        $note = Note::with('backlinks')->findOrFail($this->noteId);

        return [
            'html' => app(MarkdownService::class)->toHtml($this->content ?? ''),
            'backlinks' => $note->backlinks()->visibleTo(auth()->id())->get(),
        ];
    }
};
?>

<div x-data="{ mode: 'edit', editor: null }"
     x-effect="if (mode === 'edit' && editor) editor.refresh()">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-4">
        <flux:input wire:model="title" class="max-w-md" placeholder="{{ __('Title') }}" />

        <div class="flex items-center gap-2">
            @if ($isOwner)
                <flux:select wire:model="visibility" size="sm" class="max-w-40">
                    <flux:select.option value="public">{{ __('Public (wiki)') }}</flux:select.option>
                    <flux:select.option value="private">{{ __('Private') }}</flux:select.option>
                </flux:select>
            @endif
            <flux:button size="sm" variant="ghost" x-on:click="mode = 'edit'">{{ __('Edit') }}</flux:button>
            <flux:button size="sm" variant="ghost"
                         x-on:click="$wire.set('content', (editor && editor.value() !== null) ? editor.value() : @js($content)).then(() => mode = 'view')">
                {{ __('View') }}
            </flux:button>
            <flux:button size="sm" variant="primary" color="red" icon="check" wire:click="save">
                {{ __('Save') }}
            </flux:button>
            @if ($isOwner)
                <flux:button size="sm" variant="ghost" icon="trash"
                             wire:click="delete"
                             wire:confirm="{{ __('Delete this page?') }}" />
            @endif
        </div>
    </div>

    {{-- Markdown editor (EasyMDE). Construction lives in app.js (createNoteEditor)
         and is deferred a frame to avoid the CodeMirror measure crash during the
         wire:navigate SPA swap. --}}
    <div x-show="mode === 'edit'" wire:ignore
         x-init="editor = window.createNoteEditor($refs.ta, $wire)">
        <textarea x-ref="ta">{{ $content }}</textarea>
    </div>

    {{-- Rendered view --}}
    <div x-show="mode === 'view'" x-cloak
         class="prose dark:prose-invert max-w-none rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
        {!! $html !!}
    </div>

    {{-- Backlinks / linked references --}}
    <div class="mt-8">
        <flux:heading size="lg" class="mb-2">{{ __('References') }}</flux:heading>

        @forelse($backlinks as $backlink)
            <div class="py-1">
                <flux:link :href="route($backlink->visibility === 'public' ? 'wiki.show' : 'notes.show', $backlink)" wire:navigate>
                    {{ $backlink->title }}
                </flux:link>
            </div>
        @empty
            <flux:text>{{ __('No pages link here yet.') }}</flux:text>
        @endforelse
    </div>
</div>
