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
                @include('frontend.components.desktop-submenu-items', [
                    'items' => $item->childrenRecursive,
                    'isItemActive' => $isItemActive,
                ])
            </div>
        @endif
    </div>
@endforeach
