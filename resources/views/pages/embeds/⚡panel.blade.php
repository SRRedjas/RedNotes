<?php

use App\Models\Embed;
use App\Services\EmbedService;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    public string $title = '';

    public function create(EmbedService $service)
    {
        abort_unless(auth()->check(), 403);

        $embed = $service->createEmbed(auth()->user(), [
            'title' => $this->title,
            'html' => '',
        ]);

        return $this->redirect(route('embeds.show', $embed), navigate: true);
    }

    public function with()
    {
        return [
            'embeds' => Embed::with('user')
                ->matching($this->search)
                ->latest('updated_at')
                ->get(),
        ];
    }
};
?>

<div>
    <div class="flex items-center justify-between mb-6 gap-4">
        <flux:heading size="xl">{{ __('Embeds') }}</flux:heading>
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" class="max-w-xs" placeholder="{{ __('Search embeds...') }}" />
    </div>

    @auth
        <form wire:submit="create" class="flex items-start gap-2 mb-6">
            <flux:field class="max-w-md flex-1">
                <flux:input wire:model="title" placeholder="{{ __('Title of the new embed') }}" />
                <flux:error name="title" />
            </flux:field>
            <flux:button type="submit" icon="plus" variant="primary" color="red">
                {{ __('New embed') }}
            </flux:button>
        </form>
    @endauth

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Title') }}</flux:table.column>
            <flux:table.column>{{ __('Author') }}</flux:table.column>
            <flux:table.column>{{ __('Use in a note') }}</flux:table.column>
            <flux:table.column>{{ __('Updated') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($embeds as $embed)
                <flux:table.row>
                    <flux:table.cell>
                        <flux:link :href="route('embeds.show', $embed)" wire:navigate>
                            {{ $embed->title }}
                        </flux:link>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $embed->user->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <code class="text-xs">&#123;&#123;embed:{{ $embed->slug }}&#125;&#125;</code>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $embed->updated_at?->diffForHumans() }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <flux:text>{{ __('No embeds yet. Create the first one!') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
