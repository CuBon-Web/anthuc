@extends('layouts.main.master')
@section('title')
@php
    $productLocale = session('locale', app()->getLocale());
    $productPageTitle = ($productLocale === 'vi' && !empty($product->seo_title)) ? $product->seo_title : languageName($product->name);
@endphp
    {{ $productPageTitle }}
@endsection
@section('description')
@php
    $productPageDescription = ($productLocale === 'vi' && !empty($product->meta_description)) ? $product->meta_description : languageName($product->description);
@endphp
    {{ $productPageDescription }}
@endsection
@section('image')
    @php
        $img = json_decode($product->images);
        $ungdung = json_decode($product->preserve);
    @endphp
    {{ url('' . $img[0]) }}
@endsection
@section('schema')
    @php
        $cleanText = function ($value) {
            $text = (string) $value;
            return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        };
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $toAbsoluteUrl = function ($path) {
            $value = trim((string) $path);
            if ($value === '') {
                return null;
            }
            if (preg_match('/^https?:\/\//i', $value)) {
                return $value;
            }
            return url($value);
        };

        $productUrl = url()->current();
        $homeUrl = route('home');
        $siteUrl = url('/');
        $categoryUrl = !empty($product->cate_slug) ? route('allListProCate', ['danhmuc' => $product->cate_slug]) : null;
        $siteName = $cleanText(config('app.name', 'Website'));
        $productName = $cleanText(languageName($product->name ?? ''));
        $productDescription = $cleanText($productLocale === 'vi' && !empty($product->meta_description) ? $product->meta_description : strip_tags(languageName($product->description)));
        $categoryName = $cleanText(languageName(optional($product->cate)->name ?? ''));
        $sku = $cleanText($product->sku ?? '');
        $allImages = array_values(array_filter(array_map($toAbsoluteUrl, (array) $img)));
        $primaryImage = $allImages[0] ?? null;

        $price = (float) ($product->price ?? 0);
        $discount = (float) ($product->discount ?? 0);
        $offerPrice = $discount > 0 && $discount < $price ? $discount : $price;
        if ($offerPrice <= 0) {
            $offerPrice = $discount > 0 ? $discount : $price;
        }

        $schemaGraph = [
            [
                '@type' => 'WebSite',
                '@id' => $siteUrl . '#website',
                'url' => $siteUrl,
                'name' => $siteName,
                'inLanguage' => 'vi-VN',
            ],
            [
                '@type' => 'Organization',
                '@id' => $siteUrl . '#organization',
                'name' => $siteName,
                'url' => $siteUrl,
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $productUrl . '#breadcrumb',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => getLanguage('home'),
                        'item' => $homeUrl,
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => $categoryName !== '' ? $categoryName : getLanguage('product'),
                        'item' => $categoryUrl ?: route('allProduct'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $productName,
                        'item' => $productUrl,
                    ],
                ],
            ],
            [
                '@type' => 'Product',
                '@id' => $productUrl . '#product',
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $productUrl,
                ],
                'name' => $productName,
                'description' => $productDescription,
                'url' => $productUrl,
                'sku' => $sku !== '' ? $sku : null,
                'category' => $categoryName !== '' ? $categoryName : null,
                'image' => $allImages,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $siteName,
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'url' => $productUrl,
                    'priceCurrency' => 'VND',
                    'price' => $offerPrice > 0 ? number_format($offerPrice, 0, '.', '') : null,
                    'availability' => 'https://schema.org/InStock',
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'seller' => [
                        '@type' => 'Organization',
                        '@id' => $siteUrl . '#organization',
                    ],
                ],
            ],
        ];

        if (!empty($primaryImage)) {
            $schemaGraph[1]['logo'] = [
                '@type' => 'ImageObject',
                'url' => $primaryImage,
            ];
        }

        if (empty($schemaGraph[3]['image'])) {
            unset($schemaGraph[3]['image']);
        }
        if (empty($schemaGraph[3]['sku'])) {
            unset($schemaGraph[3]['sku']);
        }
        if (empty($schemaGraph[3]['category'])) {
            unset($schemaGraph[3]['category']);
        }
        if (empty($schemaGraph[3]['offers']['price'])) {
            unset($schemaGraph[3]['offers']);
        }
    @endphp
    <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], $jsonFlags) !!}</script>
