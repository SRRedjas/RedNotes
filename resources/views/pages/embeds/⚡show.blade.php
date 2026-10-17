<?php

use App\Models\Embed;
use App\Services\EmbedService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

new class extends Component
{
    public int $embedId;

    public string $title = '';

    public ?string $html = '';

    public bool $canEdit = false;

    public function mount(string $slug, EmbedService $service): void
    {
        $embed = Embed::where('slug', $slug)->firstOrFail();

        $this->embedId = $embed->id;
        $this->title = $embed->title;
        $this->canEdit = $service->canEdit($embed);

        // The source only travels to the browser for whoever can edit it;
        // everyone else just gets the iframe.
        if ($this->canEdit) {
            $this->html = $embed->html ?? '';
        }
    }

    public function save(EmbedService $service): void
    {
        $saved = $service->updateEmbed(Embed::findOrFail($this->embedId), [
            'title' => $this->title,
            'html' => $this->html,
        ]);

        abort_unless($saved, 403);

        LivewireAlert::title(__('Embed saved'))
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(2500)
            ->show();
    }

    public function delete(EmbedService $service)
    {
        abort_unless($service->deleteEmbed(Embed::findOrFail($this->embedId)), 403);

        return $this->redirect(route('embeds'), navigate: true);
    }

    public function with(): array
    {
        return [
            'embed' => Embed::findOrFail($this->embedId),
        ];
    }
};
?>

<div x-data="{ mode: @js($canEdit && blank($html) ? 'edit' : 'view') }">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-4">
        @if ($canEdit)
            <flux:input wire:model="title" class="max-w-md" placeholder="{{ __('Title') }}" />

            <div class="flex items-center gap-2">
                <flux:button size="sm" variant="ghost" x-on:click="mode = 'edit'">{{ __('Edit') }}</flux:button>
                <flux:button size="sm" variant="ghost" x-on:click="mode = 'view'">{{ __('View') }}</flux:button>
                <flux:button size="sm" variant="primary" color="red" icon="check" wire:click="save">
                    {{ __('Save') }}
                </flux:button>
                <flux:button size="sm" variant="ghost" icon="trash"
                             wire:click="delete"
                             wire:confirm="{{ __('Delete this embed?') }}" />
            </div>
        @else
            <flux:heading size="xl">{{ $title }}</flux:heading>
        @endif
    </div>

    <flux:error name="title" />
    <flux:error name="html" />

    @if ($canEdit)
        {{-- Source editor --}}
        <div x-show="mode === 'edit'" x-cloak class="space-y-2">
            <div class="flex items-center gap-2">
                <flux:button size="sm" icon="arrow-up-tray" x-on:click="$refs.file.click()">
                    {{ __('Import .html file') }}
                </flux:button>
                <flux:text>{{ __('or paste a complete HTML page (CSS and JavaScript included) below.') }}</flux:text>
                <input type="file" x-ref="file" accept=".html,.htm,text/html" class="hidden"
                       x-on:change="$event.target.files[0]?.text().then(text => $wire.set('html', text)); $event.target.value = ''">
            </div>

            <flux:textarea wire:model="html" rows="24" class="font-mono text-sm" spellcheck="false" />
        </div>
    @endif

    {{-- Embedded page. The key changes on save so the iframe reloads. --}}
    <div x-show="mode === 'view'" @if ($canEdit) x-cloak @endif>
        <x-embed-frame :embed="$embed" wire:key="embed-frame-{{ $embed->updated_at?->timestamp }}" />
    </div>

    <flux:text class="mt-4">
        {{ __('Embed it in any note or wiki page with:') }}
        <code>&#123;&#123;embed:{{ $embed->slug }}&#125;&#125;</code>
    </flux:text>
</div>
