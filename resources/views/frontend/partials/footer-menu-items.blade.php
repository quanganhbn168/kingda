@php
    $nested = $nested ?? false;
@endphp

<ul class="{{ $nested ? 'mt-2 space-y-2 border-l border-white/10 pl-3' : 'space-y-2' }}">
    @foreach($items as $item)
        <li>
            <a
                href="{{ $item->resolved_url ?: '#' }}"
                target="{{ $item->resolved_target }}"
                @if(filled($item->rel)) rel="{{ $item->rel }}" @endif
                class="transition hover:text-white"
            >
                {{ $item->resolved_label }}
            </a>

            @if($item->relationLoaded('activeChildrenRecursive') && $item->activeChildrenRecursive->isNotEmpty())
                @include('frontend.partials.footer-menu-items', [
                    'items' => $item->activeChildrenRecursive,
                    'nested' => true,
                ])
            @endif
        </li>
    @endforeach
</ul>
