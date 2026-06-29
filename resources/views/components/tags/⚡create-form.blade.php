<?php

use Livewire\Component;
use App\Services\TagService;

new class extends Component
{
    public $name;
    public $description;
    public $color = "#0047AB";

    public function createTag(TagService $tagService)
    {
        $data = $this->validate([
            "name"=> "required|string",
            "description"=> "nullable|string",
            "color"=> "nullable|hex_color"
        ]);
        $tagService->createTag(auth()->user(), $data);

        $this->reset(['name', 'description']);
        $this->color = "#0047AB";

        $this->dispatch('tag-created');
    }
};
?>

<div>
    {{-- Because you are alive, everything is possible. - Thich Nhat Hanh --}}

    <flux:field>
        <flux:error name="name"></flux:error>   
        <flux:input label="Name" wire:model="name" />
    </flux:field>
    <flux:field>
        <flux:error name="description"></flux:error>   
        <flux:input label="Description" wire:model="description" />
    </flux:field>
    <flux:field>
        <flux:error name="color"></flux:error>   
        <flux:input type="color" label="Color" wire:model="color" />
    </flux:field>
    <flux:button wire:click="createTag()" variant="primary" color="red" icon="plus">
        {{__('Create')}}
    </flux:button>
</div>