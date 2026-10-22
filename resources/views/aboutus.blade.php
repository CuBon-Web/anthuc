@extends('layouts.main.master')
@section('title')
    {{ ($gioithieu && languageName($gioithieu->title)) ? languageName($gioithieu->title) : getLanguage('aboutFallback') }}
@endsection
@section('description')
    {{ $gioithieu ? trim(strip_tags(languageName($gioithieu->description ?? ''))) : ($setting->company ?? '') }}
@endsection
@section('content')
@php
    $aboutTitleText = $gioithieu ? languageName($gioithieu->title) : '';
    $aboutTitle = $aboutTitleText !== '' ? $aboutTitleText : getLanguage('aboutFallback');
    $aboutImages = json_decode($gioithieu->image ?? '[]', true);
    if (!is_array($aboutImages)) {
        $aboutImages = ($gioithieu && $gioithieu->image) ? [$gioithieu->image] : [];
    }
    $aboutImage = $aboutImages[0] ?? '';
    $aboutImageUrl = $aboutImage
        ? (preg_match('#^https?://#i', $aboutImage) ? $aboutImage : url($aboutImage))
        : '';
    $aboutIntro = $gioithieu ? languageName($gioithieu->description ?? '') : '';
    $aboutContent = $gioithieu ? languageName($gioithieu->content) : '';
    if (preg_match('/<p\\b[^>]*>.*?<\\/p>/is', $aboutIntro, $aboutIntroMatch)) {
        $aboutIntro = $aboutIntroMatch[0];
    }
    $mediaUrl = function ($path) {
        if (!$path) {
            return '';
        }
        return preg_match('#^https?://#i', $path) ? $path : url($path);
    };
