@props(['embed'])
<iframe data-embed src="{{ route('embeds.raw', $embed) }}?v={{ $embed->updated_at?->timestamp }}" title="{{ $embed->title }}" sandbox="{{ \App\Services\EmbedService::SANDBOX }}" loading="lazy" {{ $attributes->class('w-full h-[480px] rounded-lg border border-zinc-200 bg-white dark:border-zinc-700') }}></iframe>
