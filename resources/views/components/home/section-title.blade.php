@props([
    'title',
    'compact' => false,
])

<div class="{{ $compact ? 'text-center' : 'mx-auto max-w-7xl px-4 text-center' }}">
    <h2 class="text-2xl font-extrabold uppercase tracking-wide text-[#17110a]">
        {{ $title }}
    </h2>
    <div class="mx-auto mt-3 h-1 w-12 rounded bg-primary"></div>
</div>
