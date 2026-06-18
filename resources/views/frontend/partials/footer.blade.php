<footer class="kd-footer-bg bg-[#17110a] text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-4 lg:px-8">
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
            <p class="mt-5 text-sm leading-6 text-slate-300">{{ __('ui.footer.company_name') }}</p>
            <div class="mt-4 space-y-2 text-sm text-slate-300">
                <p>{{ $contactSettings->default_address ?: 'Tầng 1, số 27 Trần Quang Khải, Phường Võ Cường, Tỉnh Bắc Ninh, Việt Nam' }}</p>
                <p>{{ ($contactSettings->emails[0]['email'] ?? null) ?: 'kingdah@gmail.com' }}</p>
            </div>
        </div>

        @if(($footerMenuItems ?? collect())->isNotEmpty())
            <nav class="grid gap-2 text-sm text-slate-300">
                <h3 class="mb-3 text-sm font-bold uppercase text-white">{{ __('ui.footer.quick_links') }}</h3>
                @foreach($footerMenuItems as $item)
                    <a
                        href="{{ $item->resolved_url ?: '#' }}"
                        target="{{ $item->target }}"
                        rel="{{ $item->rel }}"
                        class="hover:text-white"
                    >
                        {{ $item->label }}
                    </a>
                @endforeach
            </nav>
        @endif

        <div class="text-sm text-slate-300">
            <h3 class="mb-3 text-sm font-bold uppercase text-white">{{ __('ui.footer.products') }}</h3>
            <ul class="space-y-2">
                <li>Dòng sản phẩm gốc nước</li>
                <li>Dòng sản phẩm in nổi</li>
                <li>Dòng AG chống chói</li>
                <li>Mực in chống cháy</li>
                <li>Sơn phủ mạ chân không</li>
            </ul>
        </div>

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
