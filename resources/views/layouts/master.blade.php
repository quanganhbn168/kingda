<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    @php
        $seo = $seoTranslation ?? $translation ?? null;
        $seoTitle = data_get($seo, 'seo_title')
            ?: data_get($seo, 'meta_title')
            ?: data_get($seo, 'title')
            ?: data_get($seo, 'name')
            ?: ($seoSettings->default_seo_title ?? null)
            ?: ($settings->site_name ?? config('app.name'));
        $seoDescription = data_get($seo, 'seo_description')
            ?: data_get($seo, 'meta_description')
            ?: data_get($seo, 'description')
            ?: data_get($seo, 'excerpt')
            ?: ($seoSettings->default_seo_description ?? null);
        $isPostTranslation = $seo instanceof \App\Models\PostTranslation;
        $ogTitle = $isPostTranslation ? $seoTitle : (data_get($seo, 'og_title') ?: $seoTitle);
        $ogDescription = $isPostTranslation ? $seoDescription : (data_get($seo, 'og_description') ?: $seoDescription);
        $canonicalUrl = $isPostTranslation
            ? (data_get($seo, 'public_url') ?: url()->current())
            : (data_get($seo, 'canonical_url') ?: data_get($seo, 'public_url') ?: url()->current());
        $robots = data_get($seo, 'meta_robots')
            ?: ($seoSettings->default_robots ?? null)
            ?: 'index,follow';
        $resolvedOgImage = $ogImage ?? null;

        if (! $resolvedOgImage && $seo && method_exists($seo, 'getFirstMediaUrl')) {
            $resolvedOgImage = $isPostTranslation
                ? ($seo->getFirstMediaUrl('hero') ?: $seo->getFirstMediaUrl('thumbnail'))
                : ($seo->getFirstMediaUrl('og_image') ?: $seo->getFirstMediaUrl('hero') ?: $seo->getFirstMediaUrl('thumbnail'));
        }

        $resolvedOgImage = $resolvedOgImage
            ?: (filled($seoSettings->default_og_image ?? null)
                ? asset('storage/' . ltrim($seoSettings->default_og_image, '/'))
                : null);
    @endphp

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = $settings->site_name ?? config('app.name');
        $displayTitle = $seoTitle === $siteName ? $siteName : $seoTitle . ' - ' . $siteName;
        $displayOgTitle = $ogTitle === $siteName ? $siteName : $ogTitle . ' - ' . $siteName;
    @endphp

    <title>{{ $displayTitle }}</title>

    @if(filled($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif

    <meta name="robots" content="{{ $robots }}">

    <link rel="canonical" href="{{ $canonicalUrl }}">

    @foreach($alternateUrls ?? [] as $locale => $url)
        @if(! empty($url))
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
        @endif
    @endforeach

    @if(! empty(($alternateUrls ?? [])['vi']))
        <link rel="alternate" hreflang="x-default" href="{{ $alternateUrls['vi'] }}">
    @else
        <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    @endif

    @if(! empty($settings->favicon))
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon) }}">
    @endif

    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'vi' ? 'vi_VN' : app()->getLocale() }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $displayOgTitle }}">

    @if(filled($ogDescription))
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif

    <meta property="og:url" content="{{ $canonicalUrl }}">

    @if($resolvedOgImage)
        <meta property="og:image" content="{{ $resolvedOgImage }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $displayOgTitle }}">

    @if(filled($ogDescription))
        <meta name="twitter:description" content="{{ $ogDescription }}">
    @endif

    @if($resolvedOgImage)
        <meta name="twitter:image" content="{{ $resolvedOgImage }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
    @stack('head')

    @if(! empty($schema))
        <script type="application/ld+json">
            {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>
    @endif

    @if(! empty($integrationSettings->header_scripts ?? null))
        {!! $integrationSettings->header_scripts !!}
    @endif
</head>

<body class="min-h-screen bg-white text-slate-900 antialiased">
    <div class="flex min-h-screen flex-col">
        @include('frontend.partials.header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('frontend.partials.floating-contacts')

        @include('frontend.partials.footer')
    </div>

    @if(! empty($integrationSettings->footer_scripts ?? null))
        {!! $integrationSettings->footer_scripts !!}
    @endif

    @stack('scripts')
</body>
</html>