@endphp
<main class="about-page">
    <section class="page-hero">
        @if ($aboutImageUrl)
        <img class="page-hero-bg" src="{{ $aboutImageUrl }}" alt="{{ $aboutTitle }}">
        @endif
        <div class="container">
            <nav class="page-crumb" aria-label="{{ getLanguage('aboutBreadcrumb') }}">
                <a href="{{ route('home') }}">{{ getLanguage('home') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $aboutTitle }}</span>
            </nav>
            <span class="eyebrow">{{ getLanguage('anthustory') }}</span>
            <h1>{{ $aboutTitle }}</h1>
        </div>
    </section>

    @if ($aboutContent)
    <section class="section-pad">
        <div class="container">
            <div class="section-head text-center mx-auto mb-4">
                <span class="eyebrow">{{ getLanguage('aboutBrandStory') }}</span>
                <h2 class="section-title mt-3 mx-auto">{{ getLanguage('aboutJourneyTitle') }}</h2>
            </div>
            <div class="about-prose">
                {!! $aboutContent !!}
            </div>
        </div>
    </section>
    @endif

    @if ($processSteps->count())
    <section class="process-section section-pad" id="process">
        <div class="container">
            <div class="section-head">
                <span class="eyebrow">{{ getLanguage('homeProcessEyebrow') }}</span>
                <h2 class="section-title mt-3">{{ getLanguage('homeProcessTitle') }}</h2>
                <p class="section-subtitle">{{ getLanguage('homeProcessNote') }}</p>
            </div>
            <div class="process-list">
                @foreach ($processSteps as $step)
                @php
                    $stepTitle = languageName($step->title);
                    $stepDesc = languageName($step->description);
                    $stepImageUrl = $mediaUrl($step->image ?? '');
                @endphp
                <div class="process-step" data-aos="fade-up" @if ($loop->index) data-aos-delay="{{ $loop->index * 70 }}" @endif>
                    <div class="process-photo">
                        @if ($stepImageUrl)
                        <img src="{{ $stepImageUrl }}" alt="{{ $stepTitle }}" loading="lazy">
                        @endif
                        <span class="process-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div>
                        <h3>{{ $stepTitle }}</h3>
                        @if ($stepDesc)
                        <p>{!! $stepDesc !!}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($whyChoose->count())
    <div class="trust-wrap">
        <section class="why-section" id="why">
            <div class="container">
                <div class="section-head">
                    <h2 class="section-title title-leaf">{{ getLanguage('homeWhyTitle') }}</h2>
                </div>
                <div class="why-grid">
                    @foreach ($whyChoose as $item)
                    @php
                        $whyTitle = languageName($item->title);
                        $whyDesc = languageName($item->description);
                        $whyImageUrl = $mediaUrl($item->image ?? '');
                    @endphp
                    <div class="why-item">
                        <div class="why-icon">
                            @if ($whyImageUrl)
                            <img src="{{ $whyImageUrl }}" alt="{{ $whyTitle }}" loading="lazy">
                            @endif
                        </div>
                        <h3>{{ $whyTitle }}</h3>
                        @if ($whyDesc)
                        <p>{{ $whyDesc }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
    @endif

    @if ($strengths->count())
    <section class="strengths-section section-pad">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title title-leaf">{{ getLanguage('homeStrengthsTitle') }}</h2>
                <p class="section-subtitle mt-2">{{ getLanguage('aboutStrengthsNote') }}</p>
            </div>
            <div class="row g-4 strengths-grid">
                @foreach ($strengths as $item)
                @php
                    $strengthTitle = languageName($item->title);
                    $strengthDesc = languageName($item->description);
                    $strengthImageUrl = $mediaUrl($item->image ?? '');
                    $strengthIcon = preg_match('/^bi bi-[a-z0-9-]+$/', (string) $item->icon) ? $item->icon : '';
                @endphp
                <div class="col-md-4" data-aos="fade-up" @if ($loop->index) data-aos-delay="{{ $loop->index * 80 }}" @endif>
                    <article class="strength-card">
                        @if ($strengthImageUrl)
                        <img src="{{ $strengthImageUrl }}" alt="{{ $strengthTitle }}" loading="lazy">
                        @endif
                        <div class="strength-copy">
                            @if ($strengthIcon)
                            <i class="{{ $strengthIcon }}"></i>
                            @endif
                            <h3>{{ $strengthTitle }}</h3>
                            @if ($strengthDesc)
                            <p>{{ $strengthDesc }}</p>
                            @endif
                        </div>
                    </article>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if ($ReviewCus->count())
    <section class="reviews-section section-pad" id="reviews">
        <div class="container">
            <div class="section-head">
                <h2 class="section-title title-leaf">{{ getLanguage('homeReviewsTitle') }}</h2>
                <p class="section-subtitle">{{ getLanguage('homeReviewsNote') }}</p>
            </div>
            <div class="reviews-slider">
                <div class="swiper reviewsSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($ReviewCus as $item)
                        <div class="swiper-slide">
                            <article class="review-card">
                                <img class="review-avatar" src="{{ $item->avatar }}" alt="{{ languageName($item->name) }}" loading="lazy">
                                <div class="review-body">
                                    <div class="review-quote">“</div>
                                    <p class="review-text">{{ languageName($item->content) }}</p>
                                    <strong class="review-name">{{ languageName($item->name) }}</strong>
                                    <div class="review-stars" aria-label="{{ getLanguage('homeFiveStars') }}">★★★★★</div>
                                </div>
                            </article>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <button class="reviews-nav reviews-prev" aria-label="{{ getLanguage('homeReviewPrev') }}"><i class="bi bi-chevron-left"></i></button>
                <button class="reviews-nav reviews-next" aria-label="{{ getLanguage('homeReviewNext') }}"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
    </section>
    @endif

    @if ($partner->count())
    <section class="section-pad" style="padding-top: 0;">
        <div class="container">
            <div class="section-head text-center mx-auto mb-4">
                <span class="eyebrow">{{ getLanguage('aboutPartnerEyebrow') }}</span>
                <h2 class="section-title mt-3 mx-auto">{{ getLanguage('aboutPartnerTitle') }}</h2>
            </div>
            <div class="partner-row">
                @foreach ($partner as $item)
                @php $partnerImage = $mediaUrl($item->image ?? ''); @endphp
                @if ($item->link)
                <a href="{{ $item->link }}" target="_blank" rel="noopener" title="{{ $item->name }}">
                    <img src="{{ $partnerImage }}" alt="{{ $item->name }}" loading="lazy">
                </a>
                @elseif ($partnerImage)
                <span><img src="{{ $partnerImage }}" alt="{{ $item->name }}" loading="lazy"></span>
                @endif
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <section class="newsletter">
        <div class="container newsletter-inner">
            <div>
                <span class="eyebrow text-warning mb-2">{{ getLanguage('homeNewsletterEyebrow') }}</span>
                <h2>{{ getLanguage('aboutCtaTitle') }}</h2>
                <p class="about-lead">{{ getLanguage('aboutCtaNote') }}</p>
            </div>
            <div class="about-cta">
                <a href="{{ route('allProduct') }}" class="btn-brand">{{ getLanguage('aboutViewProducts') }} <i class="bi bi-arrow-right"></i></a>
                <a href="{{ route('lienHe') }}" class="btn-ghost">{{ getLanguage('contact') }}</a>
            </div>
        </div>
    </section>
</main>
@endsection
