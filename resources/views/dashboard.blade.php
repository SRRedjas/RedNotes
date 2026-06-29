<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4">

        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Recent Notes') }}</flux:heading>
            <flux:button :href="route('notes')" icon="document-text" variant="ghost" wire:navigate>
                {{ __('All notes') }}
            </flux:button>
        </div>

        @if($recentNotes->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-neutral-200 py-20 dark:border-neutral-700">
            <flux:icon.document-text class="size-12 text-neutral-400" />
            <flux:text class="text-center text-neutral-500">{{ __('No notes yet. Create your first one!') }}</flux:text>
            <flux:button :href="route('notes')" variant="primary" wire:navigate>
                {{ __('Create note') }}
            </flux:button>
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($recentNotes as $note)
            <a href="{{ route('notes.show', $note) }}" wire:navigate
                class="group flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-4 transition hover:border-blue-400 hover:shadow-sm dark:border-neutral-700 dark:bg-zinc-900 dark:hover:border-blue-500">

                <flux:card class="flex items-start justify-between gap-2">
                    <flux:heading size="base" class="line-clamp-1 group-hover:text-blue-500 dark:group-hover:text-blue-400">
                        {{ $note->title ?: __('Untitled') }}
                    </flux:heading>
                    <flux:icon.arrow-right class="size-4 shrink-0 text-neutral-400 opacity-0 transition group-hover:opacity-100" />
                </flux:card>

                @if($note->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1">
                    @foreach($note->tags as $tag)
                    <flux:badge size="sm" style="background-color: {{ $tag->color }}; color: #fff;">
                        {{ $tag->name }}
                    </flux:badge>
                    @endforeach
                </div>
                @endif

                <flux:text class="mt-auto text-xs text-neutral-400">
                    {{ $note->updated_at?->diffForHumans() }}
                </flux:text>
            </a>
            @endforeach
        </div>
        @endif

    </div>
</x-layouts::app>