<header x-data="{ drawerOpen: false }" class="sticky top-0 z-50 border-b border-slate-200 bg-white shadow-sm">
    <div class="hidden border-b border-slate-200 bg-slate-50 text-xs text-slate-600 lg:block">
        <div class="mx-auto flex h-9 max-w-7xl items-center justify-between px-4">
            <div class="flex items-center gap-6">
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-primary-dark"></i>
                    {{ $contactSettings->default_address ?: 'Tầng 1, số 27 Trần Quang Khải, P. Vũ Cường, Tỉnh Bắc Ninh, Việt Nam' }}
                </span>
                <span class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-primary-dark"></i>
                    {{ ($contactSettings->emails[0]['email'] ?? null) ?: 'kingdah@gmail.com' }}
                </span>
                @if(! empty($contactSettings->phones[0]['phone'] ?? null))
                    <span class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-phone text-primary-dark"></i>
                        {{ $contactSettings->phones[0]['phone'] }}
                    </span>
                @endif
            </div>

            @include('frontend.components.language-switcher')
        </div>
    </div>

    <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 lg:h-[5.5rem]">
        <a href="{{ url(app()->getLocale() === 'vi' ? '/' : '/' . app()->getLocale()) }}" class="flex min-w-0 items-center gap-3">
            @if($siteSettings->logo)
                <img
                    src="{{ asset('storage/' . $siteSettings->logo) }}"
                    alt="{{ $siteSettings->site_name }}"
                    class="max-h-16 w-auto max-w-[13rem] object-contain lg:max-h-20 lg:max-w-[16rem]"
                >
            @else
                <span class="text-3xl font-extrabold text-[#d71920]">
                    {{ $siteSettings->site_name ?: 'Kingda' }}
                </span>
            @endif
        </a>

        <nav class="hidden h-full items-center lg:flex">
            @include('frontend.components.menu-items', [
                'items' => $headerMenuItems,
                'level' => 0,
            ])
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            <a href="{{ $contactUrl }}" class="inline-flex rounded bg-primary px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary-dark">
                {{ __('ui.actions.contact_consulting') }}
            </a>
        </div>

        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded border border-slate-200 lg:hidden"
            @click="drawerOpen = true"
            aria-label="{{ __('ui.nav.open_menu') }}"
        >
            <span class="block h-0.5 w-5 bg-slate-900"></span>
        </button>
    </div>

    @include('frontend.partials.mobile-drawer')
</header>
