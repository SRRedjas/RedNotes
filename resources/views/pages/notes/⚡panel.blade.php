<?php

use Livewire\Component;
use App\Services\NoteService;

new class extends Component
{
    public function with()
    {
        return [
            'notes' => auth()->user()->notes()->with('tags')->latest('updated_at')->get(),
        ];
    }

};
?>

<div>
    {{-- An unexamined life is not worth living. - Socrates --}}

    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('Notes') }}</flux:heading>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Title') }}</flux:table.column>
            <flux:table.column>{{ __('Tags') }}</flux:table.column>
            <flux:table.column>{{ __('Updated') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($notes as $note)
                <flux:table.row>
                    <flux:table.cell>
                        <flux:link :href="route('notes.show', $note)" wire:navigate>
                            {{ $note->title }}
                        </flux:link>
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
                    <flux:table.cell colspan="3">
                        <flux:text>{{ __('No notes yet. Create your first one!') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
