@extends('layouts.master')

@section('title', $translation->meta_title ?? $translation->title)

@section('content')
    @php
        $hero = $aboutSettings['hero'] ?? [];
        $intro = $aboutSettings['intro'] ?? [];
        $development = $aboutSettings['development'] ?? [];
        $timeline = $aboutSettings['timeline'] ?? [];
        $culture = $aboutSettings['culture'] ?? [];
        $capabilities = $aboutSettings['capabilities'] ?? [];
        $certificates = $aboutSettings['certificates'] ?? [];
        $intellectualProperty = $aboutSettings['intellectual_property'] ?? [];
        $customers = $aboutSettings['customers'] ?? [];
        $contact = $aboutSettings['contact'] ?? [];
        $heroImage = $hero['image'] ?: asset('images/about/kingda-hero-rd.jpg');
        $introImage = $intro['image'] ?: asset('images/about/kingda-office.jpg');
        $introSmallImageOne = $intro['small_image_one'] ?: asset('images/about/kingda-lab.jpg');
        $introSmallImageTwo = $intro['small_image_two'] ?: asset('images/about/kingda-production.jpg');
        $localizedContactUrl = app()->getLocale() === 'vi'
            ? url('/lien-he')
            : url('/' . app()->getLocale() . '/contact');
        $contactButtonUrl = filled($contact['button_url'] ?? null) && $contact['button_url'] !== '/lien-he'
            ? $contact['button_url']
            : $localizedContactUrl;
    @endphp

    <main class="kingda-about-page">
        <section class="about-hero">
            <div class="about-hero__bg"></div>
            <div class="about-container">
                <div class="about-hero__inner">
                    <div class="about-hero__content" data-aos="fade-up">
                        @if(filled($hero['eyebrow'] ?? null))
                            <div class="about-eyebrow">{{ $hero['eyebrow'] }}</div>
                        @endif
                        <h1>{{ $hero['title'] ?? $translation->headline ?? $translation->title }}</h1>
                        @if(filled($hero['description'] ?? null))
                            <p>{{ $hero['description'] }}</p>
                        @endif
                        <div class="about-hero__actions">
                            <a href="#about-intro" class="about-btn about-btn--primary">{{ $hero['primary_button_label'] ?? '' }}</a>
                            <a href="#about-culture" class="about-btn about-btn--ghost">{{ $hero['secondary_button_label'] ?? '' }}</a>
                        </div>
                    </div>
                    <div class="about-hero__visual" data-aos="fade-left">
                        <div class="about-hero__card about-hero__card--main">
                            <img src="{{ $heroImage }}" alt="{{ $hero['title'] ?? $translation->title }}">
                        </div>
                        <div class="about-hero__floating about-hero__floating--one">
                            <strong>{{ $hero['floating_one_value'] ?? '' }}</strong>
                            <span>{{ $hero['floating_one_label'] ?? '' }}</span>
                        </div>
                        <div class="about-hero__floating about-hero__floating--two">
                            <strong>{{ $hero['floating_two_value'] ?? '' }}</strong>
                            <span>{{ $hero['floating_two_label'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about-intro" class="about-section about-intro">
            <div class="about-container">
                <div class="about-grid about-grid--intro">
                    <div class="about-intro__media" data-aos="fade-right">
                        <div class="about-image-stack">
                            <div class="about-image-stack__main">
                                <img src="{{ $introImage }}" alt="{{ $intro['title'] ?? $translation->title }}">
                            </div>
                            <div class="about-image-stack__small about-image-stack__small--one">
                                <img src="{{ $introSmallImageOne }}" alt="{{ $intro['title'] ?? $translation->title }}">
                            </div>

                        </div>
                    </div>
                    <div class="about-intro__content" data-aos="fade-left">
                        <div class="about-section-label">{{ $intro['eyebrow'] ?? '' }}</div>
                        <h2>{{ $intro['title'] ?? '' }}</h2>
                        @foreach(preg_split('/\R{2,}/', trim($intro['content'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        @if(! empty($intro['stats']))
                            <div class="about-intro__stats">
                                @foreach($intro['stats'] as $stat)
                                    <div>
                                        <strong>{{ $stat['value'] ?? '' }}</strong>
                                        <span>{{ $stat['label'] ?? '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section about-development">
            <div class="about-container">
                <div class="about-section-head about-section-head--center" data-aos="fade-up">
                    <div class="about-section-label">{{ $development['eyebrow'] ?? '' }}</div>
                    <h2>{{ $development['title'] ?? '' }}</h2>
                    <p>{{ $development['description'] ?? '' }}</p>
                </div>
                <div class="about-development__grid">
                    @foreach($development['items'] ?? [] as $index => $item)
                        <article class="about-development-card" data-aos="fade-up" data-aos-delay="{{ $index * 120 }}">
                            <span>{{ $item['number'] ?? str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $item['title'] ?? '' }}</h3>
                            <p>{{ $item['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="about-section about-timeline">
            <div class="about-container">
                <div class="about-section-head" data-aos="fade-up">
                    <div class="about-section-label">{{ $timeline['eyebrow'] ?? '' }}</div>
                    <h2>{{ $timeline['title'] ?? '' }}</h2>
                </div>
                <div class="about-timeline__list">
                    @foreach($timeline['items'] ?? [] as $index => $item)
                        <article class="about-timeline-item" data-aos="fade-up" data-aos-delay="{{ $index * 80 }}">
                            <div class="about-timeline-item__year">{{ $item['year'] ?? '' }}</div>
                            <div class="about-timeline-item__body">
                                <h3>{{ $item['title'] ?? '' }}</h3>
                                <p>{{ $item['description'] ?? '' }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="about-culture" class="about-section about-culture">
            <div class="about-container">
                <div class="about-culture__layout">
                    <div class="about-culture__intro" data-aos="fade-right">
                        <div class="about-section-label">{{ $culture['eyebrow'] ?? '' }}</div>
                        <h2>{{ $culture['title'] ?? '' }}</h2>
                        <p>{{ $culture['description'] ?? '' }}</p>
                        <div class="about-culture__highlight">
                            <div class="about-culture__icon">✦</div>
                            <div>
                                <h3>{{ $culture['highlight_title'] ?? '' }}</h3>
                                <p>{{ $culture['highlight_description'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="about-culture__cards" data-aos="fade-left">
                        @foreach($culture['items'] ?? [] as $item)
                            <article class="about-culture-card">
                                <div class="about-culture-card__icon">{{ $item['icon'] ?? '✦' }}</div>
                                <h3>{{ $item['title'] ?? '' }}</h3>
                                <p>{{ $item['description'] ?? '' }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="about-cta">
            <div class="about-container">
                <div class="about-cta__inner" data-aos="fade-up">
                    <div>
                        <span>{{ $contact['eyebrow'] ?? '' }}</span>
                        <h2>{{ $contact['title'] ?? '' }}</h2>
                        <p>{{ $contact['description'] ?? '' }}</p>
                    </div>
                    <a href="{{ $contactButtonUrl }}" class="about-btn about-btn--light">
                        {{ $contact['button_label'] ?? '' }}
                    </a>
                </div>
            </div>
        </section>
    </main>
@endsection

@push('styles')
    <style>
        :root {
            --kingda-red: #d71920;
            --kingda-red-dark: #8f0000;
            --kingda-red-deep: #5f0000;

            --kingda-gold: #f6c400;
            --kingda-gold-light: #ffe680;
            --kingda-gold-dark: #c99500;

            --kingda-text: #241313;
            --kingda-heading: #5f0000;
            --kingda-muted: #75615c;

            --kingda-soft: #fff7ef;
            --kingda-soft-red: #fff1f1;
            --kingda-border: rgba(215, 25, 32, .16);

            --kingda-shadow: 0 24px 80px rgba(120, 0, 0, .13);

            /*
             * Giữ lại 3 biến cũ để khỏi phải sửa quá nhiều CSS bên dưới.
             * Tên biến vẫn là blue/cyan nhưng màu thực tế đã đổi sang đỏ/vàng.
             */
            --kingda-blue: var(--kingda-red);
            --kingda-blue-dark: var(--kingda-red-dark);
            --kingda-cyan: var(--kingda-gold);
        }

        .kingda-about-page {
            overflow: hidden;
            color: var(--kingda-text);
            background: #fff;
        }

        .kingda-about-page * {
            box-sizing: border-box;
        }

        .about-container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .about-section {
            position: relative;
            padding: 96px 0;
        }

        .about-section-label,
        .about-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            color: var(--kingda-blue);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .about-section-label::after,
        .about-eyebrow::after {
            content: "";
            width: 48px;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--kingda-blue), var(--kingda-cyan));
        }

        .about-section-head {
            max-width: 760px;
            margin-bottom: 48px;
        }

        .about-section-head--center {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .about-section-head--center .about-section-label {
            justify-content: center;
        }

        .about-section-head h2,
        .about-intro__content h2,
        .about-culture__intro h2,
        .about-certifications__content h2 {
            margin: 0 0 18px;
            color: var(--kingda-heading);
            font-size: clamp(32px, 4vw, 52px);
            line-height: 1.12;
            letter-spacing: -0.04em;
        }

        .about-section-head p,
        .about-intro__content p,
        .about-culture__intro p,
        .about-certifications__content p {
            margin: 0;
            color: var(--kingda-muted);
            font-size: 17px;
            line-height: 1.8;
        }

        .about-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 24px;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            transition: .25s ease;
        }

        .about-btn--primary {
            color: #fff;
            background: linear-gradient(135deg, var(--kingda-blue-dark), var(--kingda-blue), var(--kingda-cyan));
            box-shadow: 0 16px 36px rgba(11, 94, 215, .24);
        }

        .about-btn--primary:hover {
            transform: translateY(-2px);
            color: #fff;
            box-shadow: 0 22px 44px rgba(11, 94, 215, .32);
        }

        .about-btn--ghost {
            color: var(--kingda-blue);
            background: rgba(255, 255, 255, .72);
            border: 1px solid var(--kingda-border);
        }

        .about-btn--ghost:hover {
            color: var(--kingda-blue-dark);
            background: #fff;
        }

        .about-btn--light {
            color: var(--kingda-blue-dark);
            background: #fff;
            white-space: nowrap;
        }

        .about-btn--light:hover {
            transform: translateY(-2px);
            color: var(--kingda-blue-dark);
        }

        /* HERO */
        .about-hero {
            position: relative;
            min-height: 760px;
            display: flex;
            align-items: center;
            padding: 120px 0 90px;
            isolation: isolate;
            background:
                radial-gradient(circle at 85% 20%, rgba(246, 196, 0, .22), transparent 34%),
                radial-gradient(circle at 10% 20%, rgba(215, 25, 32, .08), transparent 30%),
                linear-gradient(135deg, #fffaf0 0%, #fff3f3 48%, #fff 100%);
        }

        .about-hero__bg {
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                linear-gradient(120deg, rgba(215, 25, 32, .055) 0 1px, transparent 1px 100%),
                radial-gradient(circle at 10% 20%, rgba(246, 196, 0, .12), transparent 22%);
            background-size: 92px 92px, auto;
            opacity: .9;
        }

        .about-hero__inner {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 520px;
            gap: 64px;
            align-items: center;
        }

        .about-hero__content h1 {
            margin: 0 0 24px;
            color: var(--kingda-heading);
            font-size: clamp(42px, 5.8vw, 76px);
            line-height: 1.02;
            letter-spacing: -0.06em;
        }

        .about-hero__content p {
            max-width: 660px;
            margin: 0;
            color: var(--kingda-muted) font-size: 18px;
            line-height: 1.8;
        }

        .about-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 34px;
        }

        .about-hero__visual {
            position: relative;
            min-height: 560px;
        }

        .about-hero__card {
            position: absolute;
            overflow: hidden;
            border-radius: 32px;
            background: #fff;
            box-shadow: var(--kingda-shadow);
        }

        .about-hero__card--main {
            inset: 0 0 44px 30px;
        }

        .about-hero__card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .about-hero__card--main::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(6, 43, 120, .05), rgba(24, 199, 243, .18));
        }

        .about-hero__floating {
            position: absolute;
            z-index: 2;
            min-width: 148px;
            padding: 20px 22px;
            border-radius: 24px;
            background: rgba(255, 255, 255, .86);
            border: 1px solid rgba(255, 255, 255, .8);
            box-shadow: 0 18px 50px rgba(6, 43, 120, .16);
            backdrop-filter: blur(14px);
        }

        .about-hero__floating strong {
            display: block;
            color: var(--kingda-blue);
            font-size: 30px;
            line-height: 1;
        }

        .about-hero__floating span {
            display: block;
            margin-top: 6px;
            color: var(--kingda-muted);
            font-size: 13px;
            font-weight: 700;
        }

        .about-hero__floating--one {
            left: 0;
            bottom: 88px;
        }

        .about-hero__floating--two {
            right: -24px;
            top: 86px;
        }

        /* INTRO */
        .about-grid {
            display: grid;
            gap: 64px;
            align-items: center;
        }

        .about-grid--intro {
            grid-template-columns: 520px minmax(0, 1fr);
        }

        .about-image-stack {
            position: relative;
            min-height: 560px;
        }

        .about-image-stack__main,
        .about-image-stack__small {
            position: absolute;
            overflow: hidden;
            border-radius: 28px;
            box-shadow: var(--kingda-shadow);
            background: #eaf4ff;
        }

        .about-image-stack__main {
            inset: 0 74px 90px 0;
        }

        .about-image-stack__small--one {
            right: 0;
            top: 70px;
            width: 230px;
            height: 180px;
        }

        .about-image-stack__small--two {
            left: 80px;
            bottom: 0;
            width: 330px;
            height: 220px;
        }

        .about-image-stack img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .about-intro__stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 34px;
        }

        .about-intro__stats div {
            padding: 22px 18px;
            border: 1px solid var(--kingda-border);
            border-radius: 22px;
            background: linear-gradient(180deg, #fff, #f7fbff);
        }

        .about-intro__stats strong {
            display: block;
            color: var(--kingda-blue);
            font-size: 30px;
            line-height: 1;
        }

        .about-intro__stats span {
            display: block;
            margin-top: 8px;
            color: var(--kingda-muted);
            font-size: 13px;
            font-weight: 700;
        }

        /* DEVELOPMENT */
        .about-development {
            background:
                radial-gradient(circle at 15% 10%, rgba(246, 196, 0, .16), transparent 28%),
                radial-gradient(circle at 90% 20%, rgba(215, 25, 32, .08), transparent 30%),
                linear-gradient(180deg, #fff7ef, #fff);
        }

        .about-development__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        .about-development-card {
            position: relative;
            overflow: hidden;
            padding: 36px;
            border-radius: 30px;
            border: 1px solid var(--kingda-border);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 20px 60px rgba(15, 45, 90, .08);
        }

        .about-development-card span {
            display: inline-flex;
            width: 54px;
            height: 54px;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            border-radius: 18px;
            color: #fff;
            font-weight: 900;
            background: linear-gradient(135deg, var(--kingda-blue-dark), var(--kingda-blue));
        }

        .about-development-card h3 {
            margin: 0 0 14px;
            color: var(--kingda-heading);
            font-size: 24px;
        }

        .about-development-card p {
            margin: 0;
            color: var(--kingda-muted);
            line-height: 1.75;
        }

        /* TIMELINE */
        .about-timeline__list {
            position: relative;
            display: grid;
            gap: 18px;
        }

        .about-timeline__list::before {
            content: "";
            position: absolute;
            left: 88px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--kingda-blue), rgba(24, 199, 243, .1));
        }

        .about-timeline-item {
            position: relative;
            display: grid;
            grid-template-columns: 176px minmax(0, 1fr);
            gap: 30px;
            align-items: stretch;
        }

        .about-timeline-item__year {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 104px;
            border-radius: 24px;
            color: #fff;
            font-size: 28px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--kingda-blue-dark), var(--kingda-blue), var(--kingda-cyan));
            box-shadow: 0 16px 44px rgba(11, 94, 215, .18);
        }

        .about-timeline-item__year::after {
            content: "";
            position: absolute;
            right: -39px;
            top: 50%;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transform: translateY(-50%);
            background: #fff;
            border: 4px solid var(--kingda-blue);
            z-index: 2;
        }

        .about-timeline-item__body {
            padding: 28px 32px;
            border-radius: 24px;
            border: 1px solid var(--kingda-border);
            background: #fff;
            box-shadow: 0 16px 50px rgba(15, 45, 90, .06);
        }

        .about-timeline-item__body h3 {
            margin: 0 0 8px;
            color: #082060;
            font-size: 22px;
        }

        .about-timeline-item__body p {
            margin: 0;
            color: var(--kingda-muted);
            line-height: 1.7;
        }

        /* CULTURE */
        .about-culture {
            background:
                linear-gradient(135deg, rgba(255, 247, 239, .94), rgba(255, 255, 255, .96)),
                radial-gradient(circle at 85% 20%, rgba(246, 196, 0, .22), transparent 32%),
                radial-gradient(circle at 8% 80%, rgba(215, 25, 32, .08), transparent 28%);
        }

        .about-culture__layout {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 54px;
            align-items: center;
        }

        .about-culture__highlight {
            display: grid;
            grid-template-columns: 82px minmax(0, 1fr);
            gap: 22px;
            margin-top: 34px;
            padding: 30px;
            border-radius: 30px;
            color: #fff;
            background: linear-gradient(135deg, #062b78, #0b5ed7);
            box-shadow: 0 24px 80px rgba(11, 94, 215, .22);
        }

        .about-culture__icon {
            display: flex;
            width: 82px;
            height: 82px;
            align-items: center;
            justify-content: center;
            border-radius: 26px;
            color: #fff;
            font-size: 34px;
            background: rgba(255, 255, 255, .14);
        }

        .about-culture__highlight h3 {
            margin: 0 0 10px;
            font-size: 24px;
        }

        .about-culture__highlight p {
            margin: 0;
            color: rgba(255, 255, 255, .82);
            line-height: 1.75;
        }

        .about-culture__cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
        }

        .about-culture-card {
            padding: 32px;
            min-height: 230px;
            border-radius: 30px;
            border: 1px solid rgba(11, 94, 215, .12);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 20px 60px rgba(15, 45, 90, .08);
            backdrop-filter: blur(14px);
        }

        .about-culture-card__icon {
            display: flex;
            width: 56px;
            height: 56px;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            border-radius: 18px;
            color: var(--kingda-blue);
            font-size: 26px;
            font-weight: 900;
            background: #eef7ff;
        }

        .about-culture-card h3 {
            margin: 0 0 12px;
            color: #082060;
            font-size: 24px;
        }

        .about-culture-card p {
            margin: 0;
            color: var(--kingda-muted);
            line-height: 1.75;
        }

        /* CAPABILITY */
        .about-capability__grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .about-capability-card {
            overflow: hidden;
            border-radius: 30px;
            border: 1px solid var(--kingda-border);
            background: #fff;
            box-shadow: 0 20px 70px rgba(15, 45, 90, .08);
        }

        .about-capability-card__image {
            height: 250px;
            overflow: hidden;
        }

        .about-capability-card__image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: .45s ease;
        }

        .about-capability-card:hover img {
            transform: scale(1.06);
        }

        .about-capability-card__body {
            padding: 28px;
        }

        .about-capability-card__body h3 {
            margin: 0 0 12px;
            color: #082060;
            font-size: 22px;
        }

        .about-capability-card__body p {
            margin: 0;
            color: var(--kingda-muted);
            line-height: 1.7;
        }

        /* CERTIFICATIONS */
        .about-certifications {
            background: linear-gradient(180deg, #fff7ef, #fff);
        }

        .about-certifications__layout {
            display: grid;
            grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
            gap: 54px;
            align-items: center;
        }

        .about-ip {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-top: 34px;
        }

        .about-ip div {
            padding: 24px;
            border-radius: 24px;
            background: #fff;
            border: 1px solid var(--kingda-border);
            box-shadow: 0 16px 50px rgba(15, 45, 90, .06);
        }

        .about-ip strong {
            display: block;
            color: var(--kingda-blue);
            font-size: 34px;
            line-height: 1;
        }

        .about-ip span {
            display: block;
            margin-top: 8px;
            color: var(--kingda-muted);
            font-weight: 700;
        }

        .about-certifications__cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .about-certifications__cards article {
            min-height: 190px;
            padding: 30px;
            border-radius: 28px;
            color: #fff;
            background: linear-gradient(135deg, var(--kingda-red-dark), var(--kingda-red));
            box-shadow: 0 22px 70px rgba(120, 0, 0, .18);
        }

        .about-certifications__cards article:nth-child(even) {
            background: linear-gradient(135deg, var(--kingda-red), var(--kingda-gold-dark));
        }

        .about-certifications__cards span {
            display: inline-flex;
            margin-bottom: 22px;
            padding: 8px 12px;
            border-radius: 999px;
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            background: rgba(255, 255, 255, .18);
        }

        .about-certifications__cards img {
            display: block;
            max-width: 150px;
            max-height: 82px;
            margin: -6px 0 18px;
            object-fit: contain;
            border-radius: 12px;
            background: rgba(255, 255, 255, .92);
            padding: 10px;
        }

        .about-certifications__cards h3 {
            margin: 0 0 10px;
            font-size: 24px;
        }

        .about-certifications__cards h3 a {
            color: inherit;
            text-decoration: none;
        }

        .about-certifications__cards h3 a:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .about-certifications__cards p {
            margin: 0;
            color: rgba(255, 255, 255, .82);
            line-height: 1.65;
        }

        /* PARTNERS */
        .about-partners__grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        .about-partners__item {
            display: flex;
            min-height: 96px;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            border: 1px solid var(--kingda-border);
            color: var(--kingda-red-dark);
            font-size: 22px;
            font-weight: 900;
            background: linear-gradient(180deg, #fff, #f7fbff);
            box-shadow: 0 14px 44px rgba(15, 45, 90, .06);
            text-align: center;
            text-decoration: none;
            transition: transform .22s ease, border-color .22s ease, box-shadow .22s ease;
        }

        .about-partners__item:hover {
            transform: translateY(-4px);
            border-color: rgba(215, 25, 32, .26);
            box-shadow: 0 20px 54px rgba(15, 45, 90, .10);
        }

        .about-partners__item img {
            max-width: 148px;
            max-height: 52px;
            object-fit: contain;
        }

        /* CTA */
        .about-cta {
            padding: 0 0 96px;
        }

        .about-cta__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            padding: 56px;
            border-radius: 36px;
            color: #fff;
            background:
                radial-gradient(circle at 80% 20%, rgba(246, 196, 0, .44), transparent 32%),
                linear-gradient(135deg, var(--kingda-red-deep), var(--kingda-red), #e8452e);
            box-shadow: 0 30px 100px rgba(120, 0, 0, .28);
        }

        .about-cta__inner span {
            display: inline-block;
            margin-bottom: 12px;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .about-cta__inner h2 {
            max-width: 760px;
            margin: 0 0 14px;
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.12;
            letter-spacing: -0.04em;
        }

        .about-cta__inner p {
            max-width: 740px;
            margin: 0;
            color: rgba(255, 255, 255, .78);
            font-size: 17px;
            line-height: 1.75;
        }

        @media (max-width: 1100px) {

            .about-hero__inner,
            .about-grid--intro,
            .about-culture__layout,
            .about-certifications__layout {
                grid-template-columns: 1fr;
            }

            .about-hero__visual,
            .about-image-stack {
                min-height: 480px;
            }

            .about-development__grid,
            .about-capability__grid {
                grid-template-columns: 1fr;
            }

            .about-partners__grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-section {
                padding: 68px 0;
            }

            .about-hero {
                min-height: auto;
                padding: 90px 0 64px;
            }

            .about-hero__actions {
                flex-direction: column;
                align-items: stretch;
            }

            .about-hero__visual,
            .about-image-stack {
                min-height: 380px;
            }

            .about-hero__card--main {
                inset: 0;
            }

            .about-hero__floating {
                display: none;
            }

            .about-image-stack__main {
                inset: 0;
            }

            .about-image-stack__small {
                display: none;
            }

            .about-intro__stats,
            .about-development__grid,
            .about-culture__cards,
            .about-ip,
            .about-certifications__cards,
            .about-partners__grid {
                grid-template-columns: 1fr;
            }

            .about-culture__highlight {
                grid-template-columns: 1fr;
            }

            .about-timeline__list::before {
                display: none;
            }

            .about-timeline-item {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .about-timeline-item__year::after {
                display: none;
            }

            .about-cta__inner {
                flex-direction: column;
                align-items: flex-start;
                padding: 36px 24px;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AOS) {
                AOS.init({
                    duration: 750,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 80,
                });
            }
        });
    </script>
@endpush
