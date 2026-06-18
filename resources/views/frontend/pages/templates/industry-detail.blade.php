@extends('layouts.master')

@section('content')
    @php
        $locale = app()->getLocale();
        $listingUrl = url($locale === 'vi' ? '/linh-vuc' : '/' . $locale . '/industries');
        $hero = $industry->getFirstMediaUrl('hero') ?: $industry->getFirstMediaUrl('thumbnail');
    @endphp

    <section class="relative bg-[#17110a] py-16 text-white">
        @if($hero)
            <img src="{{ $hero }}" alt="{{ $translation->title }}" class="absolute inset-0 h-full w-full object-cover opacity-35">
        @endif
        <div class="relative mx-auto max-w-7xl px-4">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-bold text-red-100/80">
                <a href="{{ url($locale === 'vi' ? '/' : '/' . $locale) }}" class="hover:text-white">{{ __('ui.common.home') }}</a>
                <span>/</span>
                <a href="{{ $listingUrl }}" class="hover:text-white">{{ __('ui.common.industries') }}</a>
            </nav>
            <div class="mt-8 text-4xl text-red-200">
                <i class="fa-solid {{ $industry->icon ?: 'fa-industry' }}"></i>
            </div>
            <h1 class="mt-4 text-4xl font-extrabold md:text-5xl">{{ $translation->title }}</h1>
            @if($translation->description)
                <p class="mt-5 max-w-3xl leading-7 text-slate-200">{{ $translation->description }}</p>
            @endif
        </div>
    </section>

    @if($translation->content)
        <section class="bg-slate-100 py-16">
            <div class="mx-auto max-w-4xl rounded bg-white p-6 shadow ring-1 ring-slate-200 md:p-8">
                <div class="prose max-w-none">
                    {!! $translation->content !!}
                </div>
            </div>
        </section>
    @endif
@endsection
