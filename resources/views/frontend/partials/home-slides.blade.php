<section class="relative overflow-hidden bg-slate-950 text-white">
    <div data-home-slide-swiper class="swiper kd-home-slide-swiper h-[500px] md:h-[620px] xl:h-[700px]">
        <div class="swiper-wrapper">
            @foreach($slides as $index => $slide)
                @php
                    $slideTranslation = $slide->publishedTranslation(app()->getLocale());
                    $desktopImage = $slide->getFirstMediaUrl('desktop');
                    $mobileImage = $slide->getFirstMediaUrl('mobile') ?: $desktopImage;
                    $videoUrl = $slide->getFirstMediaUrl('video');
                    $mediaType = $slide->media_type ?: 'image';
                    $align = match ($slide->text_position) {
                        'center' => 'mx-auto text-center items-center',
                        'right' => 'ml-auto text-right items-end',
                        default => 'mr-auto text-left items-start',
                    };
                @endphp

                @if($slideTranslation)
                    <article class="swiper-slide kd-home-slide relative h-[500px] overflow-hidden md:h-[620px] xl:h-[700px]">
                        @if($mediaType === 'video_upload' && $videoUrl)
                            <video
                                class="kd-home-slide-media absolute inset-0 h-full w-full object-cover"
                                autoplay
                                muted
                                loop
                                playsinline
                                preload="metadata"
                                @if($desktopImage) poster="{{ $desktopImage }}" @endif
                            >
                                <source src="{{ $videoUrl }}">
                            </video>
                        @elseif($desktopImage)
                            <picture class="absolute inset-0">
                                <source media="(max-width: 767px)" srcset="{{ $mobileImage }}">
                                <img
                                    src="{{ $desktopImage }}"
                                    alt="{{ $slideTranslation->image_alt ?: $slideTranslation->title }}"
                                    class="kd-home-slide-media h-full w-full object-cover"
                                >
                            </picture>
                        @else
                            <div class="absolute inset-0 bg-[linear-gradient(105deg,rgba(23,17,10,1)_0%,rgba(159,15,22,.92)_52%,rgba(242,211,0,.28)_100%)]"></div>
                        @endif

                        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(23,17,10,.58)_0%,rgba(23,17,10,.42)_38%,rgba(23,17,10,.16)_56%,rgba(23,17,10,0)_72%)]"></div>

                        <div class="relative z-10 mx-auto flex h-[500px] max-w-7xl items-center px-4 py-14 md:h-[620px] md:py-18 xl:h-[700px]">
                            <div class="flex max-w-[36rem] flex-col {{ $align }}">
                                @if($slideTranslation->eyebrow)
                                    <p class="kd-slide-kicker text-xs font-extrabold uppercase tracking-[0.18em] text-red-100">
                                        {{ $slideTranslation->eyebrow }}
                                    </p>
                                @endif

                                <h1 class="kd-slide-title mt-4 max-w-2xl text-2xl font-black leading-tight tracking-normal text-white sm:text-[2rem] md:text-[2.35rem] xl:text-[2.75rem]">
                                    {{ $slideTranslation->title }}
                                </h1>

                                @if($slideTranslation->description)
                                    <p class="kd-slide-description mt-5 max-w-xl text-sm leading-7 text-slate-100 sm:text-base md:text-[1rem] md:leading-8">
                                        {{ $slideTranslation->description }}
                                    </p>
                                @endif

                                @if(($slideTranslation->primary_button_label && $slideTranslation->primary_button_url) || ($slideTranslation->secondary_button_label && $slideTranslation->secondary_button_url) || ($mediaType === 'video_embed' && $slide->video_embed_url))
                                    <div class="kd-slide-actions mt-7 flex flex-wrap gap-3 md:mt-8">
                                        @if($slideTranslation->primary_button_label && $slideTranslation->primary_button_url)
                                            <a href="{{ $slideTranslation->primary_button_url }}" class="kd-slide-btn kd-slide-btn-primary inline-flex items-center justify-center rounded px-6 py-3 text-sm font-extrabold text-white md:px-7">
                                                {{ $slideTranslation->primary_button_label }}
                                            </a>
                                        @endif

                                        @if($slideTranslation->secondary_button_label && $slideTranslation->secondary_button_url)
                                            <a href="{{ $slideTranslation->secondary_button_url }}" class="kd-slide-btn kd-slide-btn-secondary inline-flex items-center justify-center rounded border border-white/55 px-6 py-3 text-sm font-extrabold text-white md:px-7">
                                                {{ $slideTranslation->secondary_button_label }}
                                            </a>
                                        @endif

                                        @if($mediaType === 'video_embed' && $slide->video_embed_url)
                                            <a href="{{ $slide->video_embed_url }}" data-glightbox class="kd-slide-btn kd-slide-btn-secondary inline-flex items-center justify-center gap-2 rounded border border-white/55 px-6 py-3 text-sm font-extrabold text-white md:px-7">
                                                <i class="fa-solid fa-play"></i>
                                                {{ __('ui.actions.video') }}
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>

    @if($slides->count() > 1)
        <div data-home-slide-pagination class="kd-home-slide-pagination absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 items-center gap-2"></div>

        <button
            type="button"
            data-home-slide-prev
            class="kd-slide-arrow absolute left-4 top-1/2 z-20 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-white/35 text-white md:inline-flex"
            aria-label="{{ __('ui.nav.previous') }}"
        >
            ‹
        </button>

        <button
            type="button"
            data-home-slide-next
            class="kd-slide-arrow absolute right-4 top-1/2 z-20 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded border border-white/35 text-white md:inline-flex"
            aria-label="{{ __('ui.nav.next') }}"
        >
            ›
        </button>
    @endif
</section>