@endsection
@section('css')
<link rel="preload" as="style" type="text/css" href="{{ asset('frontend/css/product-detail.css') }}" />
<link href="{{ asset('frontend/css/product-detail.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('js')
<script src="{{ asset('frontend/js/product-detail.js') }}" type="text/javascript"></script>
<script src="{{ asset('frontend/js/compare.js') }}" type="text/javascript"></script>
@endsection
@section('content')
@php
    $productImages = array_values(array_filter((array) ($img ?? []), function ($path) {
        return trim((string) $path) !== '';
    }));
    $productTitle = languageName($product->name);
    $productLead = trim(strip_tags(languageName($product->description ?? '')));
    $originalPrice = (float) ($originalPrice ?? $product->price);
    $salePrice = (float) ($salePrice ?? $product->discount);
    $hasVariants = (int) $product->status_variant === 1 && !empty($variants);
    $displayPrice = ($salePrice > 0 && ($originalPrice <= 0 || $salePrice < $originalPrice)) ? $salePrice : $originalPrice;

    if ($hasVariants && !is_null($variantMinPrice ?? null) && !is_null($variantMaxPrice ?? null)) {
        if ($variantMinPrice > 0 && $variantMaxPrice > 0 && $variantMinPrice != $variantMaxPrice) {
            $priceLabel = number_format($variantMinPrice) . '₫ - ' . number_format($variantMaxPrice) . '₫';
        } elseif ($variantMinPrice > 0) {
            $priceLabel = number_format($variantMinPrice) . '₫';
            $displayPrice = (float) $variantMinPrice;
        } else {
            $priceLabel = getLanguage('contact');
        }
    } else {
        $priceLabel = $displayPrice > 0 ? number_format($displayPrice) . '₫' : getLanguage('contact');
    }
    $discountPercent = 0;
    if ($originalPrice > 0 && $salePrice > 0 && $salePrice < $originalPrice) {
        $discountPercent = 100 - (int) ceil(($salePrice / $originalPrice) * 100);
    }
    $hotline = trim((string) ($setting->phone1 ?? ''));
    $hotlineDigits = preg_replace('/[^0-9]/', '', $hotline);
    $facebookUrl = trim((string) ($setting->facebook ?? ''));
    $zaloSetting = isset($setting->zalo) ? trim((string) $setting->zalo) : '';
    $zaloUrl = $zaloSetting !== ''
        ? $zaloSetting
        : ($hotlineDigits ? 'https://zalo.me/' . $hotlineDigits : '');
    $zaloMessage = rawurlencode(getLanguage('pdConsult') . ': ' . $productTitle);
    if ($zaloUrl && strpos($zaloUrl, 'zalo.me') !== false) {
        $zaloUrl .= (strpos($zaloUrl, '?') !== false ? '&' : '?') . 'text=' . $zaloMessage;
    }
    $categoryUrl = !empty($product->cate_slug)
        ? route('allListProCate', ['danhmuc' => $product->cate_slug])
        : route('allProduct');
    $cateName = $product->cate ? languageName($product->cate->name) : '';

    $technicalSpecs = [];
    if (!empty($product->size)) {
        $decodedSpecs = json_decode($product->size, true);
        if (is_array($decodedSpecs)) {
            $technicalSpecs = collect($decodedSpecs)
                ->map(function ($item) {
                    return [
                        'title' => trim((string) languageName(data_get($item, 'title', ''))),
                        'detail' => trim((string) languageName(data_get($item, 'detail', ''))),
                    ];
                })
                ->filter(function ($item) {
                    return $item['title'] !== '' || $item['detail'] !== '';
                })
                ->values()
                ->all();
        }
    }
