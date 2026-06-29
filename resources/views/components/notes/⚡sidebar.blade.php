<?php

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
        return ['notes' => auth()->user()->notes];
    }
};
?>

<div>
    {{-- Do what you can, with what you have, where you are. - Theodore Roosevelt --}}

    <flux:sidebar.group :heading="__('Notes')" class="grid">
        
        <flux:button wire:click="create" icon="plus" variant="subtle"   align="start" :href="route('notes')" :current="request()->routeIs('notes')" wire:navigate>
            {{__('New')}}
        </flux:button>

        

        @foreach($notes as $note)
        <flux:button variant="ghost" align="start" icon:trailing="arrow-top-right-on-square" :href="route('notes.show', $note)" wire:navigate>
            {{ $note->title }}
        </flux:button>
        @endforeach

    </flux:sidebar.group>
</div>