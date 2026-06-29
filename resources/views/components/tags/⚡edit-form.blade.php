<?php

use Livewire\Component;
use App\Models\Tag;
use App\Services\TagService;
use Flux\Flux;

new class extends Component
{
    public int $tagId;
    public $name;
    public $description;
    public $color;

    public function mount(Tag $tag)
    {
        abort_unless($tag->user_id === auth()->id(), 403);

        $this->tagId       = $tag->id;
        $this->name        = $tag->name;
        $this->description = $tag->description;
        $this->color       = $tag->color;
    }

    public function updateTag(TagService $tagService)
    {
        $data = $this->validate([
            "name"        => "required|string",
            "description" => "nullable|string",
            "color"       => "nullable|hex_color",
        ]);

        $tagService->updateTag(Tag::findOrFail($this->tagId), $data);

        Flux::modal('edit-form-' . $this->tagId)->close();

        $this->dispatch('tag-updated');
    }
};
?>

<div>
    {{-- The secret of change is to focus all of your energy not on fighting the old, but on building the new. - Socrates --}}

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
    <flux:button wire:click="updateTag()" variant="primary" color="red" icon="check">
        {{__('Save')}}
    </flux:button>
</div>
