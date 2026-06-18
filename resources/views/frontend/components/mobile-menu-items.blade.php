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

    <div class="{{ ($level ?? 0) > 0 ? 'ml-4 border-l border-slate-100 pl-3' : '' }}">
        <a
            href="{{ $item->resolved_url ?: '#' }}"
            target="{{ $item->resolved_target }}"
            rel="{{ $item->rel }}"
            class="block rounded-xl px-3 py-3 text-sm font-semibold transition {{ $isActive ? 'bg-red-50 text-primary' : 'text-slate-800 hover:bg-slate-50 hover:text-primary' }}"
            @if($isActive) aria-current="page" @endif
            @if(($item->resolved_target ?? '_self') === '_self') @click="drawerOpen = false" @endif
        >
            {{ $item->resolved_label }}
        </a>

        @if($item->has_children)
            <div class="mt-1 space-y-1">
                @include('frontend.components.mobile-menu-items', [
                    'items' => $item->childrenRecursive,
                    'level' => ($level ?? 0) + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
