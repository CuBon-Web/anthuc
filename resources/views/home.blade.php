@extends('layouts.main.master')
@section('title')
    {{ $setting->company }}
@endsection
@section('description')
    {{ $setting->webname }}
@endsection
@section('image')
    @php
        $ogBanner = $banner->first();
        $ogImage = $ogBanner && $ogBanner->image ? url($ogBanner->image) : url($setting->logo ?? '');
    @endphp
    {{ $ogImage }}
@endsection
@section('css')
@endsection
@section('js')
@endsection
@section('content')
 <!-- Hero -->
 <main>
   <section class="hero" id="home">
     <div class="swiper heroSwiper">
       <div class="swiper-wrapper">
         @forelse ($banner as $slide)
         @php
           $slideImage = $slide->image ?? '';
           $slideMobile = $slide->image_mobile ?: $slideImage;
           $slideTitle = languageName($slide->title);
           $slideDesc = languageName($slide->description);
           $slideAlt = trim(strip_tags($slideTitle)) ?: ($setting->company ?? getLanguage('homeBannerFallback'));
           $slideLink = trim((string) ($slide->link ?? ''));
           $slideHasLink = $slideLink !== '' && (
             strpos($slideLink, 'http://') === 0
             || strpos($slideLink, 'https://') === 0
             || strpos($slideLink, '/') === 0
             || strpos($slideLink, '#') === 0
           );
           $slideUrl = function ($path) {
             if (!$path) {
               return '';
             }
             return preg_match('#^https?://#i', $path) ? $path : url($path);
           };
         @endphp
         <div class="swiper-slide hero-slide">
           @if ($slide->isYoutube())
           <iframe class="hero-bg hero-video" src="https://www.youtube.com/embed/{{ $slide->youtube_id }}?autoplay=1&mute=1&loop=1&playlist={{ $slide->youtube_id }}&controls=0&rel=0&modestbranding=1&playsinline=1" title="{{ $slideAlt }}" allow="autoplay; encrypted-media" @if(!$loop->first) loading="lazy" @endif></iframe>
           @elseif ($slideImage)
           <picture>
             @if ($slideMobile && $slideMobile !== $slideImage)
             <source media="(max-width: 767.98px)" srcset="{{ $slideUrl($slideMobile) }}">
             @endif
             <img class="hero-bg" src="{{ $slideUrl($slideImage) }}" alt="{{ $slideAlt }}" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif>
           </picture>
           @endif
           @if ($slideTitle || $slideDesc || $slideHasLink)
           <div class="container">
             <div class="hero-copy">
               @if ($slideTitle)
               <h1>{!! $slideTitle !!}</h1>
               @endif
               @if ($slideDesc)
               <div class="hero-desc">{!! $slideDesc !!}</div>
               @endif
               @if ($slideHasLink)
               <div class="hero-ctas">
                 <a href="{{ $slideLink }}" class="btn-brand">{{getLanguage('viewmore')}} <i class="bi bi-arrow-right"></i></a>
               </div>
               @endif
             </div>
           </div>
           @endif
         </div>
         @empty
         @endforelse
       </div>
       <div class="swiper-pagination hero-pagination"></div>
       <button class="hero-nav hero-prev" aria-label="{{ getLanguage('homeSlidePrev') }}"><i class="bi bi-chevron-left"></i></button>
       <button class="hero-nav hero-next" aria-label="{{ getLanguage('homeSlideNext') }}"><i class="bi bi-chevron-right"></i></button>
     </div>
   </section>

   <!-- Brand benefits strip -->
   <section class="hero-benefits" aria-label="{{ getLanguage('homeBenefitLabel') }}">
     <div class="container">
       <div class="benefit-strip">
         <div class="benefit-item"><i class="bi bi-flower1"></i><div><strong>{{ getLanguage('benefitHerb') }}</strong><span>{{ getLanguage('benefitHerbNote') }}</span></div></div>
         <div class="benefit-item"><i class="bi bi-shield-check"></i><div><strong>{{ getLanguage('benefitGentle') }}</strong><span>{{ getLanguage('benefitGentleNote') }}</span></div></div>
         <div class="benefit-item"><i class="bi bi-heart"></i><div><strong>{{ getLanguage('benefitFamily') }}</strong><span>{{ getLanguage('benefitFamilyNote') }}</span></div></div>
         <div class="benefit-item"><i class="bi bi-recycle"></i><div><strong>{{ getLanguage('benefitSustain') }}</strong><span>{{ getLanguage('benefitSustainNote') }}</span></div></div>
       </div>
     </div>
   </section>


   <!-- Featured products -->
   <section class="products-section" id="products">
     <div class="container position-relative">
       <div class="product-toolbar">
         <div class="section-head">
           <h2 class="section-title">{{ getLanguage('homeFeaturedTitle') }}</h2>
           <p class="section-subtitle">{{ getLanguage('homeFeaturedNote') }}</p>
         </div>
         <div class="product-toolbar-actions">
           <a href="#categories" class="product-viewall">{{ getLanguage('homeViewAllProducts') }} <i class="bi bi-arrow-right"></i></a>
           <button class="product-nav" id="productsPrev" aria-label="{{ getLanguage('homeProductPrev') }}"><i class="bi bi-chevron-left"></i></button>
           <button class="product-nav" id="productsNext" aria-label="{{ getLanguage('homeProductNext') }}"><i class="bi bi-chevron-right"></i></button>
         </div>
       </div>
       <div class="row products-grid" id="productsGrid">
        @foreach ($homePro as $item)
        <div class="col-6 col-lg-3" data-aos="fade-up">
          @include('layouts.product.item', ['pro' => $item])
        </div>
        @endforeach
       </div>
     </div>
   </section>

   <!-- Brand story -->
   <section class="story-section" id="story">
     <div class="story-grid">
       <div class="story-photo" data-aos="fade-up">
         <img src="{{json_decode($gioithieu->image)[0]}}" alt="{{ getLanguage('homeStoryAlt') }}" loading="lazy">
         <div class="story-photo-label">{{ getLanguage('homeStoryLabel') }}</div>
       </div>
       <div class="story-copy" data-aos="fade-up">
         <span class="eyebrow">{{ getLanguage('anthustory') }}</span>
         <h2>{{$setting->company}}</h2>
         <p>{!! languageName($gioithieu->description) !!}</p>
         <a href="{{route('aboutUs')}}" class="btn-brand">{{ getLanguage('homeExploreStory') }} <i class="bi bi-arrow-right"></i></a>
       </div>
     </div>
   </section>

   <!-- Fermentation process -->
   <section class="process-section section-pad" id="process">
     <div class="container">
       <div class="section-head">
         <span class="eyebrow">{{ getLanguage('homeProcessEyebrow') }}</span>
         <h2 class="section-title mt-3">{{ getLanguage('homeProcessTitle') }}</h2>
         <p class="section-subtitle">{{ getLanguage('homeProcessNote') }}</p>
       </div>
       <div class="process-list">
         @forelse ($processSteps as $step)
         @php
           $stepTitle = languageName($step->title);
           $stepDesc = languageName($step->description);
           $stepImage = $step->image ?? '';
           $stepImageUrl = $stepImage
             ? (preg_match('#^https?://#i', $stepImage) ? $stepImage : url($stepImage))
             : '';
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
         @empty
         @endforelse
       </div>
     </div>
   </section>

   <!-- Categories -->
   <section class="categories-section section-pad" id="categories">
     <div class="container">
       <div class="section-head">
         <span class="eyebrow">{{ getLanguage('homeCategoryEyebrow') }}</span>
         <h2 class="section-title mt-3">{{ getLanguage('homeCategoryTitle') }}</h2>
         <p class="section-subtitle">{{ getLanguage('homeCategoryNote') }}</p>
       </div>
       <div class="row g-3 g-lg-4">
        @foreach ($categoryhome as $item)
        <div class="col-6 col-lg-3" data-aos="fade-up">
          <a href="{{route('allListProCate', ['danhmuc' => $item->slug])}}" class="category-card"><img src="{{$item->avatar}}" alt="{{languageName($item->name)}}" loading="lazy"><div class="category-content"><small>{{languageName($item->name)}}</small><h3>{{languageName($item->name)}}</h3><span>{{ getLanguage('homeExplore') }} <i class="bi bi-arrow-right"></i></span></div></a>
        </div>
        @endforeach
        
       </div>
     </div>
   </section>

   <!-- Reviews -->
   <div class="trust-wrap">
   <section class="reviews-section" id="reviews">
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
                <img class="review-avatar" src="{{$item->avatar}}" alt="{{languageName($item->name)}}" loading="lazy">
                <div class="review-body">
                  <div class="review-quote">“</div><p class="review-text">{{languageName($item->content)}}</p><strong class="review-name">{{languageName($item->name)}}</strong><div class="review-stars" aria-label="{{ getLanguage('homeFiveStars') }}">★★★★★</div></div></article></div>
            @endforeach
           </div>
           <div class="swiper-pagination"></div>
         </div>
         <button class="reviews-nav reviews-prev" aria-label="{{ getLanguage('homeReviewPrev') }}"><i class="bi bi-chevron-left"></i></button>
         <button class="reviews-nav reviews-next" aria-label="{{ getLanguage('homeReviewNext') }}"><i class="bi bi-chevron-right"></i></button>
       </div>
     </div>
   </section>

   <!-- Why choose us -->
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
           $whyImage = $item->image ?? '';
           $whyImageUrl = $whyImage
             ? (preg_match('#^https?://#i', $whyImage) ? $whyImage : url($whyImage))
             : '';
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

   <!-- Strengths -->
   <section class="strengths-section section-pad">
     <div class="container">
       <div class="section-head">
         <h2 class="section-title title-leaf">{{ getLanguage('homeStrengthsTitle') }}</h2>
         <p class="section-subtitle mt-2">{{ getLanguage('homeStrengthsNote') }}</p>
       </div>
       <div class="row g-4 strengths-grid">
         @foreach ($strengths as $item)
         @php
           $strengthTitle = languageName($item->title);
           $strengthDesc = languageName($item->description);
           $strengthImage = $item->image ?? '';
           $strengthImageUrl = $strengthImage
             ? (preg_match('#^https?://#i', $strengthImage) ? $strengthImage : url($strengthImage))
             : '';
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

   <!-- Journal -->
   <section class="journal-section section-pad" id="journal">
     <div class="container">
       <div class="product-toolbar">
         <div class="section-head">
           <h2 class="section-title mt-3">{{ getLanguage('homeNewsTitle') }}</h2>
           <p class="section-subtitle">{{ getLanguage('homeNewsNote') }}</p>
         </div>
        <a href="{{ route('allListBlog') }}" class="btn-outline-brand">{{ getLanguage('homeViewAll') }} <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="row g-3">
        @foreach ($hotnews as $news)
        @php
          $newsTitle = languageName($news->title);
          $newsDesc = trim(strip_tags(languageName($news->description ?? '')));
          $newsImage = $news->image ?? '';
          $newsImageUrl = $newsImage
            ? (preg_match('#^https?://#i', $newsImage) ? $newsImage : url($newsImage))
            : '';
          $newsCate = $news->cate ? languageName($news->cate->name) : getLanguage('homeNewsFallback');
          $newsMeta = mb_strtoupper($newsCate, 'UTF-8');
          if ($news->created_at) {
            $newsMeta .= ' · ' . $news->created_at->format('d/m/Y');
          }
        @endphp
        <div class="col-md-4">
          <a href="{{ route('detailBlog', ['slug' => $news->slug]) }}" class="journal-card">
            @if ($newsImageUrl)
            <img src="{{ $newsImageUrl }}" alt="{{ $newsTitle }}" loading="lazy">
            @endif
            <div>
              <small>{{ $newsMeta }}</small>
              <h3>{{ $newsTitle }}</h3>
              @if ($newsDesc)
              <p>{{ \Illuminate\Support\Str::limit($newsDesc, 140) }}</p>
              @endif
            </div>
          </a>
        </div>
        @endforeach
      </div>
     </div>
   </section>

   <!-- Newsletter -->
   <section class="newsletter">
     <div class="container newsletter-inner">
       <div><span class="eyebrow text-warning mb-2">{{ getLanguage('homeNewsletterEyebrow') }}</span><h2>{{ getLanguage('homeNewsletterTitle') }}<br>{{ getLanguage('homeNewsletterTitle2') }}</h2><p>{{ getLanguage('homeNewsletterNote') }}</p></div>
       <form class="newsletter-form" id="newsletterForm"><input type="email" required placeholder="{{ getLanguage('homeEmailPlaceholder') }}" aria-label="{{ getLanguage('homeEmailLabel') }}"><button type="submit">{{ getLanguage('homeSubscribe') }} <i class="bi bi-arrow-right ms-1"></i></button></form>
     </div>
   </section>
 </main>
@endsection
