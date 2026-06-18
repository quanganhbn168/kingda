@extends('layouts.master')

@php
    $settings = $homeSettings ?? [];
    $stats = collect($settings['stats'] ?? []);
    $intro = $settings['intro'] ?? [];
    $industrySection = $settings['industries'] ?? [];
    $productSection = $settings['products'] ?? [];
    $capabilities = $settings['capabilities'] ?? [];
    $certifications = $settings['certifications'] ?? [];
    $advantages = $settings['advantages'] ?? [];
    $customers = $settings['customers'] ?? [];
    $newsSection = $settings['news'] ?? [];
    $cta = $settings['cta'] ?? [];
    $ctaBackground = $cta['background_image'] ?? asset('images/kingda-contact.jpg');
    $industries = collect($homeIndustries ?? []);
    $productGroups = collect($homeProductCategories ?? []);
    $homeNews = collect($homePosts ?? []);
    $locale = app()->getLocale();
    $productIndexUrl = $locale === 'vi' ? url('/san-pham') : url('/' . $locale . '/products');
    $contactPageUrl = $locale === 'vi' ? url('/lien-he') : url('/' . $locale . '/contact');
    $aboutPageUrl = $locale === 'vi' ? url('/gioi-thieu') : url('/' . $locale . '/about');
    $newsIndexUrl = $locale === 'vi' ? url('/tin-tuc') : url('/' . $locale . '/news');
@endphp

