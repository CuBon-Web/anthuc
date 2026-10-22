<!-- Announcement bar -->
<div class="topbar">
   <div class="container">
     <span><i class="bi bi-flower1 me-2"></i>{{$setting->company}}</span>
     <span class="topbar-contact"><i class="bi bi-telephone me-1"></i>Hỗ trợ khách hàng: {{$setting->phone1}}</span>
   </div>
 </div>

 <!-- Header -->
 <header class="site-header" id="siteHeader">
   <div class="container">
     <div class="nav-inner">
       <button class="icon-btn mobile-menu-btn" id="mobileMenuBtn" aria-label="Mở menu" aria-expanded="false"><i class="bi bi-list"></i></button>
       <a class="brand-lockup" href="{{route('home')}}" aria-label="An Thực - Trang chủ">
         <img width="150" src="{{url($setting->logo)}}" alt="An Thực - Trang chủ">
       </a>
       <nav class="main-nav" id="mainNav" aria-label="Điều hướng chính">
         <div class="mobile-nav-head">
           <span class="brand-lockup">
             <span class="brand-symbol"><i class="bi bi-flower3"></i></span>
             <span><span class="brand-name">An Thực</span><span class="brand-tagline">An Tâm Hòa Thục</span></span>
           </span>
           <button class="mobile-nav-close" id="mobileNavClose" type="button" aria-label="Đóng menu"><i class="bi bi-x-lg"></i></button>
         </div>
         <span class="mobile-nav-label">Danh mục</span>
         <a class="active" href="{{route('home')}}">{{getLanguage('home')}}</a>
         <a href="{{ route('aboutUs') }}">{{getLanguage('anthustory')}}</a>
         @if (isset($categoryhome) && $categoryhome->count())
         <div class="nav-drop">
           <div class="nav-drop-head">
             <a href="{{ route('allProduct') }}">{{getLanguage('product')}}<i class="bi bi-chevron-down nav-caret"></i></a>
             <button class="nav-drop-btn" type="button" aria-expanded="false" aria-label="Mở danh mục sản phẩm"><i class="bi bi-chevron-down"></i></button>
           </div>
           <div class="nav-drop-panel">
             @foreach ($categoryhome as $item)
             <div class="nav-drop-item{{ $item->typeCate->count() ? ' has-child' : '' }}">
               <div class="nav-drop-row">
                 <a href="{{ route('allListProCate', ['danhmuc' => $item->slug]) }}" title="{{ languageName($item->name) }}">
                   <span>{{ languageName($item->name) }}</span>
                   @if ($item->typeCate->count())
                   <i class="bi bi-chevron-right nav-sub-caret"></i>
                   @endif
                 </a>
                 @if ($item->typeCate->count())
                 <button class="nav-sub-btn" type="button" aria-expanded="false" aria-label="Mở {{ languageName($item->name) }}"><i class="bi bi-chevron-down"></i></button>
                 @endif
               </div>
               @if ($item->typeCate->count())
               <div class="nav-drop-sub">
                 @foreach ($item->typeCate as $type)
                 <a href="{{ route('allListType', ['danhmuc' => $item->slug, 'loaidanhmuc' => $type->slug]) }}" title="{{ languageName($type->name) }}">{{ languageName($type->name) }}</a>
                 @endforeach
               </div>
               @endif
             </div>
             @endforeach
           </div>
         </div>
         @else
         <a href="{{ route('allProduct') }}">{{getLanguage('product')}}</a>
         @endif
         @foreach ($blogCate as $item)
         @if ($item->typeCate->count())
         <div class="nav-drop nav-blog">
           <div class="nav-drop-head">
             <a href="{{ route('listCateBlog', ['slug' => $item->slug]) }}">{{ languageName($item->name) }} <i class="bi bi-chevron-down nav-caret"></i></a>
             <button class="nav-drop-btn" type="button" aria-expanded="false" aria-label="Mở {{ languageName($item->name) }}"><i class="bi bi-chevron-down"></i></button>
           </div>
           <div class="nav-drop-panel">
             @foreach ($item->typeCate as $type)
             <a href="{{ route('listTypeBlog', ['slug' => $type->slug]) }}" title="{{ languageName($type->name) }}">{{ languageName($type->name) }}</a>
             @endforeach
           </div>
         </div>
         @else
         <a href="{{ route('listCateBlog', ['slug' => $item->slug]) }}">{{ languageName($item->name) }}</a>
         @endif
         @endforeach
         
         <a href="{{ route('lienHe') }}">{{getLanguage('contact')}}</a>
         <div class="mobile-nav-foot">
           <a class="mobile-nav-hotline" href="tel:{{$setting->phone1}}"><i class="bi bi-telephone"></i><span><small>Hỗ trợ khách hàng</small>{{$setting->phone1}}</span></a>
           <div class="mobile-nav-social">
             <a href="{{$setting->facebook}}" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
             <a href="{{$setting->instagram}}" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
             <a href="{{$setting->tiktok}}" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
             <a href="{{$setting->youtube}}" aria-label="Youtube"><i class="bi bi-youtube"></i></a>
           </div>
         </div>
       </nav>
       <div class="header-actions">
         <button class="icon-btn" id="searchToggle" type="button" aria-label="Tìm kiếm" aria-expanded="false" aria-controls="searchPanel"><i class="bi bi-search"></i></button>
         <button class="icon-btn" aria-label="Giỏ hàng" data-bs-toggle="offcanvas" data-bs-target="#cartCanvas"><i class="bi bi-bag"></i><span class="cart-count" id="cartCount">{{ collect(session('cart', []))->sum('quantity') }}</span></button>
      
        <div class="language-select">
          @php
            $activeLanguages = collect($lang ?? [])->where('status', 1)->sortBy('priority')->values();
            $currentLocale = session('locale', app()->getLocale());
            $currentLanguage = $activeLanguages->firstWhere('code', $currentLocale) ?: $activeLanguages->first();
          @endphp
          @if ($activeLanguages->count())
          <details>
            <summary aria-label="Chọn ngôn ngữ">
              @if (!empty($currentLanguage->flagIcon))
              <img src="{{ url($currentLanguage->flagIcon) }}" alt="">
              @endif
              <span>{{ strtoupper(substr($currentLanguage->code, 0, 2)) }}</span>
              <i class="bi bi-chevron-down"></i>
            </summary>
            <div class="language-select__menu">
              @foreach ($activeLanguages as $item)
              <form method="POST" action="{{ route('languages') }}">
                @csrf
                <input type="hidden" name="code" value="{{ $item->code }}">
                <button type="submit" class="{{ $item->code === $currentLocale ? 'is-active' : '' }}">
                  @if (!empty($item->flagIcon))
                  <img src="{{ url($item->flagIcon) }}" alt="">
                  @endif
                  <span>{{ $item->name }}</span>
                </button>
              </form>
              @endforeach
            </div>
          </details>
          @endif
        </div>
        </div>
     </div>
     <div class="search-panel d-none" id="searchPanel">
       <form class="search-form" id="searchForm" action="{{ route('search_result') }}" method="get" role="search" data-suggest-url="{{ route('autosearchcomplete') }}">
         <input class="search-form__input" id="searchInput" name="keyword" type="search" placeholder="{{getLanguage('searchproduct')}}" aria-label="Tìm sản phẩm" autocomplete="off" spellcheck="false" value="{{ request()->routeIs('search_result') ? request('keyword') : '' }}">
         <button class="btn-brand search-form__submit" type="submit">{{getLanguage('search')}} <i class="bi bi-search"></i></button>
         <div class="search-suggest" id="searchSuggest" hidden></div>
       </form>
     </div>
   </div>
 </header>
 <div class="nav-overlay" id="navOverlay"></div>
 <!-- Mobile sticky navigation -->
 <nav class="mobile-bottom-bar" aria-label="Điều hướng nhanh">
   <a href="#home"><i class="bi bi-house"></i><span>{{getLanguage('home')}}</span></a>
   <a href="#products"><i class="bi bi-grid"></i><span>{{getLanguage('product')}}</span></a>
   <a href="#story"><i class="bi bi-flower1"></i><span>{{getLanguage('brand')}}</span></a>
   <a href="#footer"><i class="bi bi-chat-dots"></i><span>{{getLanguage('contact')}}</span></a>
 </nav>