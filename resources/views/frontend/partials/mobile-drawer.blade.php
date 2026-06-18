<div
    x-cloak
    x-show="drawerOpen"
    x-transition.opacity
    class="fixed inset-0 z-50 bg-slate-950/50 lg:hidden"
    @click="drawerOpen = false"
    @keydown.escape.window="drawerOpen = false"
></div>

<aside
    x-cloak
    x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed right-0 top-0 z-50 h-full w-[86vw] max-w-sm bg-white shadow-2xl lg:hidden"
>
    <div class="flex h-20 items-center justify-between border-b border-slate-200 px-4">
        <a href="{{ url(app()->getLocale() === 'vi' ? '/' : '/' . app()->getLocale()) }}" class="flex items-center gap-3">
            @if($siteSettings->logo)
                <img
                    src="{{ asset('storage/' . $siteSettings->logo) }}"
                    alt="{{ $siteSettings->site_name }}"
                    class="max-h-14 w-auto max-w-[12rem] object-contain"
                >
            @else
                <span class="text-lg font-bold">
                    {{ $siteSettings->site_name }}
                </span>
            @endif
        </a>

        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200"
            @click="drawerOpen = false"
            aria-label="{{ __('ui.nav.close_menu') }}"
        >
            ✕
        </button>
    </div>

    <div class="h-[calc(100vh-5rem)] overflow-y-auto px-4 py-5">
        <div class="mb-5">
            @include('frontend.components.language-switcher')
        </div>

        <nav class="space-y-1">
            @include('frontend.components.mobile-menu-items', [
                'items' => $headerMenuItems,
                'level' => 0,
            ])
        </nav>
    </div>
</aside>
