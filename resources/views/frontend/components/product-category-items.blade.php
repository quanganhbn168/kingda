@foreach ($categories as $category)
    @php($isActive = $activeCategory?->id === $category->id)
    @php($shouldExpand = $isActive || in_array($category->id, $activeCategoryAncestorIds ?? [], true))

    <div class="{{ ($level ?? 0) > 0 ? 'ml-4 border-l border-slate-200 pl-3' : '' }}">
        <a href="{{ $category->translation?->public_url }}"
            class="group flex items-center gap-3 rounded border px-4 py-3 text-sm transition {{ $isActive ? 'border-primary bg-primary text-white shadow-lg shadow-red-900/15' : 'border-slate-200 text-slate-800 hover:border-red-100 hover:bg-red-50 hover:text-primary' }}">
            <span class="min-w-0 flex-1">
                <span class="block font-extrabold leading-5">{{ $category->translation?->name }}</span>
            </span>
        </a>

        @if (($level ?? 0) === 0 && $category->children->isNotEmpty() && $shouldExpand)
            <div class="mt-2 space-y-2">
                @include('frontend.components.product-category-items', [
                    'categories' => $category->children,
                    'activeCategory' => $activeCategory,
                    'activeCategoryAncestorIds' => $activeCategoryAncestorIds ?? [],
                    'level' => ($level ?? 0) + 1,
                ])
            </div>
        @endif
    </div>
@endforeach
