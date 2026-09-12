<?php

use App\Models\Note;
use App\Services\NoteService;
use Livewire\Component;

new class extends Component
{
    public function create(NoteService $service)
    {
        $note = $service->createNote(auth()->user(), [
            'title' => __('Untitled'),
            'content' => '',
            'visibility' => 'public',
        ]);

        return $this->redirect(route('wiki.show', $note), navigate: true);
    }

    public function with()
    {
        return ['tree' => Note::tree(Note::where('visibility', 'public')->orderBy('title')->get())];
    }
};
?>

<div>
    <flux:sidebar.group :heading="__('Wiki')" class="grid">

        <flux:button wire:click="create" icon="plus" variant="subtle" align="start" :href="route('wiki')" :current="request()->routeIs('wiki')" wire:navigate>
            {{ __('New') }}
        </flux:button>

        <x-notes.tree :nodes="$tree" route="wiki.show" />

    </flux:sidebar.group>
</div>
