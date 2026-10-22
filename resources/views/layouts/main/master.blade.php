{{-- https://bean-tools.mysapo.net/ --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', session('locale', app()->getLocale())) }}">
   <head>
      @php
      $seoCanonical = trim($__env->yieldContent('canonical')) ?: seo_canonical_url();
      $seoRobots = trim($__env->yieldContent('robots')) ?: seo_robots_directive();
      $seoTitle = trim($__env->yieldContent('title'));
      $seoDescription = trim($__env->yieldContent('description'));
      $seoImage = trim($__env->yieldContent('image'));
      $seoSiteName = $setting->webname ?? ($setting->company ?? config('app.name'));
      @endphp
      <meta charset="UTF-8" />
      <meta name="theme-color" content="#0A3A82">
      <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title>{{ $seoTitle }}</title>
      <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
      <meta http-equiv="Content-Language" content="vi" />
      <link rel="alternate" href="{{ $seoCanonical }}" hreflang="vi" />
      <meta name="description" content="{{ $seoDescription }}">
      <meta name="robots" content="{{ $seoRobots }}" />
      <meta name="googlebot" content="{{ $seoRobots }}">
      <meta name="revisit-after" content="3 days" />
      <meta name="rating" content="General">
      <meta name="application-name" content="{{ $seoSiteName }}" />
      <meta name="theme-color" content="#0A3A82" />
      <meta name="msapplication-TileColor" content="#0A3A82" />
      <meta name="apple-mobile-web-app-capable" content="yes" />
      <meta name="apple-mobile-web-app-title" content="{{ $seoSiteName }}" />
      @if ($seoImage)
      <link rel="apple-touch-icon-precomposed" href="{{ $seoImage }}" sizes="700x700">
      @endif
      <meta property="og:url" content="{{ $seoCanonical }}">
      <meta property="og:title" content="{{ $seoTitle }}">
      <meta property="og:description" content="{{ $seoDescription }}">
      <meta property="og:image" content="{{ $seoImage }}">
      <meta property="og:site_name" content="{{ $seoSiteName }}">
      <meta property="og:image:alt" content="{{ $seoTitle }}">
      <meta property="og:type" content="website" />
      <meta property="og:locale" content="vi_VN" />
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content="{{ $seoTitle }}" />
      <meta name="twitter:description" content="{{ $seoDescription }}" />
      <meta name="twitter:image" content="{{ $seoImage }}" />
      <meta name="twitter:url" content="{{ $seoCanonical }}" />
      <meta itemprop="name" content="{{ $seoTitle }}">
      <meta itemprop="description" content="{{ $seoDescription }}">
      <meta itemprop="image" content="{{ $seoImage }}">
      <meta itemprop="url" content="{{ $seoCanonical }}">
      <link rel="canonical" href="{{ $seoCanonical }}">
      @if ($seoImage)
      <link rel="image_src" href="{{ $seoImage }}" />
      @endif
      <link rel="shortcut icon" href="{{ url('' . $setting->favicon) }}" type="image/x-icon">
      <link rel="icon" href="{{ url('' . $setting->favicon) }}" type="image/x-icon">
      <meta name="csrf-token" content="{{ csrf_token() }}" />
      @hasSection('schema')
      @yield('schema')
      @else
      @include('partials.seo-organization')
      @endif
      <!-- Styles Include -->
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
      
      <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}">
      @yield('css')
   </head>
   <body>
      @include('layouts.header.index')
    
     @yield('content')
    
      @include('layouts.footer.index')
      
    
      <button class="back-to-top" id="backToTop" aria-label="{{ getLanguage('masterBackToTop') }}"><i class="bi bi-arrow-up"></i></button>
    
      <!-- Floating contact buttons -->
      @php
        $floatPhone = trim((string) ($setting->phone1 ?? ''));
        $floatPhoneDigits = preg_replace('/[^0-9+]/', '', $floatPhone);
        $floatPhoneLabel = $floatPhone !== '' ? $floatPhone : '090 123 4567';
        $floatTel = $floatPhoneDigits !== '' ? $floatPhoneDigits : '0901234567';
        $floatZalo = trim((string) ($setting->zalo ?? ''));
        if ($floatZalo === '' && $floatPhoneDigits !== '') {
            $floatZalo = 'https://zalo.me/' . $floatPhoneDigits;
        }
        if ($floatZalo === '') {
            $floatZalo = 'https://zalo.me/0901234567';
        }
        $floatMessenger = trim((string) ($setting->messenger ?? ''));
        if ($floatMessenger === '') {
            $floatMessenger = 'https://m.me/anthuc';
        }
      @endphp
      <div class="call-buttons" aria-label="{{ getLanguage('masterQuickContact') }}">
        <a class="call-btn call-zalo" href="{{ $floatZalo }}" target="_blank" rel="noopener" aria-label="{{ getLanguage('masterChatZalo') }}">
          <span class="call-icon">{{ getLanguage('pdZalo') }}</span>
          <span class="call-label">{{ getLanguage('masterChatZalo') }}</span>
        </a>
        <a class="call-btn call-facebook" href="{{ $floatMessenger }}" target="_blank" rel="noopener" aria-label="{{ getLanguage('masterChatFacebook') }}">
          <span class="call-icon"><i class="bi bi-messenger"></i></span>
          <span class="call-label">{{ getLanguage('masterChatFacebook') }}</span>
        </a>
        <a class="call-btn call-hotline" href="tel:{{ $floatTel }}" aria-label="{{ getLanguage('masterCallHotline') }} {{ $floatPhoneLabel }}">
          <span class="call-icon"><i class="bi bi-telephone-fill"></i></span>
          <span class="call-label"><small>{{ getLanguage('masterHotlineAdvise') }}</small>{{ $floatPhoneLabel }}</span>
        </a>
      </div>
    
      <!-- Cart drawer -->
      @php
        $sessionCart = session('cart', []);
        $sessionTotal = 0;
        foreach ($sessionCart as $cartItem) {
            $sessionTotal += (float) ($cartItem['price'] ?? 0) * (int) ($cartItem['quantity'] ?? 1);
        }
      @endphp
      <div class="offcanvas offcanvas-end" tabindex="-1" id="cartCanvas" aria-labelledby="cartCanvasLabel" data-checkout-url="{{ route('checkout') }}" data-update-url="{{ route('update.cart') }}" data-remove-url="{{ route('remove.from.cart') }}" data-label-empty="{{ getLanguage('cartEmpty') }}" data-label-remove="{{ getLanguage('cartRemove') }}" data-label-qty-minus="{{ getLanguage('pdQtyMinus') }}" data-label-qty-plus="{{ getLanguage('pdQtyPlus') }}" data-label-empty-toast="{{ getLanguage('cartEmptyShort') }}">
        <div class="offcanvas-header bg-cream"><h5 class="offcanvas-title font-serif text-forest" id="cartCanvasLabel">{{ getLanguage('cartTitle') }}</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ getLanguage('masterClose') }}"></button></div>
        <div class="offcanvas-body">
          <div id="cartItems" class="small">
            @forelse ($sessionCart as $cartKey => $cartItem)
              @php
                $unitPrice = (float) ($cartItem['price'] ?? 0);
                $lineQty = (int) ($cartItem['quantity'] ?? 1);
                $thumb = (string) ($cartItem['image'] ?? '');
                if ($thumb !== '' && !preg_match('#^https?://#i', $thumb)) {
                    $thumb = url($thumb);
                }
                $cartName = languageName($cartItem['name'] ?? '');
              @endphp
              <div class="d-flex gap-3 align-items-start mb-3">
                <img src="{{ $thumb ?: asset('frontend/images/lazy.png') }}" alt="" width="64" height="64" class="rounded flex-shrink-0" style="width:64px;height:64px;object-fit:cover;background:#f6efe0">
                <div class="flex-grow-1">
                  <strong class="d-block text-forest">{{ $cartName }}</strong>
                  @if (!empty($cartItem['variant']))
                  <small class="d-block text-secondary">{{ $cartItem['variant'] }}</small>
                  @endif
                  <small class="text-secondary">{{ number_format($unitPrice) }}₫</small>
                  <div class="d-flex align-items-center gap-2 mt-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary cart-drawer-qty px-2 py-0" data-cart-key="{{ $cartKey }}" data-qty="{{ max(1, $lineQty - 1) }}" aria-label="{{ getLanguage('pdQtyMinus') }}">−</button>
                    <span>{{ $lineQty }}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary cart-drawer-qty px-2 py-0" data-cart-key="{{ $cartKey }}" data-qty="{{ $lineQty + 1 }}" aria-label="{{ getLanguage('pdQtyPlus') }}">+</button>
                    <button type="button" class="btn btn-link btn-sm text-secondary p-0 ms-1 cart-drawer-remove" data-cart-key="{{ $cartKey }}">{{ getLanguage('cartRemove') }}</button>
                  </div>
                </div>
                <span class="fw-semibold text-nowrap">{{ number_format($unitPrice * $lineQty) }}₫</span>
              </div>
            @empty
              <span class="text-secondary">{{ getLanguage('cartEmpty') }}</span>
            @endforelse
          </div>
          <hr>
          <div class="d-flex justify-content-between fw-semibold mb-3"><span>{{ getLanguage('cartSubtotal') }}</span><span id="cartTotal">{{ number_format($sessionTotal) }}₫</span></div>
          <button class="btn-brand w-100" id="checkoutBtn" type="button">{{ getLanguage('cartContinue') }} <i class="bi bi-arrow-right"></i></button>
        </div>
      </div>
    
      <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="siteToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
          <div class="d-flex"><div class="toast-body" id="toastMessage">{{ getLanguage('masterToastSuccess') }}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ getLanguage('masterClose') }}"></button></div>
        </div>
      </div>
    
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
     <script src="{{ asset('frontend/js/main.js') }}"></script>
     @yield('js')
      <style>
        .search-highlight { outline: 2px solid var(--gold); outline-offset: 4px; }
      </style>
    </body>
</html>