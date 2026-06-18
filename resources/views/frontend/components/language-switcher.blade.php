<div class="relative" x-data="{ open: false }">
    <button
        type="button"
        class="inline-flex items-center gap-2 rounded border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
        @click="open = !open"
        @click.outside="open = false"
    >
        @foreach($languageItems as $language)
            @if($language['is_active'])
                <span>{{ $language['label'] }}</span>
            @endif
        @endforeach

        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition
        class="absolute right-0 mt-2 w-44 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
    >
        @foreach($languageItems as $language)
            <a
                href="{{ $language['url'] }}"
                class="flex items-center justify-between rounded-xl px-3 py-2 text-sm hover:bg-slate-50 {{ $language['is_active'] ? 'font-bold text-slate-950' : 'text-slate-600' }}"
            >
                <span>{{ $language['name'] }}</span>
                <span>{{ $language['label'] }}</span>
            </a>
        @endforeach
    </div>
</div>
