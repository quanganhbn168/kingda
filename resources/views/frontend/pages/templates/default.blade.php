@extends('layouts.master')

@section('content')
    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4">
            <h1 class="text-4xl font-bold">
                {{ $translation->headline ?: $translation->title }}
            </h1>

            @if($translation->subheadline)
                <p class="mt-4 max-w-3xl text-lg text-slate-600">
                    {{ $translation->subheadline }}
                </p>
            @endif
        </div>
    </section>

    @if($translation->content)
        <section class="py-10">
            <div class="mx-auto max-w-4xl px-4">
                <div class="prose max-w-none">
                    {!! $translation->content !!}
                </div>
            </div>
        </section>
    @endif
@endsection
