<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white text-neutral-900 antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900 dark:text-neutral-100">

    {{-- Top navigation --}}
    <header class="sticky top-0 z-30 border-b border-neutral-200 bg-white/80 backdrop-blur dark:border-neutral-800 dark:bg-neutral-950/80">
        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
            <x-app-logo href="{{ route('home') }}" wire:navigate />

            <div class="hidden items-center gap-1 sm:flex">
                <flux:button variant="ghost" size="sm" :href="route('wiki')" icon="book-open-text" wire:navigate>{{ __('Wiki') }}</flux:button>
                <flux:button variant="ghost" size="sm" :href="route('embeds')" icon="code-bracket" wire:navigate>{{ __('Embeds') }}</flux:button>
                <flux:button variant="ghost" size="sm" href="#features">{{ __('Features') }}</flux:button>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <flux:button variant="primary" size="sm" :href="route('dashboard')" icon:trailing="arrow-right" wire:navigate>{{ __('Dashboard') }}</flux:button>
                @else
                    <flux:button variant="ghost" size="sm" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                    @if (Route::has('register'))
                        <flux:button variant="primary" size="sm" :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:button>
                    @endif
                @endauth
            </div>
        </nav>

        {{-- Secondary links on small screens --}}
        <div class="flex items-center gap-1 border-t border-neutral-200 px-4 py-2 sm:hidden dark:border-neutral-800">
            <flux:button variant="ghost" size="sm" :href="route('wiki')" icon="book-open-text" wire:navigate>{{ __('Wiki') }}</flux:button>
            <flux:button variant="ghost" size="sm" :href="route('embeds')" icon="code-bracket" wire:navigate>{{ __('Embeds') }}</flux:button>
            <flux:button variant="ghost" size="sm" href="#features">{{ __('Features') }}</flux:button>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
            <div class="flex flex-col gap-6">
                <flux:badge color="red" size="sm" class="w-fit">{{ __('Markdown notes & public wiki') }}</flux:badge>
                <h1 class="text-4xl font-bold leading-tight tracking-tight sm:text-5xl">
                    {{ __('Your notes anywhere, group them, reference them.') }}
                </h1>
                <p class="max-w-prose text-lg text-neutral-600 dark:text-neutral-400">
                    {{ __('Take notes, improve your productivity. Write in Markdown, organize with tags, link notes together and share what you want in a public wiki.') }}
                </p>
                <div class="flex flex-wrap gap-3">
                    @auth
                        <flux:button variant="primary" :href="route('notes')" icon="document-text" wire:navigate>{{ __('Go to my notes') }}</flux:button>
                    @else
                        <flux:button variant="primary" :href="route('register')" icon:trailing="arrow-right" wire:navigate>{{ __('Get Started') }}</flux:button>
                    @endauth
                    <flux:button variant="ghost" :href="route('wiki')" icon="book-open-text" wire:navigate>{{ __('Browse the wiki') }}</flux:button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-neutral-200 shadow-lg dark:border-neutral-800">
                <img src="{{ asset('preview.png') }}" alt="{{ __('RedNotes preview') }}" class="w-full" loading="lazy">
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="scroll-mt-20 border-t border-neutral-200 py-16 dark:border-neutral-800">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="text-2xl font-bold sm:text-3xl">{{ __('Everything you need to think in writing') }}</h2>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">{{ __('Simple tools that stay out of your way.') }}</p>

                @php
                    $features = [
                        ['icon' => 'pencil-square', 'title' => __('With markdown'), 'text' => __('Write with a live Markdown editor: headings, lists, code and more.')],
                        ['icon' => 'tag', 'title' => __('Tags'), 'text' => __('Group your notes with colored tags and find them in seconds.')],
                        ['icon' => 'link', 'title' => __('Linked notes'), 'text' => __('Reference other notes with wikilinks and see their backlinks.')],
                        ['icon' => 'folder', 'title' => __('Folders by title'), 'text' => __('Use "folder/note" titles to build a navigable tree.')],
                        ['icon' => 'globe-alt', 'title' => __('Public wiki'), 'text' => __('Make a note public and it becomes a wiki page anyone can read.')],
                        ['icon' => 'code-bracket', 'title' => __('HTML embeds'), 'text' => __('Create sandboxed HTML/JS pages and embed them in your notes.')],
                    ];
                @endphp

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($features as $feature)
                        <div class="flex flex-col gap-3 rounded-xl border border-neutral-200 bg-white p-5 dark:border-neutral-800 dark:bg-zinc-900">
                            <span class="flex size-10 items-center justify-center rounded-lg bg-red-500/10 text-red-500">
                                <flux:icon :name="$feature['icon']" class="size-5" />
                            </span>
                            <flux:heading size="lg">{{ $feature['title'] }}</flux:heading>
                            <flux:text>{{ $feature['text'] }}</flux:text>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Latest public wiki pages --}}
        <section class="border-t border-neutral-200 py-16 dark:border-neutral-800">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold sm:text-3xl">{{ __('Latest from the wiki') }}</h2>
                        <p class="mt-2 text-neutral-600 dark:text-neutral-400">{{ __('Public pages shared by the community.') }}</p>
                    </div>
                    <flux:button variant="ghost" :href="route('wiki')" icon:trailing="arrow-right" wire:navigate>{{ __('See all') }}</flux:button>
                </div>

                @if ($wikiNotes->isEmpty())
                    <div class="mt-8 flex flex-col items-center gap-3 rounded-xl border border-dashed border-neutral-300 py-12 dark:border-neutral-700">
                        <flux:icon.book-open-text class="size-10 text-neutral-400" />
                        <flux:text>{{ __('No public pages yet.') }}</flux:text>
                    </div>
                @else
                    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($wikiNotes as $note)
                            <a href="{{ route('wiki.show', $note) }}" wire:navigate
                                class="group flex flex-col gap-2 rounded-xl border border-neutral-200 bg-white p-4 transition hover:border-red-400 hover:shadow-sm dark:border-neutral-800 dark:bg-zinc-900 dark:hover:border-red-500">
                                <div class="flex items-start justify-between gap-2">
                                    <flux:heading size="base" class="line-clamp-1 group-hover:text-red-500">
                                        {{ $note->title ?: __('Untitled') }}
                                    </flux:heading>
                                    <flux:icon.arrow-right class="size-4 shrink-0 text-neutral-400 opacity-0 transition group-hover:opacity-100" />
                                </div>

                                @if ($note->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($note->tags as $tag)
                                            <flux:badge size="sm" style="background-color: {{ $tag->color }}; color: #fff;">
                                                {{ $tag->name }}
                                            </flux:badge>
                                        @endforeach
                                    </div>
                                @endif

                                <flux:text class="mt-auto text-xs">
                                    {{ $note->user->name }} · {{ $note->updated_at?->diffForHumans() }}
                                </flux:text>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Call to action --}}
        @guest
            <section class="border-t border-neutral-200 py-16 dark:border-neutral-800">
                <div class="mx-auto flex max-w-3xl flex-col items-center gap-4 px-4 text-center sm:px-6">
                    <h2 class="text-2xl font-bold sm:text-3xl">{{ __('Start writing today') }}</h2>
                    <p class="text-neutral-600 dark:text-neutral-400">{{ __('Create a free account and keep all your notes in one place.') }}</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <flux:button variant="primary" :href="route('register')" icon:trailing="arrow-right" wire:navigate>{{ __('Get Started') }}</flux:button>
                        <flux:button variant="ghost" :href="route('login')" wire:navigate>{{ __('I already have an account') }}</flux:button>
                    </div>
                </div>
            </section>
        @endguest
    </main>

    <footer class="border-t border-neutral-200 py-8 dark:border-neutral-800">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 text-sm text-neutral-500 sm:flex-row sm:px-6">
            <span>© {{ date('Y') }} {{ config('app.name', 'RedNotes') }}</span>
            <div class="flex gap-4">
                <a href="{{ route('wiki') }}" class="hover:text-neutral-900 dark:hover:text-white" wire:navigate>{{ __('Wiki') }}</a>
                <a href="{{ route('embeds') }}" class="hover:text-neutral-900 dark:hover:text-white" wire:navigate>{{ __('Embeds') }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-neutral-900 dark:hover:text-white" wire:navigate>{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-neutral-900 dark:hover:text-white" wire:navigate>{{ __('Log in') }}</a>
                @endauth
            </div>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
