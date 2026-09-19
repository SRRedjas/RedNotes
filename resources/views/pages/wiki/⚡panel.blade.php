<?php

use App\Models\Note;
use App\Services\NoteService;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public function create(NoteService $service)
    {
        abort_unless(auth()->check(), 403);

        $note = $service->createNote(auth()->user(), [
            'title' => __('Untitled'),
            'content' => '',
            'visibility' => 'public',
        ]);

        return $this->redirect(route('wiki.show', $note), navigate: true);
    }

    public function with()
    {
        return [
            'notes' => Note::with('tags', 'user')
                ->where('visibility', 'public')
                ->matching($this->search)
                ->latest('updated_at')
                ->get(),
        ];
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6 gap-4">
        <flux:heading size="xl">{{ __('Wiki') }}</flux:heading>
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" class="max-w-xs" placeholder="{{ __('Search wiki...') }}" />
        @auth
            <flux:button wire:click="create" icon="plus" variant="primary" color="red">
                {{ __('New public page') }}
            </flux:button>
        @endauth
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Title') }}</flux:table.column>
            <flux:table.column>{{ __('Author') }}</flux:table.column>
            <flux:table.column>{{ __('Tags') }}</flux:table.column>
            <flux:table.column>{{ __('Updated') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($notes as $note)
                <flux:table.row>
                    <flux:table.cell>
                        <flux:link :href="route('wiki.show', $note)" wire:navigate>
                            {{ $note->title }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $note->user->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1">
                            @foreach($note->tags as $tag)
                                <flux:badge size="sm" style="background-color: {{ $tag->color }}; color: #fff;">
                                    {{ $tag->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $note->updated_at?->diffForHumans() }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <flux:text>{{ __('No public pages yet. Create the first one!') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
