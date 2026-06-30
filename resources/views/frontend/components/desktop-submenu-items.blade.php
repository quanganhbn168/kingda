@foreach($items as $child)
    @php($isChildActive = $isItemActive($child))

    <div
        class="relative"
        x-data="{ submenuOpen: false }"
        @mouseenter="submenuOpen = true"
        @mouseleave="submenuOpen = false"
    >
        <a
            href="{{ $child->resolved_url }}"
            target="{{ $child->resolved_target }}"
            rel="{{ $child->rel }}"
            class="flex items-center justify-between rounded px-4 py-3 text-sm font-semibold transition hover:bg-red-50 hover:text-primary {{ $isChildActive ? 'bg-red-50 text-primary' : 'text-slate-700' }}"
            @if($isChildActive) aria-current="page" @endif
        >
            <span>{{ $child->resolved_label }}</span>

            @if($child->has_children)
                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
            @endif
        </a>

        @if($child->has_children)
            <div
                x-cloak
                x-show="submenuOpen"
                x-transition.opacity.duration.150ms
                class="absolute left-full top-0 z-50 min-w-72 rounded border border-slate-200 bg-white p-2 shadow-xl"
            >
                @include('frontend.components.desktop-submenu-items', [
                    'items' => $child->childrenRecursive,
                    'isItemActive' => $isItemActive,
                ])
            </div>
        @endif
    </div>
@endforeach
