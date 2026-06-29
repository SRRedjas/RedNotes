<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tag;
use App\Services\TagService;
use Flux\Flux;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    public function with()
    {
        return [
            'tags' => auth()->user()->tags,
        ];
    }

    #[On('tag-created')]
    public function onTagCreated(): void
    {
        Flux::modal('create-form')->close();

        LivewireAlert::title(__('Tag created'))
            ->text(__('Your tag was created successfully.'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    #[On('tag-updated')]
    public function onTagUpdated(): void
    {
        LivewireAlert::title(__('Tag updated'))
            ->text(__('Your tag was updated successfully.'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function deleteTag(int $tagId, TagService $tagService): void
    {
        $tagService->deleteTag(Tag::findOrFail($tagId));

        LivewireAlert::title(__('Tag deleted'))
            ->text(__('Your tag was deleted successfully.'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }
};
?>

<div>
    {{-- It is not the man who has too little, but the man who craves more, that is poor. - Seneca --}}
    <flux:modal name="create-form" flyout>
        <livewire:tags.create-form/>
    </flux:modal>

    <flux:modal.trigger name="create-form">
        <flux:button variant="primary" color="red" icon="plus">
            {{__('New')}}
        </flux:button>
    </flux:modal.trigger>


    <flux:table>
        <flux:table.columns>
            <flux:table.column>
                {{__('Name')}}
            </flux:table.column>
            <flux:table.column>
                {{__('Description')}}
            </flux:table.column>
            <flux:table.column>
                {{__('Color')}}
            </flux:table.column>
            <flux:table.column>
                {{__('Actions')}}
            </flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach($tags as $tag)
            <flux:table.row :key="$tag->id">
                <flux:table.cell>
                    {{$tag->name}}
                </flux:table.cell>
                <flux:table.cell>
                    {{$tag->description}}
                </flux:table.cell>
                <flux:table.cell>
                    <flux:button variant="primary" style="background-color: {{$tag->color}};"></flux:button>
                </flux:table.cell>
                <flux:table.cell>
                    <div class="flex items-center gap-2">
                        <flux:modal :name="'edit-form-' . $tag->id" flyout>
                            <livewire:tags.edit-form :tag="$tag" :key="'edit-' . $tag->id" />
                        </flux:modal>

                        <flux:modal.trigger :name="'edit-form-' . $tag->id">
                            <flux:button size="sm" variant="ghost" icon="pencil-square" />
                        </flux:modal.trigger>

                        <flux:button size="sm" variant="ghost" icon="trash"
                                     wire:click="deleteTag({{ $tag->id }})"
                                     wire:confirm="{{ __('Delete this tag?') }}" />
                    </div>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

</div>