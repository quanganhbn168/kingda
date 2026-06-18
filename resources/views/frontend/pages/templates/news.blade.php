@extends('layouts.master')

@section('content')
    <section class="bg-white py-16">
        <div class="mx-auto max-w-7xl px-4">
            <p class="text-sm font-extrabold uppercase text-primary">{{ __('ui.common.news') }}</p>
            <h1 class="mt-3 text-4xl font-extrabold text-slate-950 md:text-5xl">{{ $translation->headline ?: $translation->title }}</h1>
            <p class="mt-5 max-w-3xl leading-7 text-slate-600">{{ $translation->subheadline ?: $translation->excerpt }}</p>
        </div>
    </section>

    <section class="bg-slate-100 pb-16">
        <div class="mx-auto max-w-7xl px-4">
            @if($categories->count())
                <div class="-mt-6 mb-10 flex flex-wrap gap-2 rounded bg-white p-3 shadow ring-1 ring-slate-200">
                    <a href="{{ url(app()->getLocale() === 'vi' ? '/tin-tuc' : '/' . app()->getLocale() . '/news') }}"
                        class="rounded px-4 py-2 text-sm font-bold {{ $activeCategory ? 'text-slate-700 hover:bg-slate-100' : 'bg-primary text-white' }}">
                        {{ __('ui.common.all') }}
                    </a>
                    @foreach($categories as $category)
                        @php($isActive = $activeCategory?->id === $category->id)
                        <a href="{{ $category->translation?->public_url }}"
                            class="rounded px-4 py-2 text-sm font-bold {{ $isActive ? 'bg-primary text-white' : 'text-slate-700 hover:bg-slate-100 hover:text-primary' }}">
                            {{ $category->translation?->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if($posts->count())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        @php($postImage = $post->translation?->getFirstMediaUrl('thumbnail') ?: $post->translation?->getFirstMediaUrl('hero'))
                        <a href="{{ $post->translation?->public_url }}" class="group block overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            @if($postImage)
                                <img src="{{ $postImage }}" alt="{{ $post->translation?->title }}" class="h-56 w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-56 items-center justify-center bg-gradient-to-br from-slate-950 via-primary to-red-300 text-5xl text-white">
                                    <i class="fa-solid fa-newspaper"></i>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center gap-3 text-xs font-bold uppercase text-primary">
                                    <span>{{ $post->category?->translation?->name ?: __('ui.common.news') }}</span>
                                    @if($post->translation?->published_at)
                                        <span class="text-slate-400">{{ $post->translation->published_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                                <h2 class="mt-3 min-h-16 text-xl font-extrabold leading-tight text-slate-950 transition group-hover:text-primary">{{ $post->translation?->title }}</h2>
                                <p class="mt-3 min-h-20 text-sm leading-6 text-slate-600">{{ \Illuminate\Support\Str::limit($post->translation?->description, 140) }}</p>
                                <span class="mt-5 inline-flex items-center gap-2 text-sm font-extrabold text-primary">
                                    {{ __('ui.actions.read_more') }}
                                    <i class="fa-solid fa-arrow-right-long transition group-hover:translate-x-1"></i>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="rounded bg-white p-10 text-center shadow ring-1 ring-slate-200">
                    <div class="text-4xl text-primary"><i class="fa-solid fa-newspaper"></i></div>
                    <h3 class="mt-4 text-xl font-extrabold text-slate-950">{{ __('ui.empty.news_title') }}</h3>
                    <p class="mt-2 text-slate-600">{{ __('ui.empty.news_description') }}</p>
                </div>
            @endif
        </div>
    </section>
@endsection
