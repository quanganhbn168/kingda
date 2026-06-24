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

                @if(session('contact_success'))
                    <div role="status" class="mt-6 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                        {{ session('contact_success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div role="alert" class="mt-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-extrabold">{{ __('ui.contact.validation_intro') }}</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ app()->getLocale() === 'vi' ? route('contact.store') : route('localized.contact.store', ['locale' => app()->getLocale()]) }}" class="mt-8 grid gap-4">
                    @csrf

                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" type="text" name="website" value="" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="100" required autocomplete="name" placeholder="{{ __('ui.contact.name') }} *" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" placeholder="{{ __('ui.contact.phone') }}" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('phone') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <input type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email" placeholder="{{ __('ui.contact.email') }}" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('email') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <input type="text" name="company" value="{{ old('company') }}" maxlength="150" autocomplete="organization" placeholder="{{ __('ui.contact.company') }}" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('company') ? 'border-red-400' : 'border-slate-200' }}">
                            @error('company') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <input type="text" name="subject" value="{{ old('subject') }}" maxlength="200" placeholder="{{ __('ui.contact.subject') }}" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('subject') ? 'border-red-400' : 'border-slate-200' }}">
                        @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <textarea name="message" rows="6" maxlength="5000" required placeholder="{{ __('ui.contact.message') }} *" class="w-full rounded border px-4 py-3 text-sm outline-none focus:border-primary {{ $errors->has('message') ? 'border-red-400' : 'border-slate-200' }}">{{ old('message') }}</textarea>
                        @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <p class="text-xs text-slate-500">{{ __('ui.contact.channel_hint') }}</p>
                    <button type="submit" class="inline-flex w-fit items-center gap-2 rounded bg-primary px-6 py-3 text-sm font-extrabold text-white hover:bg-primary-dark">
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
                            <span>{{ $contactSettings->address }}</span>
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
