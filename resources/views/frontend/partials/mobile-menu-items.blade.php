@foreach($items as $item)
    <div
        class="border-b border-slate-100 py-1"
        x-data="{ open: false }"
    >
        <div class="flex items-center justify-between gap-3">
            <a
                href="{{ $item->resolved_url }}"
                target="{{ $item->resolved_target }}"
                rel="{{ $item->rel }}"
                class="flex-1 py-3 text-base font-semibold text-slate-800"
            >
                {{ $item->resolved_label }}
            </a>

            @if($item->has_children)
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-50"
                    @click="open = !open"
                    aria-label="{{ __('ui.nav.open_menu') }}"
                >
                    <svg
                        class="h-4 w-4 transition"
                        :class="{ 'rotate-180': open }"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif
        </div>

        @if($item->has_children)
            <div
                x-cloak
                x-show="open"
                x-collapse
                class="ml-4 border-l border-slate-100 pl-4"
            >
                @include('frontend.components.mobile-menu-items', [
                    'items' => $item->childrenRecursive,
                    'level' => $level + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
