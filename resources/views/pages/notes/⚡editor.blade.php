<?php

use Livewire\Component;
use App\Models\Note;
use App\Services\NoteService;
use App\Services\MarkdownService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    public int $noteId;
    public string $title = '';
    public ?string $content = '';

    public function mount(Note $note): void
    {
        abort_unless($note->user_id === auth()->id(), 403);

        $this->noteId  = $note->id;
        $this->title   = $note->title;
        $this->content = $note->content ?? '';
    }

    public function save(NoteService $service): void
    {
        $note = Note::findOrFail($this->noteId);

        $service->updateNote($note, [
            'title'   => $this->title,
            'content' => $this->content,
        ]);

        LivewireAlert::title(__('Note saved'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(2500)
            ->show();
    }

    public function delete(NoteService $service)
    {
        $service->deleteNote(Note::findOrFail($this->noteId));

        return $this->redirect(route('notes'), navigate: true);
    }

    public function with(): array
    {
        $note = Note::with('backlinks')->findOrFail($this->noteId);

        return [
            'html'      => app(MarkdownService::class)->toHtml($this->content ?? ''),
            'backlinks' => $note->backlinks,
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
            <flux:button size="sm" variant="ghost" x-on:click="mode = 'edit'">{{ __('Edit') }}</flux:button>
            <flux:button size="sm" variant="ghost"
                         x-on:click="$wire.set('content', (editor && editor.value() !== null) ? editor.value() : @js($content)).then(() => mode = 'view')">
                {{ __('View') }}
            </flux:button>
            <flux:button size="sm" variant="primary" color="red" icon="check" wire:click="save">
                {{ __('Save') }}
            </flux:button>
            <flux:button size="sm" variant="ghost" icon="trash"
                         wire:click="delete"
                         wire:confirm="{{ __('Delete this note?') }}" />
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
                <flux:link :href="route('notes.show', $backlink)" wire:navigate>
                    {{ $backlink->title }}
                </flux:link>
            </div>
        @empty
            <flux:text>{{ __('No notes link here yet.') }}</flux:text>
        @endforelse
    </div>
</div>
