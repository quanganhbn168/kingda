@php
    $normalizeMenuPath = function (?string $url): string {
        $path = trim(parse_url($url ?: '/', PHP_URL_PATH) ?: '/', '/');

        return preg_replace('#/+#', '/', $path) ?: '';
    };

    $stripLocalePrefix = function (string $path): string {
        $segments = $path === '' ? [] : explode('/', $path);

        if (isset($segments[0]) && in_array($segments[0], ['vi', 'en', 'zh'], true)) {
            array_shift($segments);
        }

        return implode('/', $segments);
    };

    $currentPath = $normalizeMenuPath(request()->path());
    $currentPathWithoutLocale = $stripLocalePrefix($currentPath);

    $isItemActive = function ($item) use (&$isItemActive, $normalizeMenuPath, $stripLocalePrefix, $currentPath, $currentPathWithoutLocale): bool {
        $path = $normalizeMenuPath($item->resolved_url);
        $pathWithoutLocale = $stripLocalePrefix($path);

        if ($path === '') {
            return $currentPath === '' || $currentPathWithoutLocale === '';
        }

        if ($pathWithoutLocale === '') {
            return $currentPath === $path;
        }

        if ($currentPath === $path || str_starts_with($currentPath . '/', $path . '/')) {
            return true;
        }

        if ($pathWithoutLocale !== '' && ($currentPathWithoutLocale === $pathWithoutLocale || str_starts_with($currentPathWithoutLocale . '/', $pathWithoutLocale . '/'))) {
            return true;
        }

        if ($item->has_children) {
            return $item->childrenRecursive->contains(fn ($child): bool => $isItemActive($child));
        }

        return false;
    };
@endphp

@foreach($items as $item)
    @php($isActive = $isItemActive($item))

    <div class="group/menu relative flex h-full items-center">
        <a
            href="{{ $item->resolved_url }}"
            target="{{ $item->resolved_target }}"
            rel="{{ $item->rel }}"
            class="relative inline-flex items-center gap-1 px-2.5 py-2 text-sm font-bold transition after:absolute after:-bottom-0.5 after:left-2.5 after:right-2.5 after:h-0.5 after:origin-left after:rounded-full after:bg-primary after:transition-transform hover:text-primary hover:after:scale-x-100 {{ $isActive ? 'text-primary after:scale-x-100' : 'text-slate-800 after:scale-x-0' }}"
            @if($isActive) aria-current="page" @endif
        >
            <span>{{ $item->resolved_label }}</span>

            @if($item->has_children)
                <svg class="h-4 w-4 transition group-hover/menu:rotate-180" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            @endif
        </a>

        @if($item->has_children)
            <div class="invisible absolute left-0 top-full z-50 min-w-64 translate-y-2 rounded border border-slate-200 bg-white p-2 opacity-0 shadow-xl transition group-hover/menu:visible group-hover/menu:translate-y-0 group-hover/menu:opacity-100">
                @foreach($item->childrenRecursive as $child)
                    @php($isChildActive = $isItemActive($child))

                    <div class="group/submenu relative">
                        <a
                            href="{{ $child->resolved_url }}"
                            target="{{ $child->resolved_target }}"
                            rel="{{ $child->rel }}"
                            class="flex items-center justify-between rounded px-4 py-3 text-sm font-semibold transition hover:bg-red-50 hover:text-primary {{ $isChildActive ? 'bg-red-50 text-primary' : 'text-slate-700' }}"
                            @if($isChildActive) aria-current="page" @endif
                        >
                            <span>{{ $child->resolved_label }}</span>

                            @if($child->has_children)
                                <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-hover/submenu:translate-x-0.5 group-hover/submenu:text-primary" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.17 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </a>

                        @if($child->has_children)
                            <div class="invisible absolute left-full top-0 z-50 min-w-72 -translate-x-2 rounded border border-slate-200 bg-white p-2 opacity-0 shadow-xl transition group-hover/submenu:visible group-hover/submenu:translate-x-0 group-hover/submenu:opacity-100">
                                @foreach($child->childrenRecursive as $grandChild)
                                    @php($isGrandChildActive = $isItemActive($grandChild))

                                    <a
                                        href="{{ $grandChild->resolved_url }}"
                                        target="{{ $grandChild->resolved_target }}"
                                        rel="{{ $grandChild->rel }}"
                                        class="block rounded px-4 py-3 text-sm font-semibold transition hover:bg-red-50 hover:text-primary {{ $isGrandChildActive ? 'bg-red-50 text-primary' : 'text-slate-700' }}"
                                        @if($isGrandChildActive) aria-current="page" @endif
                                    >
                                        {{ $grandChild->resolved_label }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endforeach
