<?php

use App\Models\Note;
use App\Services\NoteService;
use Livewire\Component;


new class extends Component
{


    public function create(NoteService $service)
    {
        $note = $service->createNote(auth()->user(), [
            'title'   => __('Untitled'),
            'content' => '',
        ]);

        return $this->redirect(route('notes.show', $note), navigate: true);
    }


    public function with()
    {
        return ['tree' => Note::tree(auth()->user()->notes()->orderBy('title')->get())];
    }
};
?>

<div>
    {{-- Do what you can, with what you have, where you are. - Theodore Roosevelt --}}

    <flux:sidebar.group :heading="__('Notes')" class="grid">

        <flux:button wire:click="create" icon="plus" variant="subtle"   align="start" :href="route('notes')" :current="request()->routeIs('notes')" wire:navigate>
            {{__('New')}}
        </flux:button>

        <x-notes.tree :nodes="$tree" route="notes.show" />

    </flux:sidebar.group>
</div>