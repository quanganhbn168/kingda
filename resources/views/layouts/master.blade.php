<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $translation->seo_title ?? $translation->title ?? $translation->name ?? $settings->site_name ?? config('app.name') }}</title>

    @if(! empty($translation->seo_description ?? $translation->description ?? null))
        <meta name="description" content="{{ $translation->seo_description ?? $translation->description }}">
    @endif

    <meta name="robots" content="{{ $translation->meta_robots ?? 'index,follow' }}">

    <link rel="canonical" href="{{ $translation->canonical_url ?? $translation->public_url ?? url()->current() }}">

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
    <meta property="og:site_name" content="{{ $settings->site_name ?? config('app.name') }}">
    <meta property="og:title" content="{{ $translation->og_title ?? $translation->seo_title ?? $translation->title ?? $translation->name ?? $settings->site_name ?? config('app.name') }}">

    @if(! empty($translation->og_description ?? $translation->seo_description ?? $translation->description ?? null))
        <meta property="og:description" content="{{ $translation->og_description ?? $translation->seo_description ?? $translation->description }}">
    @endif

    <meta property="og:url" content="{{ $translation->public_url ?? url()->current() }}">

    @if(! empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
    @elseif(isset($translation) && method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('og_image'))
        <meta property="og:image" content="{{ $translation->getFirstMediaUrl('og_image') }}">
    @elseif(isset($translation) && method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('hero'))
        <meta property="og:image" content="{{ $translation->getFirstMediaUrl('hero') }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $translation->og_title ?? $translation->seo_title ?? $translation->title ?? $translation->name ?? $settings->site_name ?? config('app.name') }}">

    @if(! empty($translation->og_description ?? $translation->seo_description ?? $translation->description ?? null))
        <meta name="twitter:description" content="{{ $translation->og_description ?? $translation->seo_description ?? $translation->description }}">
    @endif

    @if(! empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @elseif(isset($translation) && method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('og_image'))
        <meta name="twitter:image" content="{{ $translation->getFirstMediaUrl('og_image') }}">
    @elseif(isset($translation) && method_exists($translation, 'getFirstMediaUrl') && $translation->getFirstMediaUrl('hero'))
        <meta name="twitter:image" content="{{ $translation->getFirstMediaUrl('hero') }}">
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

        @include('frontend.partials.footer')
    </div>

    @if(! empty($integrationSettings->footer_scripts ?? null))
        {!! $integrationSettings->footer_scripts !!}
    @endif

    @stack('scripts')
</body>
</html>
