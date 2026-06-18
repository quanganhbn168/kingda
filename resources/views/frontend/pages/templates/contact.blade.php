@extends('layouts.master')

@section('content')
    <section class="bg-[#17110a] py-16 text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-[minmax(0,1fr)_24rem] lg:items-end">
            <div>
                <p class="text-sm font-extrabold uppercase text-red-200">{{ __('ui.common.kingda') }}</p>
                <h1 class="mt-3 text-4xl font-extrabold md:text-5xl">{{ $translation->headline ?: $translation->title }}</h1>
                <p class="mt-5 max-w-3xl leading-7 text-slate-300">{{ $translation->subheadline ?: $translation->excerpt }}</p>
            </div>
            <div class="rounded bg-white/10 p-6 ring-1 ring-white/15">
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded bg-primary text-xl text-white">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <div class="text-sm text-slate-300">{{ __('ui.contact.quick_consulting') }}</div>
                        <div class="text-xl font-extrabold">{{ $contactSettings->hotlines[0]['phone'] ?? $contactSettings->phones[0]['phone'] ?? 'kingdah@gmail.com' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-slate-100 py-16">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 lg:grid-cols-[minmax(0,1fr)_26rem]">
            <div class="rounded bg-white p-8 shadow ring-1 ring-slate-200">
                <h2 class="text-2xl font-extrabold text-slate-950">{{ __('ui.contact.request_title') }}</h2>
                <form class="mt-8 grid gap-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="text" name="name" placeholder="{{ __('ui.contact.name') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                        <input type="tel" name="phone" placeholder="{{ __('ui.contact.phone') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="email" name="email" placeholder="{{ __('ui.contact.email') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                        <input type="text" name="company" placeholder="{{ __('ui.contact.company') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                    </div>
                    <input type="text" name="subject" placeholder="{{ __('ui.contact.subject') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary">
                    <textarea name="message" rows="6" placeholder="{{ __('ui.contact.message') }}" class="rounded border border-slate-200 px-4 py-3 text-sm outline-none focus:border-primary"></textarea>
                    <button type="button" class="inline-flex w-fit items-center gap-2 rounded bg-primary px-6 py-3 text-sm font-extrabold text-white hover:bg-primary-dark">
                        {{ __('ui.actions.send') }}
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
            </div>

            <aside class="space-y-4">
                <div class="rounded bg-white p-6 shadow ring-1 ring-slate-200">
                    <h2 class="text-xl font-extrabold text-slate-950">{{ __('ui.contact.info_title') }}</h2>
                    <div class="mt-5 space-y-4 text-sm leading-6 text-slate-600">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-primary"></i>
                            <span>{{ $contactSettings->default_address }}</span>
                        </div>
                        <div class="flex gap-3">
                            <i class="fa-solid fa-envelope mt-1 text-primary"></i>
                            <span>{{ $contactSettings->emails[0]['email'] ?? 'kingdah@gmail.com' }}</span>
                        </div>
                        <div class="flex gap-3">
                            <i class="fa-solid fa-phone mt-1 text-primary"></i>
                            <span>{{ $contactSettings->phones[0]['phone'] ?? $contactSettings->hotlines[0]['phone'] ?? '' }}</span>
                        </div>
                        @if($contactSettings->default_working_hours)
                            <div class="flex gap-3">
                                <i class="fa-solid fa-clock mt-1 text-primary"></i>
                                <span>{{ $contactSettings->default_working_hours }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($contactSettings->default_google_map_embed)
                    <div class="overflow-hidden rounded bg-white shadow ring-1 ring-slate-200">
                        {!! $contactSettings->default_google_map_embed !!}
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if($branches->count())
        <section class="pb-16">
            <div class="mx-auto max-w-7xl px-4">
                <h2 class="text-3xl font-extrabold text-slate-950">{{ __('ui.contact.branches_title') }}</h2>
                <div class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($branches as $branch)
                        @php($branchTranslation = $branch->translation())
                        <article class="rounded bg-white p-6 shadow ring-1 ring-slate-200">
                            <h3 class="font-extrabold text-slate-950">{{ $branchTranslation?->name ?: $branch->code }}</h3>
                            <p class="mt-3 text-sm leading-6 text-slate-600">{{ $branchTranslation?->display_address ?: $branchTranslation?->short_address }}</p>
                            <div class="mt-5 space-y-2 text-sm text-slate-600">
                                @foreach($branch->publicContacts->take(3) as $contact)
                                    <div class="flex gap-2">
                                        <i class="fa-solid fa-circle-info mt-1 text-primary"></i>
                                        <span>{{ $contact->display_value ?: $contact->value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