@endphp
<div class="bodywrap product-detail-page">
   <section class="bread-crumb">
      <div class="container">
         <ul class="breadcrumb">
            <li class="home">
               <a href="{{ route('home') }}"><span>{{ getLanguage('home') }}</span></a>
               <span class="mr_lr">
                  <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
               </span>
            </li>
            @if ($product->cate)
            <li>
               <a href="{{ $categoryUrl }}"><span>{{ $cateName }}</span></a>
               <span class="mr_lr">
                  <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
               </span>
            </li>
            @endif
            <li><strong><span>{{ $productTitle }}</span></strong></li>
         </ul>
      </div>
   </section>

   <section class="page product-detail-section">
      <div class="container">
         <div class="row product-detail-card">
            <div class="col-lg-6">
               <div class="product-detail-gallery">
                  @if ($discountPercent > 0)
                  <span class="product-detail-badge">-{{ $discountPercent }}%</span>
                  @endif
                  <div class="swiper product-detail-gallery-main">
                     <div class="swiper-wrapper">
                        @forelse ($productImages as $image)
                        <div class="swiper-slide">
                           <figure class="image-box">
                              <img src="{{ url($image) }}" alt="{{ $productTitle }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                           </figure>
                        </div>
                        @empty
                        <div class="swiper-slide">
                           <figure class="image-box image-box--empty">
                              <span>{{ getLanguage('pdNoImage') }}</span>
                           </figure>
                        </div>
                        @endforelse
                     </div>
                     @if (count($productImages) > 1)
                     <button type="button" class="product-detail-gallery-prev" aria-label="{{ getLanguage('pdImagePrev') }}"><i class="bi bi-chevron-left"></i></button>
                     <button type="button" class="product-detail-gallery-next" aria-label="{{ getLanguage('pdImageNext') }}"><i class="bi bi-chevron-right"></i></button>
                     @endif
                  </div>
                  @if (count($productImages) > 1)
                  <div class="swiper product-detail-gallery-thumbs">
                     <div class="swiper-wrapper">
                        @foreach ($productImages as $image)
                        <div class="swiper-slide">
                           <figure><img src="{{ url($image) }}" alt="{{ $productTitle }}" loading="lazy"></figure>
                        </div>
                        @endforeach
                     </div>
                  </div>
                  @endif
               </div>
            </div>

            <div class="col-lg-6 product-detail-summary">
               <p class="pd-kicker">{{ $cateName ?: getLanguage('pdBrandFallback') }}</p>
               <h1 class="product-detail-summary__title">{{ $productTitle }}</h1>
               @if ($productLead)
               <p class="product-detail-lead">{{ \Illuminate\Support\Str::limit($productLead, 220) }}</p>
               @endif

               <div class="product-detail-summary__price">
                  <span class="product-detail-summary__price-current" id="pd-price-current">{{ $priceLabel }}</span>
                  @if (!$hasVariants && $salePrice > 0 && $originalPrice > 0 && $salePrice < $originalPrice)
                  <del class="product-detail-summary__price-old" id="pd-price-old">{{ number_format($originalPrice) }}₫</del>
                  @elseif ($hasVariants && $originalPrice > 0 && ($variantMinPrice ?? 0) > 0 && $originalPrice > ($variantMinPrice ?? 0))
                  <del class="product-detail-summary__price-old" id="pd-price-old">{{ number_format($originalPrice) }}₫</del>
                  @else
                  <del class="product-detail-summary__price-old" id="pd-price-old" style="display:none;"></del>
                  @endif
               </div>

               <div class="product-detail-meta">
                  @if ($product->sku)
                  <div class="product-detail-meta__item">
                     <span class="product-detail-meta__label">{{ getLanguage('pdSku') }}:</span>
                     <span>{{ $product->sku }}</span>
                  </div>
                  @endif
                  @if ($product->cate)
                  <div class="product-detail-meta__item">
                     <span class="product-detail-meta__label">{{ getLanguage('shopCategory') }}:</span>
                     <a href="{{ $categoryUrl }}">{{ $cateName }}</a>
                  </div>
                  @endif
                  <div class="product-detail-meta__item">
                     <span class="product-detail-meta__label">{{ getLanguage('pdStockLabel') }}:</span>
                     <span class="product-detail-stock">{{ getLanguage('pdInStock') }}</span>
                  </div>
               </div>

               <form id="product-purchase-form" class="product-detail-purchase"
                  data-product-id="{{ $product->id }}"
                  data-add-cart-url="{{ route('add.to.cart') }}"
                  data-checkout-url="{{ route('checkout') }}"
                  data-status-variant="{{ (int) $product->status_variant }}"
                  data-original-price="{{ $originalPrice }}"
                  data-sale-price="{{ $salePrice }}"
                  data-contact-label="{{ getLanguage('contact') }}"
                  data-variants='@json($variants ?? [])'
                  data-variant-options='@json($variantOptions ?? [])'>

                  @if ($hasVariants && !empty($variantOptions))
                     @foreach ($variantOptions as $optIndex => $option)
                     <div class="pd-variant-group" data-option-index="{{ $optIndex }}">
                        <div class="pd-variant-label">
                           {{ $option['name'] }}: <span class="pd-variant-selected"></span>
                        </div>
                        <div class="pd-variant-options">
                           @foreach ($option['values'] as $valIndex => $value)
                           <label class="pd-variant-option">
                              <input type="radio"
                                 name="pd-option-{{ $optIndex }}"
                                 value="{{ $value }}"
                                 {{ $valIndex === 0 ? 'checked' : '' }}>
                              <span>{{ $value }}</span>
                           </label>
                           @endforeach
                        </div>
                     </div>
                     @endforeach
                  @endif

                  <div class="pd-quantity">
                     <span class="pd-quantity__label">{{ getLanguage('pdQuantity') }}:</span>
                     <div class="pd-quantity-control">
                        <button type="button" class="pd-qty-minus" aria-label="{{ getLanguage('pdQtyMinus') }}">−</button>
                        <input type="number" class="pd-quantity-input" value="1" min="1" max="999" aria-label="{{ getLanguage('pdQuantity') }}">
                        <button type="button" class="pd-qty-plus" aria-label="{{ getLanguage('pdQtyPlus') }}">+</button>
                     </div>
                  </div>

                  <div class="pd-actions">
                     <button type="button" class="pd-btn pd-btn--cart">{{ getLanguage('pdAddToCart') }}</button>
                     <button type="button" class="pd-btn pd-btn--buy">{{ getLanguage('pdBuyNow') }}</button>
                  </div>
               </form>

               @if ($hotline || $facebookUrl || $zaloUrl)
               <div class="product-detail-actions">
                  @if ($hotline)
                  <a href="tel:{{ $hotline }}" class="product-detail-actions__btn product-detail-actions__btn--phone" title="{{ getLanguage('pdCall') }} {{ $hotline }}">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.36 11.36 0 003.56.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1 11.36 11.36 0 00.57 3.56 1 1 0 01-.25 1.01l-2.2 2.22z"/></svg>
                     <span>{{ getLanguage('pdCallNow') }}</span>
                  </a>
                  @endif
                  @if ($facebookUrl)
                  <a href="{{ $facebookUrl }}" class="product-detail-actions__btn product-detail-actions__btn--facebook" target="_blank" rel="noopener noreferrer" title="{{ getLanguage('pdFacebook') }}">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 9h3l-1 4h-3v9h-4v-9H7V9h3V6.5C10 4 11.7 2 15 2h3v4h-2c-1.1 0-2 .9-2 2V9z"/></svg>
                     <span>{{ getLanguage('pdFacebook') }}</span>
                  </a>
                  @endif
                  @if ($zaloUrl)
                  <a href="{{ $zaloUrl }}" class="product-detail-actions__btn product-detail-actions__btn--zalo" target="_blank" rel="noopener noreferrer" title="{{ getLanguage('pdZalo') }}">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.48 2 2 5.82 2 10.5c0 2.69 1.37 5.08 3.5 6.63L4 22l5.28-2.64c.87.12 1.76.19 2.72.19 5.52 0 10-3.82 10-8.5S17.52 2 12 2z"/></svg>
                     <span>{{ getLanguage('pdZalo') }}</span>
                  </a>
                  @endif
               </div>
               @endif
            </div>
         </div>

         <div class="product-detail-tabs">
            <ul class="product-detail-tabs__nav" role="tablist">
               <li><button type="button" class="active" data-tab="tab-desc" role="tab">{{ getLanguage('pdDescription') }}</button></li>
               <li><button type="button" data-tab="tab-specs" role="tab">{{ getLanguage('pdSpecs') }}</button></li>
            </ul>
            <div class="product-detail-tabs__panel active" id="tab-desc" role="tabpanel">
               <div class="content-post">
                  {!! languageName($product->content) !!}
               </div>
            </div>
            <div class="product-detail-tabs__panel" id="tab-specs" role="tabpanel">
               @if (count($technicalSpecs) > 0)
               <table class="product-specs-table">
                  <tbody>
                     @foreach ($technicalSpecs as $spec)
                     <tr>
                        <td>{{ $spec['title'] !== '' ? $spec['title'] : getLanguage('pdSpecFallback') }}</td>
                        <td>{{ $spec['detail'] !== '' ? $spec['detail'] : '-' }}</td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
               @else
               <p class="product-specs-empty">{{ getLanguage('pdNoSpecs') }}</p>
               @endif
            </div>
         </div>

         @if (count($productlq) > 0)
         <div class="product-detail-related">
            <p class="pd-kicker">{{ getLanguage('pdRelatedKicker') }}</p>
            <h2 class="product-detail-related__title">{{ getLanguage('pdRelatedTitle') }}</h2>
            <div class="row row-fix">
               @foreach ($productlq as $item)
               <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6 col-6 col-fix">
                  @include('layouts.product.item', ['pro' => $item])
               </div>
               @endforeach
            </div>
         </div>
         @endif
      </div>
   </section>
   @include('layouts.product.compare-sidebar')
</div>
@endsection
