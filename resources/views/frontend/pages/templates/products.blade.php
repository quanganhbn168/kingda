@extends('layouts.master')

@section('content')
    <section class="bg-[#17110a] py-16 text-white">
        <div class="mx-auto max-w-7xl px-4">
            <p class="text-sm font-extrabold uppercase text-red-200">{{ __('ui.common.kingda') }}</p>
            <h1 class="mt-3 text-4xl font-extrabold md:text-5xl">{{ $translation->headline ?: $translation->title }}</h1>
            <p class="mt-5 max-w-3xl leading-7 text-slate-300">{{ $translation->subheadline ?: $translation->excerpt }}</p>
        </div>
    </section>

    <section class="bg-slate-100 py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="overflow-hidden rounded bg-white shadow-xl ring-1 ring-slate-200">
                    <div class="bg-[#17110a] px-5 py-5 text-white">
                        <h2 class="mt-1 text-lg font-extrabold">{{ __('ui.common.product_categories') }}</h2>
                    </div>

                    <div class="p-3">
                        <a href="{{ url(app()->getLocale() === 'vi' ? '/san-pham' : '/' . app()->getLocale() . '/products') }}"
                            class="group flex items-center gap-3 rounded px-4 py-3 text-sm font-extrabold transition {{ $activeCategory ? 'text-slate-800 hover:bg-red-50 hover:text-primary' : 'bg-primary text-white shadow-lg shadow-red-900/15' }}">
                            <span class="min-w-0 flex-1">{{ __('ui.common.all_products') }}</span>
                            <span
                                class="rounded-full px-2 py-1 text-xs {{ $activeCategory ? 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-primary' : 'bg-white/20 text-white' }}">{{ $totalProductsCount ?? $products->total() }}</span>
                        </a>

                        <div class="mt-3 space-y-2">
                            @include('frontend.components.product-category-items', [
                                'categories' => $categoryTree,
                                'activeCategory' => $activeCategory,
                                'level' => 0,
                            ])
                        </div>
                    </div>
                </div>
            </aside>
            <div>
                <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-950">
                            {{ $activeCategory?->translation?->name ?: __('ui.common.all_products') }}
                        </h2>
                        @if ($activeCategory?->translation?->description)
                            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                                {{ $activeCategory->translation->description }}</p>
                        @endif
                    </div>
                    <div class="text-sm font-bold text-slate-500">
                        {{ __('ui.common.product_count', ['count' => $products->total()]) }}</div>
                </div>

                @if ($products->count())
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            @php($productImage = $product->displayImageUrl())
                            <a href="{{ $product->translation?->public_url }}"
                                class="group block overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                                @if ($productImage)
                                    <img src="{{ $productImage }}" alt="{{ $product->translation?->name }}"
                                        class="h-52 w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div
                                        class="flex h-52 items-center justify-center bg-gradient-to-br from-white via-red-50 to-slate-200 text-5xl text-primary">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                @endif
                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span
                                            class="text-xs font-extrabold uppercase text-primary">{{ $product->category?->translation?->name }}</span>
                                        @if ($product->sku)
                                            <span class="text-xs font-bold text-slate-400">{{ $product->sku }}</span>
                                        @endif
                                    </div>
                                    <h3
                                        class="mt-3 min-h-14 text-lg font-extrabold text-slate-950 transition group-hover:text-primary">
                                        {{ $product->translation?->name }}</h3>
                                    <p class="mt-3 min-h-20 text-sm leading-6 text-slate-600">
                                        {{ \Illuminate\Support\Str::limit($product->translation?->description, 120) }}</p>
                                    <span class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-primary">
                                        {{ __('ui.actions.view_detail') }}
                                        <i class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="rounded bg-white p-10 text-center shadow ring-1 ring-slate-200">
                        <div class="text-4xl text-primary"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h3 class="mt-4 text-xl font-extrabold text-slate-950">{{ __('ui.empty.products_title') }}</h3>
                        <p class="mt-2 text-slate-600">{{ __('ui.empty.products_description') }}</p>
                    </div>
                @endif

                @if (filled($activeCategory?->translation?->content))
                    <article class="kd-article-content mt-10 rounded bg-white px-6 py-8 shadow ring-1 ring-slate-200 md:px-9">
                        {!! $activeCategory->translation->content !!}
                    </article>
                @endif
            </div>
        </div>
    </section>
@endsection
