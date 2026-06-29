@php
    $socialLinks = collect($contactSettings->social_links ?? [])
        ->filter(fn ($item): bool => is_array($item) && filled($item['url'] ?? null));

    $findSocialLink = function (array $keywords) use ($socialLinks): ?array {
        return $socialLinks->first(function (array $item) use ($keywords): bool {
            $haystack = mb_strtolower(($item['label'] ?? '') . ' ' . ($item['url'] ?? ''));

            return collect($keywords)->contains(fn (string $keyword): bool => str_contains($haystack, $keyword));
        });
    };

    $phoneItem = collect($contactSettings->hotlines ?? [])
        ->concat($contactSettings->phones ?? [])
        ->first(fn ($item): bool => filled(is_array($item) ? ($item['phone'] ?? $item['value'] ?? null) : $item));
    $phone = is_array($phoneItem) ? ($phoneItem['phone'] ?? $phoneItem['value'] ?? null) : $phoneItem;
    $phoneHref = $phone ? 'tel:' . preg_replace('/[^0-9+]/', '', $phone) : null;
    $contactPageUrl = app()->getLocale() === 'vi' ? url('/lien-he') : url('/' . app()->getLocale() . '/contact');

    $zalo = $findSocialLink(['zalo']);
    $zaloUrl = $zalo['url'] ?? null;

    if (! $zaloUrl && filled($integrationSettings->zalo_oa_id ?? null)) {
        $zaloUrl = 'https://zalo.me/' . trim($integrationSettings->zalo_oa_id);
    }

    $zaloQrImage = filled($contactSettings->zalo_qr_image ?? null)
        ? asset('storage/' . ltrim($contactSettings->zalo_qr_image, '/'))
        : null;
    $zaloImageUrl = $zaloQrImage ?: ($zaloUrl && (
        str_starts_with($zaloUrl, 'data:image/')
        || preg_match('/\.(?:png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $zaloUrl)
    ) ? $zaloUrl : null);
    $zaloIsImage = filled($zaloImageUrl);

    $wechat = $findSocialLink(['wechat', 'weixin', '微信']);
    $wechatUrl = $wechat['url'] ?? null;
    $wechatQrImage = filled($contactSettings->wechat_qr_image ?? null)
        ? asset('storage/' . ltrim($contactSettings->wechat_qr_image, '/'))
        : null;
    $wechatImageUrl = $wechatQrImage ?: ($wechatUrl && (
        str_starts_with($wechatUrl, 'data:image/')
        || preg_match('/\.(?:png|jpe?g|gif|webp|svg)(?:\?.*)?$/i', $wechatUrl)
    ) ? $wechatUrl : null);
    $wechatIsImage = filled($wechatImageUrl);

    $zaloHref = $zaloUrl ?: $contactPageUrl;
    $phoneHref = $phoneHref ?: $contactPageUrl;
    $wechatHref = $wechatUrl ?: $contactPageUrl;
@endphp

<aside
        class="kd-floating-contacts"
        :class="{ 'kd-floating-contacts--has-top': showBackToTop }"
        aria-label="{{ app()->getLocale() === 'vi' ? 'Liên hệ nhanh' : 'Quick contact' }}"
        x-data="{ zaloOpen: false, wechatOpen: false, showBackToTop: false }"
        x-init="showBackToTop = window.scrollY > 360"
        @scroll.window.throttle.150ms="showBackToTop = window.scrollY > 360"
        @keydown.escape.window="zaloOpen = false; wechatOpen = false"
    >
        <div class="kd-floating-contact-wrap" @click.outside="zaloOpen = false">
            @if($zaloIsImage)
                <button
                    type="button"
                    class="kd-floating-contact kd-floating-contact--zalo"
                    aria-label="Zalo"
                    :aria-expanded="zaloOpen"
                    @click="zaloOpen = !zaloOpen; wechatOpen = false"
                >
                    <span class="kd-floating-contact__icon kd-floating-contact__icon--zalo">Zalo</span>
                    <span class="kd-floating-contact__label">Zalo</span>
                </button>

                <div
                    class="kd-floating-qr"
                    x-cloak
                    x-show="zaloOpen"
                    x-transition.opacity.duration.180ms
                >
                    <button type="button" class="kd-floating-qr__close" aria-label="Close" @click="zaloOpen = false">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <img src="{{ $zaloImageUrl }}" alt="Zalo QR code">
                    <strong>Zalo</strong>
                    <span>{{ app()->getLocale() === 'vi' ? 'Quét mã để kết nối' : 'Scan to connect' }}</span>
                </div>
            @else
                <a
                    href="{{ $zaloHref }}"
                    @if($zaloUrl) target="_blank" rel="noopener noreferrer" @endif
                    class="kd-floating-contact kd-floating-contact--zalo"
                    aria-label="Zalo"
                >
                    <span class="kd-floating-contact__icon kd-floating-contact__icon--zalo">Zalo</span>
                    <span class="kd-floating-contact__label">Zalo</span>
                </a>
            @endif
        </div>

        <a
            href="{{ $phoneHref }}"
            class="kd-floating-contact kd-floating-contact--phone"
            aria-label="{{ app()->getLocale() === 'vi' ? 'Gọi điện' : 'Call us' }}{{ $phone ? ': ' . $phone : '' }}"
        >
            <span class="kd-floating-contact__icon"><i class="fa-solid fa-phone-volume"></i></span>
            <span class="kd-floating-contact__label">{{ app()->getLocale() === 'vi' ? 'Điện thoại' : 'Phone' }}</span>
        </a>

        <div class="kd-floating-contact-wrap" @click.outside="wechatOpen = false">
            @if($wechatIsImage)
                    <button
                        type="button"
                        class="kd-floating-contact kd-floating-contact--wechat"
                        aria-label="WeChat"
                        :aria-expanded="wechatOpen"
                        @click="wechatOpen = !wechatOpen; zaloOpen = false"
                    >
                        <span class="kd-floating-contact__icon"><i class="fa-brands fa-weixin"></i></span>
                        <span class="kd-floating-contact__label">WeChat</span>
                    </button>

                    <div
                        class="kd-floating-qr"
                        x-cloak
                        x-show="wechatOpen"
                        x-transition.opacity.duration.180ms
                    >
                        <button type="button" class="kd-floating-qr__close" aria-label="Close" @click="wechatOpen = false">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                        <img src="{{ $wechatImageUrl }}" alt="WeChat QR code">
                        <strong>WeChat</strong>
                        <span>{{ app()->getLocale() === 'vi' ? 'Quét mã để kết nối' : 'Scan to connect' }}</span>
                    </div>
            @else
                <a
                    href="{{ $wechatHref }}"
                    @if($wechatUrl) target="_blank" rel="noopener noreferrer" @endif
                    class="kd-floating-contact kd-floating-contact--wechat"
                    aria-label="WeChat"
                >
                    <span class="kd-floating-contact__icon"><i class="fa-brands fa-weixin"></i></span>
                    <span class="kd-floating-contact__label">WeChat</span>
                </a>
            @endif
        </div>

        <button
            type="button"
            class="kd-floating-contact kd-floating-contact--top"
            aria-label="{{ app()->getLocale() === 'vi' ? 'Lên đầu trang' : 'Back to top' }}"
            x-cloak
            x-show="showBackToTop"
            x-transition.opacity.duration.180ms
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        >
            <span class="kd-floating-contact__icon"><i class="fa-solid fa-arrow-up"></i></span>
            <span class="kd-floating-contact__label">{{ app()->getLocale() === 'vi' ? 'Lên đầu' : 'Top' }}</span>
        </button>
</aside>
