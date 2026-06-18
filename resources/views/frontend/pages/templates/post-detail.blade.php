@extends('layouts.master')

@section('content')
    @php
        use Illuminate\Support\Str;

        $locale = app()->getLocale();
        $homeUrl = url($locale === 'vi' ? '/' : '/' . $locale);
        $listingUrl = url($locale === 'vi' ? '/tin-tuc' : '/' . $locale . '/news');
        $categoryTranslation = $categoryTranslation ?? (
            $post->category?->relationLoaded('translations')
                ? $post->category->translations->firstWhere('locale', $locale)
                : $post->category?->translationFor($locale)->first()
        );
        $categoryUrl = $categoryUrl ?? (
            $categoryTranslation?->slug
                ? url($locale === 'vi' ? '/tin-tuc/' . $categoryTranslation->slug : '/' . $locale . '/news/' . $categoryTranslation->slug)
                : null
        );
        $hero = $translation->getFirstMediaUrl('hero') ?: $translation->getFirstMediaUrl('thumbnail');
        $categoryName = $categoryTranslation?->name ?: __('ui.common.news');
        $publishedAt = $translation->published_at;
        $plainText = trim(strip_tags((string) $content));
        $wordCount = str_word_count(Str::ascii($plainText));
        $readingMinutes = max(1, (int) ceil($wordCount / 220));
    @endphp

    <section class="relative overflow-hidden bg-[#17110a] py-12 text-white md:py-16">
        <div class="absolute inset-0">
            @if($hero)
                <img src="{{ $hero }}" alt="{{ $translation->title }}" class="h-full w-full object-cover opacity-35">
            @else
                <div class="h-full w-full bg-[linear-gradient(135deg,#17110a,#8b1118_55%,#24130d)]"></div>
            @endif
            <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(23,17,10,.92),rgba(23,17,10,.72))]"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-bold text-red-100/85">
                <a href="{{ $homeUrl }}" class="transition hover:text-white">{{ __('ui.common.home') }}</a>
                <span>/</span>
                <a href="{{ $listingUrl }}" class="transition hover:text-white">{{ __('ui.common.news') }}</a>
                @if($categoryTranslation)
                    <span>/</span>
                    <a href="{{ $categoryUrl ?: $listingUrl }}" class="transition hover:text-white">{{ $categoryTranslation->name }}</a>
                @endif
            </nav>
        </div>
    </section>

    <section class="bg-slate-100 py-12 md:py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <main class="min-w-0">
                <article class="overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                    <header class="px-6 py-7 md:px-9 md:py-9">
                        <div class="flex flex-wrap items-center gap-3 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                            <span class="text-primary">{{ $categoryName }}</span>
                            @if($publishedAt)
                                <span>{{ $publishedAt->format('d/m/Y') }}</span>
                            @endif
                            <span>{{ $readingMinutes }} phút đọc</span>
                            @if($post->author?->name)
                                <span>{{ $post->author->name }}</span>
                            @endif
                        </div>

                        <h1 class="mt-4 max-w-4xl text-3xl font-black leading-tight text-slate-950 md:text-5xl">{{ $translation->title }}</h1>

                        @if($translation->description)
                            <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600 md:text-lg">{{ $translation->description }}</p>
                        @endif
                    </header>

                    @if($hero)
                        <div class="px-6 md:px-9">
                            <img src="{{ $hero }}" alt="{{ $translation->title }}" class="aspect-[16/8] w-full rounded object-cover">
                        </div>
                    @endif

                    @if($content)
                        <div class="kd-article-content scroll-smooth px-6 py-8 md:px-9">{!! $content !!}</div>
                    @else
                        <div class="px-6 py-8 md:px-9">
                            <p class="text-base leading-8 text-slate-600">{{ $translation->description }}</p>
                        </div>
                    @endif
                </article>
            </main>

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                @if(! empty($toc))
                    <div class="rounded bg-white p-5 shadow ring-1 ring-slate-200">
                        <h2 class="text-sm font-black uppercase tracking-wide text-slate-950">{{ __('ui.common.table_of_contents') }}</h2>
                        <nav class="mt-4 space-y-2">
                            @foreach($toc as $item)
                                <a href="#{{ $item['id'] }}"
                                    class="block rounded px-3 py-2 text-sm font-bold leading-6 text-slate-600 transition hover:bg-red-50 hover:text-primary {{ $item['level'] === 3 ? 'ml-3' : '' }}">
                                    {{ $item['title'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif

                <div class="rounded bg-white p-5 shadow ring-1 ring-slate-200">
                    <p class="text-xs font-extrabold uppercase tracking-wide text-primary">Thông tin bài viết</p>
                    <div class="mt-4 space-y-3 text-sm leading-6 text-slate-600">
                        <p><strong class="text-slate-950">Chuyên mục:</strong> {{ $categoryName }}</p>
                        @if($publishedAt)
                            <p><strong class="text-slate-950">Ngày đăng:</strong> {{ $publishedAt->format('d/m/Y') }}</p>
                        @endif
                        <p><strong class="text-slate-950">Thời lượng:</strong> {{ $readingMinutes }} phút đọc</p>
                        @if($post->author?->name)
                            <p><strong class="text-slate-950">Tác giả:</strong> {{ $post->author->name }}</p>
                        @endif
                    </div>
                </div>

                <a href="{{ $listingUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded bg-primary px-5 py-3 text-sm font-extrabold text-white transition hover:bg-primary-dark">
                    Xem tất cả tin tức
                    <i class="fa-solid fa-arrow-right-long"></i>
                </a>
            </aside>
        </div>

        @if($relatedPosts->count())
            <div class="mx-auto mt-12 max-w-7xl px-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ __('ui.common.related_news') }}</p>
                        <h2 class="mt-2 text-3xl font-black text-slate-950">Bài viết khác</h2>
                    </div>
                    <a href="{{ $listingUrl }}" class="text-sm font-extrabold text-primary hover:text-primary-dark">Xem tất cả tin tức</a>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    @foreach($relatedPosts as $related)
                        @php
                            $relatedTranslation = $related->translation;
                            $relatedCategoryTranslation = $related->category?->translation;
                            $relatedImage = $relatedTranslation?->getFirstMediaUrl('thumbnail') ?: $relatedTranslation?->getFirstMediaUrl('hero');
                            $relatedUrl = $relatedTranslation?->slug
                                ? url($locale === 'vi'
                                    ? '/tin-tuc/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/')
                                    : '/' . $locale . '/news/' . collect([$relatedCategoryTranslation?->slug, $relatedTranslation->slug])->filter()->join('/'))
                                : '#';
                        @endphp
                        <a href="{{ $relatedUrl }}" class="group block overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            @if($relatedImage)
                                <img src="{{ $relatedImage }}" alt="{{ $relatedTranslation?->title }}" class="h-52 w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-52 w-full items-center justify-center bg-gradient-to-br from-slate-950 via-primary to-red-300 text-white">
                                    <div class="text-center">
                                        <i class="fa-solid fa-newspaper text-4xl"></i>
                                        <div class="mt-3 text-xs font-extrabold uppercase">Kingda</div>
                                    </div>
                                </div>
                            @endif
                            <div class="p-5">
                                <p class="text-xs font-extrabold uppercase tracking-wide text-primary">{{ $related->category?->translation?->name ?: __('ui.common.news') }}</p>
                                <h3 class="mt-2 text-lg font-black leading-snug text-slate-950 transition group-hover:text-primary">{{ $relatedTranslation?->title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit($relatedTranslation?->description, 115) }}</p>
                                <span class="mt-4 inline-flex items-center gap-2 text-sm font-extrabold text-primary">
                                    Đọc tiếp
                                    <i class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
