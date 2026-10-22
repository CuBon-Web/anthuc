@php
    $footerLocale = session('locale', app()->getLocale());
    $footerPages = \App\models\PageContent::where('status', 1)
        ->where('language', $footerLocale)
        ->orderBy('id')
        ->get();
    if ($footerPages->isEmpty()) {
        $footerPages = collect($pageContent ?? []);
    }

    $footerBrand = getLanguage('pdBrandFallback');
    $footerPhone = trim((string) ($setting->phone1 ?? ''));
    $footerPhoneDigits = preg_replace('/[^0-9+]/', '', $footerPhone);
    $footerEmail = trim((string) ($setting->email ?? ''));
    $footerAddress = trim((string) ($setting->address2 ?? ''));
    if ($footerAddress === '') {
        $footerAddress = trim((string) ($setting->address1 ?? ''));
    }
    $footerIntroHtml = trim((string) ($setting->webname ?? ''));
    $footerIntroText = trim((string) ($setting->company ?? ''));
    $footerLogo = trim((string) ($setting->logo_footer ?? ''));
    if ($footerLogo === '') {
        $footerLogo = trim((string) ($setting->logo ?? ''));
    }
    if ($footerLogo !== '' && !preg_match('#^https?://#i', $footerLogo)) {
        $footerLogo = url($footerLogo);
    }

    $footerUrl = function ($url) {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return '';
        }
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        return $url;
    };

    $footerSocials = array_values(array_filter([
        ['url' => $footerUrl($setting->facebook ?? ''), 'label' => getLanguage('pdFacebook'), 'icon' => 'bi-facebook'],
        ['url' => $footerUrl($setting->fbPixel ?? ''), 'label' => getLanguage('footerInstagram'), 'icon' => 'bi-instagram'],
        ['url' => $footerUrl($setting->google ?? ''), 'label' => getLanguage('footerTikTok'), 'icon' => 'bi-tiktok'],
    ], function ($item) {
        return $item['url'] !== '';
    }));

    $footerAboutLinks = [
        ['href' => route('aboutUs'), 'label' => getLanguage('aboutBrandStory')],
        ['href' => route('home') . '#process', 'label' => getLanguage('footerProcess')],
        ['href' => route('home') . '#why', 'label' => getLanguage('footerValues')],
        ['href' => route('allListBlog'), 'label' => getLanguage('homeNewsTitle')],
        ['href' => route('lienHe'), 'label' => getLanguage('contact')],
    ];
    foreach ($footerPages->where('type', 've-chung-toi') as $page) {
        $footerAboutLinks[] = [
            'href' => route('pagecontent', ['slug' => $page->slug]),
            'label' => languageName($page->title),
        ];
    }

    $footerSupportLinks = [];
    foreach ($footerPages->where('type', 'ho-tro-khanh-hang') as $page) {
        $footerSupportLinks[] = [
            'href' => route('pagecontent', ['slug' => $page->slug]),
            'label' => languageName($page->title),
        ];
    }
    $footerSupportLinks[] = ['href' => route('fag'), 'label' => getLanguage('footerFaq')];

    $footerTerms = $footerPages->firstWhere('slug', 'dieu-khoan-su-dung');
    $footerPrivacy = $footerPages->firstWhere('slug', 'chinh-sach-bao-mat');
@endphp
<footer class="site-footer" id="footer">
   <div class="container footer-content">
     <div class="row g-4">
       <div class="col-lg-4 col-md-6 footer-brand">
         <a class="brand-lockup" href="{{ route('home') }}">
           @if ($footerLogo !== '')
           <img class="footer-logo" src="{{ $footerLogo }}" alt="{{ $footerBrand }}" width="72">
           @else
           <span class="brand-symbol"><i class="bi bi-flower3"></i></span>
           @endif
         </a>
         @if ($footerIntroHtml !== '')
         <div class="footer-intro">{!! $footerIntroHtml !!}</div>
         @elseif ($footerIntroText !== '')
         <p>{{ $footerIntroText }}</p>
         @else
         <p>{{ getLanguage('footerBrandNote') }}</p>
         @endif
         @if (count($footerSocials))
         <div class="social-links">
           @foreach ($footerSocials as $social)
           <a href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] }}"><i class="bi {{ $social['icon'] }}"></i></a>
           @endforeach
         </div>
         @endif
       </div>
       <div class="col-6 col-lg-2 col-md-3 footer-col">
         <h3>{{ getLanguage('footerAbout') }}</h3>
         @foreach ($footerAboutLinks as $link)
         <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
         @endforeach
       </div>
       <div class="col-6 col-lg-2 col-md-3 footer-col">
         <h3>{{ getLanguage('footerSupport') }}</h3>
         @foreach ($footerSupportLinks as $link)
         <a href="{{ $link['href'] }}">{{ $link['label'] }}</a>
         @endforeach
       </div>
       <div class="col-lg-4 col-md-6 footer-col">
         <h3>{{ getLanguage('footerContactInfo') }}</h3>
         @if ($footerPhone !== '')
         <p><i class="bi bi-telephone"></i><a href="tel:{{ $footerPhoneDigits }}">{{ $footerPhone }}</a></p>
         @endif
         @if ($footerEmail !== '')
         <p><i class="bi bi-envelope"></i><a href="mailto:{{ $footerEmail }}">{{ $footerEmail }}</a></p>
         @endif
         @if ($footerAddress !== '')
         <p><i class="bi bi-geo-alt"></i>{{ $footerAddress }}</p>
         @endif
         <p><i class="bi bi-clock"></i>{{ getLanguage('footerHours') }}</p>
       </div>
     </div>
   </div>
   <div class="footer-bottom"><div class="container"><span>© <span id="year"></span> {{ $footerBrand }}. {{ getLanguage('footerRights') }}</span><span><a href="{{ $footerTerms ? route('pagecontent', ['slug' => $footerTerms->slug]) : '#footer' }}">{{ getLanguage('footerTerms') }}</a> &nbsp; | &nbsp; <a href="{{ $footerPrivacy ? route('pagecontent', ['slug' => $footerPrivacy->slug]) : '#footer' }}">{{ getLanguage('footerPrivacy') }}</a> &nbsp; | &nbsp; {{ getLanguage('footerCredit') }}</span></div></div>
 </footer>
