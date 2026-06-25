@extends('layouts.master')

@section('content')
    @php($hero = method_exists($translation, 'getFirstMediaUrl') ? ($translation->getFirstMediaUrl('hero') ?: $translation->getFirstMediaUrl('thumbnail')) : null)

    <section class="relative bg-[#17110a] py-16 text-white">
        @if($hero)
            <img src="{{ $hero }}" alt="{{ $translation->title }}" class="absolute inset-0 h-full w-full object-cover opacity-35">
        @endif
        <div class="relative mx-auto max-w-7xl px-4">
            <p class="text-sm font-extrabold uppercase text-red-200">{{ __('ui.common.kingda') }}</p>
            <h1 class="mt-3 text-4xl font-extrabold md:text-5xl">{{ $translation->headline ?: $translation->title }}</h1>
            <p class="mt-5 max-w-3xl leading-7 text-slate-200">{{ $translation->subheadline ?: $translation->excerpt }}</p>
        </div>
    </section>

    @if(! empty($stats))
        <section class="bg-white py-10">
            <div class="mx-auto grid max-w-7xl gap-4 px-4 md:grid-cols-4">
                @foreach($stats as $stat)
                    <div class="rounded border border-slate-200 p-5">
                        <div class="text-3xl font-extrabold text-primary">{{ $stat->value ?? '' }}</div>
                        <div class="mt-2 font-bold text-slate-900">{{ $stat->label ?? '' }}</div>
                        @if(filled($stat->description))
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $stat->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="bg-slate-100 py-16">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($industries as $industry)
                @php($image = $industry->getFirstMediaUrl('thumbnail') ?: $industry->getFirstMediaUrl('hero'))
                <a href="{{ $industry->translation?->public_url }}" class="group overflow-hidden rounded bg-white shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-xl">
                    @if($image)
                        <img src="{{ $image }}" alt="{{ $industry->translation?->title }}" class="h-56 w-full object-cover">
                    @endif
                    <div class="p-6">
                        <div class="text-3xl text-primary">
                            <i class="fa-solid {{ $industry->icon ?: 'fa-industry' }}"></i>
                        </div>
                        <h2 class="mt-4 text-xl font-extrabold text-slate-950">{{ $industry->translation?->title }}</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $industry->translation?->description }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
