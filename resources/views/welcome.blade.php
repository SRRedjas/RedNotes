<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-2">
        <div class="bg-muted relative hidden flex-col p-10 text-white lg:flex  dark:border-e dark:border-neutral-800 gap-12">
            <div class="absolute inset-0 bg-neutral-900"></div>
            <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                <span class="flex h-10 w-10 items-center justify-center rounded-md">
                    <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                </span>

                {{ config('app.name', 'Laravel') }}



            </a>
            <div class="relative z-20 mt-auto">
                <img src="preview.png" alt="preview">
                {{__('Take notes, improve your productivity')}}
            </div>
        </div>
        <div class="w-full ">
            <div class="mx-auto flex w-full flex-col justify-between space-y-6 sm:w-[350px]">
                <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                    <span class="flex h-9 w-9 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                <p>
                    {{__('Your notes anywhere, group them, reference them.')}}
                </p>
                <h1 class="font-bold text-2xl">{{__('Notes')}}</h1>
                <flux:skeleton />
                <flux:skeleton animate="shimmer" />
                <flux:skeleton animate="pulse" />
                <flux:skeleton />
                <flux:skeleton animate="shimmer" />
                <flux:skeleton animate="pulse" />
                <flux:skeleton />
                <flux:skeleton animate="shimmer" />
                <flux:skeleton animate="pulse" />

                <p>
                    {{__('With markdown')}}
                </p>

                <flux:button variant="ghost" href="/register" icon:trailing="arrow-right">{{__('Get Started')}}</flux:button>
            </div>
        </div>
    </div>


    @fluxScripts
</body>

</html>