@props([
    'items' => collect(),
])

@php
    $marqueeItems = collect($items)->filter(fn ($item) => filled($item->name ?? null))->values();
@endphp

@if($marqueeItems->isNotEmpty())
    <div {{ $attributes->merge(['class' => 'kingda-marquee relative overflow-hidden']) }}>
        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-20 bg-gradient-to-r from-white to-transparent"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-20 bg-gradient-to-l from-white to-transparent"></div>

        <div class="kingda-marquee-track flex gap-5 py-2">
            @foreach($marqueeItems->concat($marqueeItems) as $item)
                @php($itemUrl = $item->url ?? null)
                <div class="flex h-24 min-w-[12rem] shrink-0 items-center justify-center rounded bg-white px-8 text-center shadow ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-lg">
                    @if($itemUrl)
                        <a href="{{ $itemUrl }}" class="flex h-full w-full items-center justify-center" aria-label="{{ $item->name ?? '' }}">
                    @endif

                    @if(filled($item->logo))
                        <img src="{{ $item->logo }}" alt="{{ $item->name ?? '' }}" class="max-h-12 max-w-36 object-contain">
                    @else
                        <span class="text-xl font-extrabold text-slate-800">{{ $item->name ?? '' }}</span>
                    @endif

                    @if($itemUrl)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
