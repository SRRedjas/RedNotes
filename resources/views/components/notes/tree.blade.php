@props(['nodes' => [], 'route', 'nested' => false])

<ul @class(['ms-3 ps-2 border-s border-zinc-200 dark:border-zinc-700' => $nested])>
    @foreach($nodes as $node)
        <li>
            @if($node['note'])
                <flux:button variant="ghost" size="sm" align="start" icon:trailing="arrow-top-right-on-square"
                             :href="route($route, $node['note'])" wire:navigate>
                    {{ $node['label'] }}
                </flux:button>
            @else
                <flux:text class="block px-2 py-1 text-xs uppercase tracking-wide text-zinc-500">
                    {{ $node['label'] }}
                </flux:text>
            @endif

            @if(!empty($node['children']))
                <x-notes.tree :nodes="$node['children']" :route="$route" nested />
            @endif
        </li>
    @endforeach
</ul>
