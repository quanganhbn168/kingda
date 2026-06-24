@php
    $locale = app()->getLocale();
    $footerDescription = data_get($settings->footer_description ?? [], $locale)
        ?: data_get($settings->footer_description ?? [], 'vi')
        ?: __('ui.footer.company_name');
    $footerMenu1Title = data_get($settings->footer_menu_1_title ?? [], $locale)
        ?: data_get($settings->footer_menu_1_title ?? [], 'vi')
        ?: __('ui.footer.quick_links');
    $footerMenu2Title = data_get($settings->footer_menu_2_title ?? [], $locale)
        ?: data_get($settings->footer_menu_2_title ?? [], 'vi')
        ?: __('ui.footer.products');
    $footerMenu1Items = $footerMenu1Items ?? $footerMenuItems ?? collect();
    $footerMenu2Items = $footerMenu2Items ?? collect();
@endphp

<footer class="kd-footer-bg bg-[#17110a] text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
        <div>
            @if(! empty($settings->logo_footer ?? null))
                <img
                    src="{{ asset('storage/' . $settings->logo_footer) }}"
                    alt="{{ $settings->site_name ?? 'Kingda' }}"
                    class="max-h-16 w-auto max-w-[14rem] object-contain"
                >
            @elseif(! empty($settings->logo ?? null))
                <img
                    src="{{ asset('storage/' . $settings->logo) }}"
                    alt="{{ $settings->site_name ?? 'Kingda' }}"
                    class="max-h-16 w-auto max-w-[14rem] object-contain"
                >
            @else
                <div class="text-2xl font-extrabold">{{ $settings->site_name ?? 'Kingda' }}</div>
            @endif

            @if(filled($footerDescription))
                <p class="mt-5 whitespace-pre-line text-sm leading-6 text-slate-300">{{ $footerDescription }}</p>
            @endif

            <div class="mt-4 space-y-2 text-sm text-slate-300">
                <p>{{ $contactSettings->default_address ?: 'Tầng 1, số 27 Trần Quang Khải, Phường Võ Cường, Tỉnh Bắc Ninh, Việt Nam' }}</p>
                <p>{{ ($contactSettings->emails[0]['email'] ?? null) ?: 'kingdah@gmail.com' }}</p>
            </div>
        </div>

        <nav class="text-sm text-slate-300" aria-label="{{ $footerMenu1Title }}">
            <h3 class="mb-3 text-sm font-bold uppercase text-white">{{ $footerMenu1Title }}</h3>
            @if($footerMenu1Items->isNotEmpty())
                @include('frontend.partials.footer-menu-items', ['items' => $footerMenu1Items])
            @endif
        </nav>

        <nav class="text-sm text-slate-300" aria-label="{{ $footerMenu2Title }}">
            <h3 class="mb-3 text-sm font-bold uppercase text-white">{{ $footerMenu2Title }}</h3>
            @if($footerMenu2Items->isNotEmpty())
                @include('frontend.partials.footer-menu-items', ['items' => $footerMenu2Items])
            @endif
        </nav>

        <div>
            <h3 class="mb-3 text-sm font-bold uppercase text-white">{{ __('ui.footer.newsletter') }}</h3>
            <p class="text-sm text-slate-300">{{ __('ui.footer.newsletter_description') }}</p>
            <form class="mt-5 flex overflow-hidden rounded bg-white">
                <input type="email" placeholder="{{ __('ui.footer.email_placeholder') }}" class="min-w-0 flex-1 px-4 py-3 text-sm text-slate-900 outline-none">
                <button type="button" class="bg-primary px-4 text-white">→</button>
            </form>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 text-xs text-slate-400">
            <span>© {{ now()->year }} Kingda. {{ __('ui.footer.rights') }}</span>
            <span>{{ __('ui.footer.privacy_terms') }}</span>
        </div>
    </div>
</footer>
