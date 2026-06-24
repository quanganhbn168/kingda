@extends('layouts.master')

@section('content')
    @php
        use Illuminate\Support\Str;

        $locale = app()->getLocale();

        $homeUrl = url($locale === 'vi' ? '/' : '/' . $locale);
        $listingUrl = url($locale === 'vi' ? '/san-pham' : '/' . $locale . '/products');

        $categoryTranslation = $categoryTranslation ?? (
            $product->category?->relationLoaded('translations')
                ? $product->category->translations->firstWhere('locale', $locale)
                : $product->category?->translationFor($locale)->first()
        );

        $categoryUrl = $categoryUrl ?? (
            $categoryTranslation?->slug
                ? url($locale === 'vi'
                    ? '/san-pham/' . $categoryTranslation->slug
                    : '/' . $locale . '/products/' . $categoryTranslation->slug)
                : null
        );

        $mainImage = $product->displayImageUrl($translation);

        $blocks = is_array($translation->blocks) ? $translation->blocks : [];
        $specifications = is_array($translation->specifications) ? $translation->specifications : [];

        $applications = collect($blocks['applications'] ?? [])->filter()->values();
        $substrates = collect($blocks['substrates'] ?? [])->filter()->values();
        $features = collect($blocks['features'] ?? [])->filter()->values();

        $consultingInputs = collect($blocks['consulting_inputs'] ?? [])->filter()->values();

        if ($consultingInputs->isEmpty()) {
            $consultingInputs = collect(__('ui.product_detail.default_consulting_inputs'));
        }

        $storageNotes = collect($blocks['storage_notes'] ?? [])->filter()->values();

        $productFaq = collect($blocks['faq'] ?? [])
            ->filter(fn ($item): bool => is_array($item) && filled($item['question'] ?? null))
            ->values();

        $categoryName = $categoryTranslation?->name ?: __('ui.common.products');
        $relatedCount = $relatedProducts->count();

        $quickFacts = collect([
            ['label' => __('ui.product_detail.category'), 'value' => $categoryName],
            ['label' => __('ui.product_detail.sku'), 'value' => $product->sku],
            ['label' => __('ui.product_detail.main_application'), 'value' => $applications->isNotEmpty() ? Str::limit($applications->first(), 80) : null],
            ['label' => __('ui.product_detail.substrate'), 'value' => $substrates->isNotEmpty() ? $substrates->take(3)->implode(', ') : null],
        ])->filter(fn (array $item): bool => filled($item['value'] ?? null))->values();
    @endphp

    <div
        x-data="{ consultationOpen: @js($errors->getBag('productConsultation')->any()) }"
        @keydown.escape.window="consultationOpen = false"
    >
    @if(session('product_consultation_success'))
        <div class="mx-auto mt-5 max-w-7xl px-4">
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('product_consultation_success') }}
            </div>
        </div>
    @endif

    <section class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-6">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold text-slate-500">
                <a href="{{ $homeUrl }}" class="transition hover:text-primary">
                    {{ __('ui.common.home') }}
                </a>

                <span class="text-slate-300">/</span>

                <a href="{{ $listingUrl }}" class="transition hover:text-primary">
                    {{ __('ui.common.products') }}
                </a>

                @if($categoryTranslation)
                    <span class="text-slate-300">/</span>

                    <a href="{{ $categoryUrl ?: $listingUrl }}" class="transition hover:text-primary">
                        {{ $categoryTranslation->name }}
                    </a>
                @endif

                <span class="text-slate-300">/</span>

                <span class="max-w-[260px] truncate text-slate-900">
                    {{ $translation->name }}
                </span>
            </nav>
        </div>
    </section>

    <section class="bg-slate-50 pb-14 md:pb-20">
        <div class="mx-auto max-w-7xl px-4">
            <div class="grid gap-8 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-slate-200 md:p-6 lg:grid-cols-[46%_1fr] lg:p-8">
                <div class="overflow-hidden rounded-2xl bg-slate-100">
                    @if($mainImage)
                        <img src="{{ $mainImage }}" alt="{{ $translation->name }}" class="aspect-[1/1] w-full object-cover">
                    @else
                        <div class="flex aspect-[4/3] w-full items-center justify-center bg-slate-100 text-slate-400">
                            <div class="text-center">
                                <i class="fa-solid fa-image text-5xl"></i>
                                <div class="mt-3 text-sm font-bold">{{ __('ui.product_detail.no_product_image') }}</div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex flex-col justify-center">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex rounded-full bg-red-50 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-primary">
                            {{ $categoryName }}
                        </span>

                        @if($product->sku)
                            <span class="inline-flex rounded-full bg-slate-100 px-4 py-2 text-xs font-extrabold uppercase tracking-wide text-slate-600">
                                {{ __('ui.product_detail.sku') }}: {{ $product->sku }}
                            </span>
                        @endif
                    </div>

                    <h1 class="mt-5 text-3xl font-black leading-tight text-slate-950 md:text-5xl">
                        {{ $translation->name }}
                    </h1>

                    @if($translation->description)
                        <p class="mt-5 text-base leading-8 text-slate-600 md:text-lg">
                            {{ $translation->description }}
                        </p>
                    @endif

                    @if($quickFacts->isNotEmpty())
                        <div class="mt-7 grid gap-3 sm:grid-cols-2">
                            @foreach($quickFacts as $fact)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">
                                        {{ $fact['label'] }}
                                    </div>

                                    <div class="mt-1 text-sm font-extrabold leading-6 text-slate-900">
                                        {{ $fact['value'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-8 flex flex-wrap gap-3">
                        <button type="button" @click="consultationOpen = true" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                            {{ __('ui.actions.contact_consulting') }}
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </button>

                        <a href="#thong-so" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-extrabold text-slate-800 transition hover:border-primary hover:text-primary">
                            {{ __('ui.product_detail.view_specifications') }}
                            <i class="fa-solid fa-table-list"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                <main class="min-w-0 space-y-8">

                    @if($applications->isNotEmpty())
                        <section id="ung-dung" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                        {{ __('ui.product_detail.applications') }}
                                    </p>

                                    <h2 class="mt-2 text-3xl font-black text-slate-950">
                                        {{ __('ui.product_detail.suitable_applications') }}
                                    </h2>
                                </div>

                                <p class="max-w-xl text-sm leading-6 text-slate-500">
                                    {{ __('ui.product_detail.applications_description') }}
                                </p>
                            </div>

                            <div class="mt-7 grid gap-4 md:grid-cols-2">
                                @foreach($applications as $item)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                        <div class="flex gap-4">
                                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-white text-primary shadow-sm">
                                                <i class="fa-solid fa-check"></i>
                                            </span>

                                            <p class="text-sm leading-7 text-slate-700">
                                                {{ $item }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($substrates->isNotEmpty())
                        <section id="nen-vat-lieu" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                        {{ __('ui.product_detail.substrates') }}
                                    </p>

                                    <h2 class="mt-2 text-3xl font-black text-slate-950">
                                        {{ __('ui.product_detail.compatibility') }}
                                    </h2>
                                </div>

                                <p class="max-w-xl text-sm leading-6 text-slate-500">
                                    {{ __('ui.product_detail.compatibility_note') }}
                                </p>
                            </div>

                            <div class="mt-7 flex flex-wrap gap-3">
                                @foreach($substrates as $item)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-extrabold text-slate-700">
                                        <i class="fa-solid fa-circle-check text-primary"></i>
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($features->isNotEmpty())
                        <section id="dac-tinh" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                        {{ __('ui.product_detail.features') }}
                                    </p>

                                    <h2 class="mt-2 text-3xl font-black text-slate-950">
                                        {{ __('ui.product_detail.technical_features') }}
                                    </h2>
                                </div>
                            </div>

                            <div class="mt-7 grid gap-4">
                                @foreach($features as $item)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="flex gap-4">
                                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-primary">
                                                <i class="fa-solid fa-star"></i>
                                            </span>

                                            <p class="text-sm leading-7 text-slate-700">
                                                {{ $item }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section id="thong-so" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                    {{ __('ui.product_detail.specifications') }}
                                </p>

                                <h2 class="mt-2 text-3xl font-black text-slate-950">
                                    {{ __('ui.product_detail.reference_specifications') }}
                                </h2>
                            </div>

                            <p class="max-w-xl text-sm leading-6 text-slate-500">
                                {{ __('ui.product_detail.specifications_note') }}
                            </p>
                        </div>

                        @if(! empty($specifications))
                            <dl class="mt-7 overflow-hidden rounded-2xl border border-slate-200">
                                @foreach($specifications as $key => $value)
                                    <div class="grid gap-2 border-b border-slate-200 bg-white p-4 last:border-b-0 md:grid-cols-3">
                                        <dt class="font-extrabold text-slate-900">
                                            {{ $key }}
                                        </dt>

                                        <dd class="leading-6 text-slate-600 md:col-span-2">
                                            {{ $value }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <div class="mt-7 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-7 text-slate-500">
                                {{ __('ui.product_detail.specifications_empty') }}
                            </div>
                        @endif
                    </section>

                    @if($translation->content)
                        <section id="noi-dung" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                {{ __('ui.product_detail.details') }}
                            </p>

                            <div class="kd-product-content mt-5">
                                {!! $translation->content !!}
                            </div>
                        </section>
                    @endif

                    @if($storageNotes->isNotEmpty())
                        <section id="bao-quan" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                {{ __('ui.product_detail.storage') }}
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-slate-950">
                                {{ __('ui.product_detail.storage_notes') }}
                            </h2>

                            <div class="mt-7 grid gap-4 md:grid-cols-2">
                                @foreach($storageNotes as $note)
                                    <div class="flex gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-slate-700">
                                        <i class="fa-solid fa-circle-check mt-1 text-amber-600"></i>
                                        <span>{{ $note }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($productFaq->isNotEmpty())
                        <section id="faq" class="kd-detail-section rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200 md:p-8">
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                FAQ
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-slate-950">
                                {{ __('ui.product_detail.faq') }}
                            </h2>

                            <div class="mt-7 divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200">
                                @foreach($productFaq as $item)
                                    <details class="group bg-white p-5 open:bg-slate-50">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-base font-black text-slate-950">
                                            <span>{{ $item['question'] ?? '' }}</span>
                                            <i class="fa-solid fa-chevron-down text-primary transition group-open:rotate-180"></i>
                                        </summary>

                                        <p class="mt-4 text-sm leading-7 text-slate-600">
                                            {{ $item['answer'] ?? '' }}
                                        </p>
                                    </details>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </main>

                <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-primary">
                            {{ __('ui.product_detail.need_consulting') }}
                        </div>

                        <h2 class="mt-2 text-2xl font-black leading-tight text-slate-950">
                            {{ __('ui.product_detail.send_product_request') }}
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            {{ __('ui.product_detail.consulting_description') }}
                        </p>

                        <button type="button" @click="consultationOpen = true" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                            {{ __('ui.actions.contact_consulting') }}
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </button>
                    </div>

                    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-slate-400">
                            {{ __('ui.product_detail.information_to_prepare') }}
                        </div>

                        <div class="mt-5 space-y-3">
                            @foreach($consultingInputs as $item)
                                <div class="flex gap-3 rounded-2xl bg-slate-50 p-4">
                                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-black text-white">
                                        {{ $loop->iteration }}
                                    </span>

                                    <p class="text-sm leading-6 text-slate-600">
                                        {{ $item }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>


                </aside>
            </div>

            @if($relatedCount)
                <section class="mt-14">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                {{ __('ui.common.related_products') }}
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-slate-950">
                                {{ __('ui.common.you_may_also_like') }}
                            </h2>

                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                                {{ __('ui.common.discover_more_products') }}
                            </p>
                        </div>

                        <a href="{{ $listingUrl }}" class="inline-flex items-center gap-2 text-sm font-extrabold text-primary hover:text-primary-dark">
                            {{ __('ui.common.view_all_products') }}
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </a>
                    </div>

                    <div class="mt-6 grid gap-5 md:grid-cols-3">
                        @foreach($relatedProducts as $related)
                            @php
                                $relatedTranslation = $related->translation;
                                $relatedCategoryTranslation = $related->category?->translation;
                                $relatedImage = $related->displayImageUrl($relatedTranslation);

                                $relatedUrl = $relatedTranslation?->slug
                                    ? url($locale === 'vi'
                                        ? '/san-pham/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/')
                                        : '/' . $locale . '/products/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/'))
                                    : '#';
                            @endphp

                            <a href="{{ $relatedUrl }}" class="group overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                                @if($relatedImage)
                                    <img src="{{ $relatedImage }}" alt="{{ $relatedTranslation?->name }}" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-52 w-full items-center justify-center bg-slate-100 text-slate-400">
                                        <div class="text-center">
                                            <i class="fa-solid fa-image text-4xl"></i>
                                            <div class="mt-3 text-xs font-extrabold uppercase">
                                                {{ __('ui.common.no_image') }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="p-5">
                                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">
                                        {{ $related->category?->translation?->name ?: __('ui.common.products') }}
                                    </p>

                                    <h3 class="mt-2 text-lg font-black leading-snug text-slate-950">
                                        {{ $relatedTranslation?->name }}
                                    </h3>

                                    @if($relatedTranslation?->description)
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ Str::limit($relatedTranslation->description, 115) }}
                                        </p>
                                    @endif

                                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-primary">
                                        {{ __('ui.actions.view_detail') }}
                                        <i class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
    <template x-teleport="body">
        <div
            x-cloak
            x-show="consultationOpen"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-slate-950/65 p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="product-consultation-title"
            @click.self="consultationOpen = false"
        >
            <div x-show="consultationOpen" x-transition class="relative my-auto w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl md:p-8">
                <button type="button" @click="consultationOpen = false" class="absolute right-4 top-4 flex size-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-950" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-primary">Kingda</p>
                <h2 id="product-consultation-title" class="mt-2 pr-10 text-2xl font-black text-slate-950">{{ __('ui.product_consultation.title') }}</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ __('ui.product_consultation.description') }}</p>

                @if($errors->getBag('productConsultation')->any())
                    <div role="alert" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-extrabold">{{ __('ui.product_consultation.validation_intro') }}</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->getBag('productConsultation')->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ $locale === 'vi' ? route('products.consultation.store', ['product' => $product]) : route('localized.products.consultation.store', ['locale' => $locale, 'product' => $product]) }}" class="mt-6 grid gap-4">
                    @csrf

                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="product-consultation-website">Website</label>
                        <input id="product-consultation-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div>
                        <label for="consultation-product" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.product_consultation.interested_product') }}</label>
                        <input id="consultation-product" type="text" value="{{ $translation->name }}" readonly class="w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="consultation-name" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.contact.name') }} *</label>
                            <input id="consultation-name" type="text" name="name" value="{{ old('name') }}" maxlength="100" required autocomplete="name" class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->getBag('productConsultation')->has('name') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('name', 'productConsultation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="consultation-phone" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.contact.phone') }} *</label>
                            <input id="consultation-phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" required autocomplete="tel" class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->getBag('productConsultation')->has('phone') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('phone', 'productConsultation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="consultation-email" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.contact.email') }}</label>
                            <input id="consultation-email" type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" class="w-full rounded-xl border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->getBag('productConsultation')->has('email') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('email', 'productConsultation') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="consultation-company" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.contact.company') }}</label>
                            <input id="consultation-company" type="text" name="company" value="{{ old('company') }}" maxlength="150" autocomplete="organization" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                        </div>
                    </div>

                    <div>
                        <label for="consultation-message" class="mb-1.5 block text-sm font-bold text-slate-700">{{ __('ui.contact.message') }}</label>
                        <textarea id="consultation-message" name="message" rows="4" maxlength="5000" placeholder="{{ __('ui.product_consultation.message_placeholder') }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                        {{ __('ui.product_consultation.submit') }}
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </template>
    </div>
@endsection
