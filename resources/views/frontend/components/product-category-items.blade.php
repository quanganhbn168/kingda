@foreach ($categories as $category)
    @php($isActive = $activeCategory?->id === $category->id)

    <div class="{{ ($level ?? 0) > 0 ? 'ml-4 border-l border-slate-200 pl-3' : '' }}">
        <a href="{{ $category->translation?->public_url }}"
            class="group flex items-center gap-3 rounded border px-4 py-3 text-sm transition {{ $isActive ? 'border-primary bg-primary text-white shadow-lg shadow-red-900/15' : 'border-slate-200 text-slate-800 hover:border-red-100 hover:bg-red-50 hover:text-primary' }}">
            <span class="min-w-0 flex-1">
                <span class="block font-extrabold leading-5">{{ $category->translation?->name }}</span>
            </span>
            <span
                class="rounded-full px-2 py-1 text-xs font-extrabold {{ $isActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-white group-hover:text-primary' }}">{{ $category->products_count }}</span>
        </a>

        @if ($category->children->isNotEmpty())
            <div class="mt-2 space-y-2">
                @include('frontend.components.product-category-items', [
                    'categories' => $category->children,
                    'activeCategory' => $activeCategory,
                    'level' => ($level ?? 0) + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