@section('content')
    @if (($homeSlides ?? collect())->count())
        @include('frontend.partials.home-slides', ['slides' => $homeSlides])
    @else
        <section class="relative overflow-hidden bg-[#17110a] text-white">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(242,211,0,.28),transparent_24%),linear-gradient(105deg,rgba(23,17,10,.98)_0%,rgba(159,15,22,.88)_48%,rgba(242,211,0,.16)_100%)]">
            </div>
            <div class="relative mx-auto flex min-h-[480px] max-w-7xl items-center px-4 py-20">
                <div class="max-w-3xl">
                    <p class="text-sm font-bold uppercase tracking-wide text-red-100">
                        {{ $translation->title }}
                    </p>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight md:text-5xl">
                        {{ $translation->headline ?: $translation->title }}
                    </h1>
                    @if ($translation->subheadline)
                        <p class="mt-6 max-w-2xl text-base leading-7 text-slate-100">
                            {{ $translation->subheadline }}
                        </p>
                    @endif
                    <div class="mt-9 flex flex-wrap gap-4">
                        <a href="{{ $productIndexUrl }}"
                            class="rounded bg-primary px-7 py-3 text-sm font-bold text-white shadow hover:bg-primary-dark">
                            {{ $productSection['button_label'] ?? __('ui.actions.view_all') }}
                        </a>
                        <a href="{{ $contactPageUrl }}"
                            class="rounded border border-white/70 px-7 py-3 text-sm font-bold text-white hover:bg-white hover:text-[#9f0f16]">
                            {{ $cta['button_label'] ?? __('ui.actions.contact_consulting') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($stats->isNotEmpty())
        <section class="-mt-8 relative z-10">
            <div class="mx-auto max-w-6xl rounded bg-white px-6 py-8 shadow-xl ring-1 ring-slate-200">
                <div class="grid gap-6 md:grid-cols-4">
                    @foreach ($stats as $item)
                        <div class="border-slate-200 md:border-r md:last:border-r-0">
                            <div class="flex gap-4">
                                <div class="text-3xl text-primary-dark"><i class="fa-solid fa-chart-line"></i></div>
                                <div>
                                    <div class="text-4xl font-extrabold text-primary-dark">{{ $item['value'] ?? '' }}</div>
                                    <div class="mt-1 text-sm font-bold text-slate-900">{{ $item['label'] ?? '' }}</div>
                                    <p class="mt-2 max-w-44 text-xs leading-5 text-slate-600">
                                        {{ $item['description'] ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-20">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 lg:grid-cols-2">
            <div>
                <p class="text-sm font-extrabold uppercase text-primary-dark">{{ $intro['eyebrow'] ?? '' }}</p>
                <h2 class="mt-3 text-4xl font-extrabold leading-tight text-slate-950">{{ $intro['title'] ?? '' }}</h2>
                @if (!empty($intro['description']))
                    <p class="mt-6 leading-7 text-slate-600">{{ $intro['description'] }}</p>
                @endif
                @if (!empty($intro['content']))
                    <p class="mt-4 leading-7 text-slate-600">{{ $intro['content'] }}</p>
                @endif
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ $aboutPageUrl }}"
                        class="inline-flex rounded bg-primary px-6 py-3 text-sm font-bold text-white hover:bg-primary-dark">
                        {{ $intro['button_label'] ?? __('ui.actions.learn_more') }}
                    </a>
                    @if (!empty($intro['video_embed_url']) || !empty($intro['video_upload']))
                        <a href="{{ $intro['video_embed_url'] ?: $intro['video_upload'] }}" data-glightbox
                            class="inline-flex rounded border border-primary px-6 py-3 text-sm font-bold text-primary-dark hover:bg-primary hover:text-white">
                            <i class="fa-solid fa-play mr-2"></i>
                            {{ __('ui.actions.video') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="grid overflow-hidden rounded bg-white shadow-xl ring-1 ring-slate-200 sm:grid-cols-2">
                @foreach ($intro['items'] ?? [] as $item)
                    <div class="border-b border-r border-slate-200 p-8">
                        <div class="mb-4 text-3xl text-primary-dark"><i
                                class="fa-solid {{ $item['icon'] ?? 'fa-flask-vial' }}"></i></div>
                        <h3 class="font-extrabold uppercase text-[#17110a]">{{ $item['title'] ?? '' }}</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $item['description'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($industries->isNotEmpty())
        <section class="overflow-hidden bg-[#17110a] py-16 text-white">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-extrabold uppercase text-red-200">{{ $industrySection['eyebrow'] ?? '' }}
                        </p>
                        <h2 class="mt-3 max-w-4xl text-4xl font-extrabold leading-tight">
                            {{ $industrySection['title'] ?? '' }}</h2>
                        @if (!empty($industrySection['description']))
                            <p class="mt-4 max-w-3xl text-slate-300">{{ $industrySection['description'] }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-applications-prev aria-label="{{ __('ui.nav.previous') }}"
                            class="flex size-11 items-center justify-center rounded border border-white/20 bg-white/10 text-white hover:bg-white hover:text-slate-950">
                            <i class="fa-solid fa-arrow-left"></i>
                        </button>
                        <button type="button" data-applications-next aria-label="{{ __('ui.nav.next') }}"
                            class="flex size-11 items-center justify-center rounded border border-white/20 bg-white/10 text-white hover:bg-white hover:text-slate-950">
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="ml-[max(1rem,calc((100vw-80rem)/2+1rem))] mt-10 pr-4">
                <div data-application-swiper class="swiper !overflow-visible">
                    <div class="swiper-wrapper">
                        @foreach ($industries as $item)
                            <div class="swiper-slide !h-auto">
                                <a href="{{ $item['url'] ?? '#' }}"
                                    class="group relative flex h-[29rem] overflow-hidden rounded bg-white text-slate-950 shadow-xl ring-1 ring-white/10 transition hover:-translate-y-1 hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-[#17110a]">
                                    @if (!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}"
                                            class="absolute inset-0 h-full w-full object-cover">
                                    @else
                                        <div
                                            class="absolute inset-0 bg-[linear-gradient(135deg,rgba(255,255,255,1)_0%,rgba(254,242,242,.95)_52%,rgba(226,232,240,.88)_100%)]">
                                        </div>
                                        <div class="absolute right-8 top-8 text-8xl text-primary/20"><i
                                                class="fa-solid {{ $item['icon'] ?? 'fa-layer-group' }}"></i></div>
                                    @endif
                                    <div
                                        class="absolute inset-x-0 bottom-0 z-10 bg-[linear-gradient(0deg,rgba(23,17,10,.94)_0%,rgba(159,15,22,.76)_52%,rgba(159,15,22,0)_100%)] px-7 pb-7 pt-28 text-white">
                                        <h3 class="max-w-xs text-3xl font-extrabold leading-tight">
                                            {{ $item['title'] ?? '' }}</h3>
                                        <div
                                            class="max-h-0 translate-y-4 overflow-hidden opacity-0 transition-all duration-300 group-hover:max-h-44 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:max-h-44 group-focus-within:translate-y-0 group-focus-within:opacity-100">
                                            <p class="mt-4 text-sm leading-7 text-slate-100">
                                                {{ \Illuminate\Support\Str::limit($item['description'] ?? '', 150) }}</p>
                                            @if (!empty($item['url']))
                                                <span
                                                    class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-white">
                                                    {{ $industrySection['button_label'] ?? __('ui.actions.view_detail') }}
                                                    <i
                                                        class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div data-applications-progress
                    class="swiper-pagination !relative !bottom-auto !left-auto !top-auto mt-8 !h-1.5 max-w-5xl overflow-hidden rounded-full bg-white/15 [--swiper-pagination-color:var(--color-primary-soft)]">
                </div>
            </div>
        </section>
    @endif

    @if ($productGroups->isNotEmpty())
        <section class="relative overflow-hidden bg-white py-20 text-slate-950">
            <div class="absolute inset-x-0 top-0 h-px bg-slate-100"></div>

            <div class="relative mx-auto max-w-7xl px-4">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                    <div class="max-w-3xl">
                        @if (!empty($productSection['eyebrow']))
                            <p class="text-sm font-black uppercase tracking-[.18em] text-primary-dark">
                                {{ $productSection['eyebrow'] }}
                            </p>
                        @endif

                        <h2 class="mt-3 text-3xl font-black leading-tight text-[#17110a] md:text-[2.35rem]">
                            {{ $productSection['title'] ?? '' }}
                        </h2>

                        @if (!empty($productSection['description']))
                            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                                {{ $productSection['description'] }}
                            </p>
                        @endif
                    </div>

                    <a href="{{ $productIndexUrl }}"
                        class="group inline-flex w-fit items-center gap-3 rounded-full border border-primary bg-primary px-5 py-3 text-sm font-black text-white shadow-lg shadow-primary/20 transition hover:border-primary-dark hover:bg-primary-dark">
                        {{ $productSection['button_label'] ?? __('ui.actions.view_all') }}

                        <span
                            class="grid size-7 place-items-center rounded-full bg-white/15 text-white transition group-hover:bg-white group-hover:text-primary">
                            <i class="fa-solid fa-arrow-right-long text-xs transition group-hover:translate-x-0.5"></i>
                        </span>
                    </a>
                </div>

                <div x-data="{ activeProductGroup: 0 }" class="mt-10">
                    {{-- Tabs danh mục --}}
                    <div data-drag-scroll class="kd-scrollbar-none -mx-4 mb-8 flex gap-3 px-4 pb-2">
                        @foreach ($productGroups as $groupIndex => $group)
                            <button type="button"
                                @click="activeProductGroup = {{ $groupIndex }}; $nextTick(() => window.dispatchEvent(new CustomEvent('kingda-product-tab-changed')))"
                                class="group/tab min-w-[12rem] shrink-0 rounded-full border px-5 py-3 text-left transition"
                                :class="activeProductGroup === {{ $groupIndex }} ?
                                    'border-primary bg-primary text-white shadow-lg shadow-primary/20' :
                                    'border-slate-200 bg-white text-slate-700 hover:border-primary/40 hover:bg-red-50 hover:text-primary-dark'">
                                <span class="flex items-center gap-3">
                                    <span class="grid size-8 place-items-center rounded-full text-xs font-black"
                                        :class="activeProductGroup === {{ $groupIndex }} ?
                                            'bg-white/20 text-white' :
                                            'bg-red-50 text-primary-dark'">
                                        {{ str_pad((string) ($groupIndex + 1), 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    <span class="min-w-0 truncate text-sm font-black">
                                        {{ $group['title'] ?? '' }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Panel sản phẩm --}}
                    <div class="min-h-[34rem]">
                        @foreach ($productGroups as $groupIndex => $group)
                            @php
                                $hasGroupImage = !empty($group['image']);
                                $allGroupProducts = collect($group['products'] ?? []);
                            @endphp

                            <div x-cloak x-show="activeProductGroup === {{ $groupIndex }}" class="kd-product-panel">
                                <div class="grid min-w-0 gap-5 lg:grid-cols-[minmax(18rem,.82fr)_minmax(0,1.18fr)]">
                                    {{-- Card danh mục lớn --}}
                                    <a href="{{ $group['url'] ?? $productIndexUrl }}"
                                        class="group relative min-h-[25rem] overflow-hidden rounded-2xl bg-white text-slate-950 shadow-xl shadow-slate-200/70 ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-2xl">

                                        @if ($hasGroupImage)
                                            <img src="{{ $group['image'] }}" alt="{{ $group['title'] ?? '' }}"
                                                class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">

                                            <div
                                                class="absolute inset-0 bg-[linear-gradient(0deg,rgba(23,17,10,.76),rgba(23,17,10,.30)_58%,rgba(23,17,10,.05))]">
                                            </div>
                                        @else
                                            <div
                                                class="absolute inset-0 bg-[linear-gradient(135deg,#fff,#fef2f2_54%,#fff_100%)]">
                                            </div>

                                            <div
                                                class="absolute -right-10 -top-10 text-[12rem] text-primary/10 transition duration-500 group-hover:scale-110">
                                                <i class="fa-solid fa-layer-group"></i>
                                            </div>

                                            <div
                                                class="absolute left-7 top-7 grid size-16 place-items-center rounded-2xl bg-primary text-3xl text-white shadow-xl shadow-primary-dark/20">
                                                <i class="fa-solid fa-flask-vial"></i>
                                            </div>
                                        @endif

                                        <div
                                            class="absolute inset-x-0 bottom-0 p-7 {{ $hasGroupImage ? 'text-white' : 'text-[#17110a]' }}">
                                            <span
                                                class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-black uppercase tracking-[.16em] backdrop-blur {{ $hasGroupImage ? 'bg-white/15 text-red-100' : 'bg-red-50 text-primary-dark' }}">
                                                <i class="fa-solid fa-cubes-stacked"></i>
                                                {{ $locale === 'vi' ? 'Dòng sản phẩm' : 'Product line' }}
                                            </span>

                                            <h3 class="mt-4 text-2xl font-black leading-tight md:text-3xl">
                                                {{ $group['title'] ?? '' }}
                                            </h3>

                                            @if (!empty($group['description']))
                                                <p
                                                    class="mt-4 max-w-md text-sm leading-7 {{ $hasGroupImage ? 'text-white/82' : 'text-slate-600' }}">
                                                    {{ \Illuminate\Support\Str::limit($group['description'], 170) }}
                                                </p>
                                            @endif

                                            <span
                                                class="mt-6 inline-flex items-center gap-3 text-sm font-black {{ $hasGroupImage ? 'text-white' : 'text-primary-dark' }}">
                                                {{ $locale === 'vi' ? 'Xem nhóm sản phẩm' : 'View product line' }}

                                                <span
                                                    class="grid size-8 place-items-center rounded-full bg-primary text-white transition group-hover:translate-x-1 group-hover:bg-primary-dark">
                                                    <i class="fa-solid fa-arrow-right-long text-xs"></i>
                                                </span>
                                            </span>
                                        </div>
                                    </a>

                                    {{-- Danh sách sản phẩm bên phải - Swiper --}}
                                    <div class="min-w-0 overflow-hidden">
                                        @if ($allGroupProducts->isNotEmpty())
                                            <div data-product-swiper-wrap class="relative min-w-0 overflow-hidden">
                                                <div data-product-swiper
                                                    class="swiper kd-product-swiper !overflow-hidden !px-1 !pb-2">
                                                    <div class="swiper-wrapper">
                                                        @foreach ($allGroupProducts as $product)
                                                            <div class="swiper-slide !h-auto">
                                                                <a href="{{ $product['url'] ?? ($group['url'] ?? $productIndexUrl) }}"
                                                                    class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white text-slate-950 shadow-lg shadow-slate-200/70 ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-slate-300/70 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-white">

                                                                    <div class="relative aspect-[4/3] overflow-hidden bg-red-50">
                                                                        @if (!empty($product['image']))
                                                                            <img src="{{ $product['image'] }}"
                                                                                alt="{{ $product['title'] ?? '' }}"
                                                                                class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                                                        @else
                                                                            <div
                                                                                class="absolute inset-0 grid place-items-center bg-[linear-gradient(135deg,#fff,#fef2f2)] text-5xl text-primary">
                                                                                <i class="fa-solid fa-box-open"></i>
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                    <div class="flex flex-1 flex-col p-6">
                                                                        <div
                                                                            class="text-[11px] font-black uppercase tracking-[.18em] text-primary-dark">
                                                                            {{ $product['sku'] ?? 'Kingda' }}
                                                                        </div>

                                                                        <h4
                                                                            class="mt-3 line-clamp-2 text-xl font-black leading-snug text-[#17110a] transition group-hover:text-primary">
                                                                            {{ $product['title'] ?? '' }}
                                                                        </h4>

                                                                        @if (!empty($product['description']))
                                                                            <p
                                                                                class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                                                                {{ $product['description'] }}
                                                                            </p>
                                                                        @endif

                                                                        <span
                                                                            class="mt-auto inline-flex items-center gap-2 pt-5 text-xs font-black uppercase tracking-[.16em] text-primary-dark">
                                                                            {{ __('ui.actions.view_detail') }}
                                                                            <i
                                                                                class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                                                        </span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                @if ($allGroupProducts->count() > 1)
                                                    <div class="mt-5 flex items-center justify-between gap-4">
                                                        <div data-product-swiper-pagination
                                                            class="swiper-pagination !relative !bottom-auto !left-auto !top-auto flex-1">
                                                        </div>

                                                        <div class="flex shrink-0 gap-2">
                                                            <button type="button" data-product-swiper-prev
                                                                class="grid size-11 place-items-center rounded-full border border-slate-200 bg-white text-primary-dark shadow-sm transition hover:border-primary hover:bg-primary hover:text-white"
                                                                aria-label="{{ __('ui.nav.previous') }}">
                                                                <i class="fa-solid fa-arrow-left text-sm"></i>
                                                            </button>

                                                            <button type="button" data-product-swiper-next
                                                                class="grid size-11 place-items-center rounded-full border border-slate-200 bg-white text-primary-dark shadow-sm transition hover:border-primary hover:bg-primary hover:text-white"
                                                                aria-label="{{ __('ui.nav.next') }}">
                                                                <i class="fa-solid fa-arrow-right text-sm"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <a href="{{ $group['url'] ?? $productIndexUrl }}"
                                                    class="group mt-5 flex items-center justify-between gap-5 rounded-2xl border border-dashed border-primary/25 bg-red-50/70 p-5 text-primary-dark transition hover:border-primary hover:bg-red-50">

                                                    <div>
                                                        <div
                                                            class="text-xs font-black uppercase tracking-[.16em] text-primary">
                                                            {{ $locale === 'vi' ? 'Toàn bộ nhóm' : 'Product line' }}
                                                        </div>

                                                        <div class="mt-1 text-lg font-black text-[#17110a]">
                                                            {{ $locale === 'vi' ? 'Xem tất cả ' . $allGroupProducts->count() . ' sản phẩm' : 'View all ' . $allGroupProducts->count() . ' products' }}
                                                        </div>
                                                    </div>

                                                    <span
                                                        class="grid size-10 shrink-0 place-items-center rounded-full bg-primary text-white transition group-hover:translate-x-1 group-hover:bg-primary-dark">
                                                        <i class="fa-solid fa-arrow-right-long"></i>
                                                    </span>
                                                </a>
                                            </div>
                                        @else
                                            <div
                                                class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-slate-500">
                                                {{ $locale === 'vi' ? 'Chưa có sản phẩm trong danh mục này.' : 'No products in this category yet.' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (!empty($capabilities['items']))
        <section class="bg-white py-20">
            <div class="mx-auto max-w-7xl px-4">
                <div class="mx-auto max-w-3xl text-center">
                    <p class="text-sm font-extrabold uppercase text-primary-dark">{{ $capabilities['eyebrow'] ?? '' }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold uppercase text-[#17110a] md:text-4xl">
                        {{ $capabilities['title'] ?? '' }}</h2>
                    <div class="mx-auto mt-4 h-1 w-16 rounded bg-primary-dark"></div>
                    <p class="mt-5 leading-7 text-slate-600">{{ $capabilities['description'] ?? '' }}</p>
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    @foreach ($capabilities['items'] as $index => $item)
                        <article
                            class="group overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                            <div
                                class="relative flex h-48 items-end overflow-hidden bg-[linear-gradient(135deg,rgba(23,17,10,.96)_0%,rgba(159,15,22,.82)_58%,rgba(215,25,32,.72)_100%)] p-6 text-white">
                                @if (!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? '' }}"
                                        class="absolute inset-0 h-full w-full object-cover opacity-70 transition duration-500 group-hover:scale-105">
                                    <div
                                        class="absolute inset-0 bg-[linear-gradient(0deg,rgba(23,17,10,.82),rgba(159,15,22,.34))]">
                                    </div>
                                @else
                                    <div
                                        class="absolute -right-8 -top-8 text-9xl text-white/16 transition duration-300 group-hover:scale-110">
                                        <i class="fa-solid {{ $item['icon'] ?? 'fa-industry' }}"></i>
                                    </div>
                                @endif
                                <div class="relative z-10 flex items-center gap-4">
                                    <div
                                        class="flex size-16 items-center justify-center rounded bg-white text-3xl text-primary-dark shadow-lg">
                                        <i class="fa-solid {{ $item['icon'] ?? 'fa-industry' }}"></i>
                                    </div>
                                    <div class="text-sm font-extrabold uppercase tracking-wide text-red-100">
                                        {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                            <div class="p-7">
                                <h3 class="text-xl font-extrabold leading-tight text-slate-950">{{ $item['title'] ?? '' }}
                                </h3>
                                <p class="mt-4 text-sm leading-7 text-slate-600">{{ $item['description'] ?? '' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-slate-50 pb-16">
        <div class="mx-auto max-w-7xl px-4">
            <div class="rounded bg-white p-8 shadow ring-1 ring-slate-200">
                <x-home.section-title :title="$certifications['title'] ?? 'Certificates'" compact />
                <div class="mt-8 grid gap-4 text-center sm:grid-cols-2 md:grid-cols-4">
                    @foreach ($certifications['certificates'] ?? [] as $cert)
                        @php
                            $certName = is_array($cert) ? $cert['name'] ?? '' : $cert;
                            $certImage = is_array($cert) ? $cert['image'] ?? null : null;
                            $certPdf = is_array($cert) ? $cert['pdf'] ?? null : null;
                        @endphp
                        <div
                            class="overflow-hidden rounded border border-slate-200 bg-white text-sm font-extrabold text-primary">
                            @if ($certImage)
                                <img src="{{ $certImage }}" alt="{{ $certName }}"
                                    class="h-36 w-full object-contain bg-slate-50 p-4">
                            @endif
                            <div class="p-4">
                                @if ($certPdf)
                                    <a href="{{ $certPdf }}" target="_blank" rel="noopener"
                                        class="hover:text-primary-dark">{{ $certName }}</a>
                                @else
                                    {{ $certName }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 grid gap-4 text-center sm:grid-cols-2 md:grid-cols-4">
                    @foreach ($certifications['items'] ?? [] as $item)
                        <div>
                            <div class="text-4xl font-extrabold text-primary-dark">{{ $item['value'] ?? '' }}</div>
                            <div class="mt-2 text-xs text-slate-600">{{ $item['label'] ?? '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    @if (!empty($advantages['items']))
        <section class="bg-[#17110a] py-18 text-white">
            <div class="mx-auto max-w-7xl px-4">
                <div class="max-w-3xl">
                    <p class="text-sm font-extrabold uppercase text-red-200">{{ $advantages['eyebrow'] ?? '' }}</p>
                    <h2 class="mt-3 text-4xl font-extrabold leading-tight">{{ $advantages['title'] ?? '' }}</h2>
                    @if (!empty($advantages['description']))
                        <p class="mt-4 leading-7 text-slate-300">{{ $advantages['description'] }}</p>
                    @endif
                </div>

                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($advantages['items'] as $item)
                        <article
                            class="group relative overflow-hidden rounded bg-white/5 p-7 ring-1 ring-white/10 transition hover:-translate-y-1 hover:bg-white hover:text-slate-950 hover:shadow-2xl">
                            <div
                                class="absolute -right-8 -top-8 text-8xl text-white/5 transition group-hover:text-primary/10">
                                <i class="fa-solid {{ $item['icon'] ?? 'fa-shield-halved' }}"></i>
                            </div>
                            <div
                                class="relative z-10 flex size-14 items-center justify-center rounded bg-primary-dark text-2xl text-white shadow-lg">
                                <i class="fa-solid {{ $item['icon'] ?? 'fa-shield-halved' }}"></i>
                            </div>
                            <h3 class="relative z-10 mt-8 font-extrabold text-white group-hover:text-slate-950">
                                {{ $item['title'] ?? '' }}</h3>
                            <p class="relative z-10 mt-3 text-sm leading-6 text-slate-300 group-hover:text-slate-600">
                                {{ $item['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 text-center">
            <p class="text-sm font-extrabold uppercase text-primary-dark">{{ $customers['eyebrow'] ?? '' }}</p>
            <h2 class="mt-3 text-3xl font-extrabold uppercase text-[#17110a] md:text-4xl">{{ $customers['title'] ?? '' }}
            </h2>
            <div class="mx-auto mt-4 h-1 w-16 rounded bg-primary-dark"></div>
            <p class="mx-auto mt-5 max-w-2xl leading-7 text-slate-600">{{ $customers['description'] ?? '' }}</p>
        </div>

        <div class="mt-10">
            <x-home.marquee :items="$customers['items'] ?? []" />
        </div>
    </section>

    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4">
            <div class="mb-10 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-extrabold uppercase text-primary-dark">{{ $newsSection['eyebrow'] ?? '' }}</p>
                    <h2 class="mt-3 text-4xl font-extrabold text-slate-950">{{ $newsSection['title'] ?? '' }}</h2>
                    <p class="mt-4 max-w-3xl leading-7 text-slate-600">{{ $newsSection['description'] ?? '' }}</p>
                </div>
                <a href="{{ $newsIndexUrl }}"
                    class="inline-flex items-center gap-2 rounded border border-slate-300 px-5 py-3 text-sm font-bold text-slate-900 hover:border-primary hover:bg-primary hover:text-white">
                    {{ $newsSection['button_label'] ?? __('ui.actions.view_all') }}
                    <i class="fa-solid fa-arrow-right-long"></i>
                </a>
            </div>

            @if ($homeNews->count())
                <div class="grid gap-6 md:grid-cols-3">
                    @foreach ($homeNews as $post)
                        @php($postImage = $post->translation?->getFirstMediaUrl('thumbnail') ?: $post->translation?->getFirstMediaUrl('hero'))
                        <a href="{{ $post->translation?->public_url }}"
                            class="group block overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            <div class="overflow-hidden">
                                @if ($postImage)
                                    <img src="{{ $postImage }}" alt="{{ $post->translation?->title }}"
                                        class="h-56 w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div
                                        class="flex h-56 items-center justify-center bg-[linear-gradient(135deg,rgba(23,17,10,1)_0%,rgba(159,15,22,.9)_48%,rgba(242,211,0,.74)_100%)] text-5xl text-white">
                                        <i class="fa-solid fa-newspaper"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-6">
                                <div
                                    class="flex flex-wrap items-center gap-3 text-xs font-bold uppercase text-primary-dark">
                                    <span>{{ $post->category?->translation?->name ?: $newsSection['eyebrow'] ?? __('ui.common.news') }}</span>
                                    @if ($post->translation?->published_at)
                                        <span
                                            class="text-slate-400">{{ $post->translation->published_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                                <h3
                                    class="mt-3 min-h-16 text-xl font-extrabold leading-tight text-slate-950 transition group-hover:text-primary-dark">
                                    {{ $post->translation?->title }}</h3>
                                <p class="mt-3 min-h-20 text-sm leading-6 text-slate-600">
                                    {{ \Illuminate\Support\Str::limit($post->translation?->description, 135) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="relative overflow-hidden bg-primary-dark py-16 text-white">
        <div class="absolute inset-0 bg-cover bg-center opacity-35"
            style="background-image: url('{{ $ctaBackground }}')"></div>
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(23,17,10,.92),rgba(159,15,22,.78))]"></div>
        <div class="relative mx-auto flex max-w-7xl flex-col gap-6 px-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold">{{ $cta['title'] ?? '' }}</h2>
                <p class="mt-3 max-w-3xl text-red-100">{{ $cta['description'] ?? '' }}</p>
            </div>
            <a href="{{ $cta['button_url'] ?? $contactPageUrl }}"
                class="shrink-0 rounded border border-white/70 px-7 py-3 text-sm font-bold text-white hover:bg-white hover:text-primary-dark">
                {{ $cta['button_label'] ?? __('ui.actions.contact_consulting') }}
                <i class="fa-solid fa-arrow-right-long ml-2"></i>
            </a>
        </div>
    </section>
@endsection
