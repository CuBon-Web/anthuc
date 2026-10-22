@extends('layouts.main.master')
@section('title')
{{ $title }}
@endsection
@section('description')
{{ getLanguage('shopListPrefix') }} {{ $title }}
@endsection
@section('image')
@php
    $listOgImage = isset($banner[0]) && !empty($banner[0]->image)
        ? url($banner[0]->image)
        : url($setting->logo ?? '');
@endphp
{{ $listOgImage }}
@endsection
@section('css')
<link rel="preload" as="style" type="text/css" href="{{ asset('frontend/css/shop-list.css') }}" />
<link href="{{ asset('frontend/css/shop-list.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('js')
<script>
        let shopGridCols = 4;
        let shopFilterTimer = null;
        let shopFilterToken = 0;

        function initProductFilters() {
        const keywordInput = document.getElementById('js-dom-filter-keyword');
        const clearBtn = document.getElementById('js-dom-filter-clear');
        const resultCount = document.getElementById('js-dom-filter-count');
        const sortSelect = document.getElementById('js-dom-sort-select');
        const minPriceInput = document.getElementById('js-dom-filter-price-min');
        const maxPriceInput = document.getElementById('js-dom-filter-price-max');
        const applyPriceBtn = document.getElementById('js-dom-filter-price-apply');
        const productRow = document.querySelector('.all-products .product-list-row');
        const paginationWrap = document.querySelector('.shop-pagination');
        const listMain = document.querySelector('.shop-list-main');
        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        function tagCheckboxes() {
            return Array.from(document.querySelectorAll('.js-tag-filter'));
        }

        function updateVisibleCount(count) {
            const total = Number(count || 0);
            if (resultCount) resultCount.textContent = total;
            const toolbarCount = document.getElementById('js-dom-filter-count-toolbar');
            if (toolbarCount) toolbarCount.textContent = total;
        }

        function getFilterState() {
            const groups = {};
            tagCheckboxes().forEach((checkbox) => {
                if (!checkbox.checked) return;
                const group = checkbox.dataset.group || 'tags';
                if (!groups[group]) groups[group] = [];
                groups[group].push(checkbox.value);
            });
            return {
                groups: groups,
                keyword: keywordInput ? keywordInput.value.trim() : '',
                minPrice: minPriceInput && minPriceInput.value !== '' ? Number(minPriceInput.value) : null,
                maxPrice: maxPriceInput && maxPriceInput.value !== '' ? Number(maxPriceInput.value) : null,
                sortValue: sortSelect ? sortSelect.value : 'newest'
            };
        }

        function applyGridColumns(cols) {
            if (!productRow) return;
            const gridClassMap = {
                2: ['col-lg-6', 'col-md-6', 'col-sm-6', 'col-6'],
                3: ['col-xl-4', 'col-lg-4', 'col-md-4', 'col-sm-6', 'col-6'],
                4: ['col-xl-3', 'col-lg-3', 'col-md-4', 'col-sm-6', 'col-6']
            };
            const gridRemoveClasses = [
                'col-xl-3', 'col-xl-4', 'col-lg-3', 'col-lg-4', 'col-lg-6',
                'col-md-4', 'col-md-6', 'col-sm-6', 'col-6'
            ];
            const classes = gridClassMap[cols] || gridClassMap[4];
            productRow.querySelectorAll('.product-list-col.item').forEach((item) => {
                item.classList.remove(...gridRemoveClasses);
                classes.forEach((className) => item.classList.add(className));
            });
        }

        function applyDomFilter(page, immediate) {
            if (!productRow) return;
            window.clearTimeout(shopFilterTimer);
            const run = function () {
                const state = getFilterState();
                const sortMap = {
                    newest: 'created-desc',
                    oldest: 'created-oldest',
                    price_asc: 'price-asc',
                    price_desc: 'price-desc'
                };
                const token = ++shopFilterToken;
                productRow.classList.add('is-loading');
                fetch('{{ route('filterProduct') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                    },
                    body: JSON.stringify({
                        fillter: state.groups,
                        sortby: sortMap[state.sortValue] || 'created-desc',
                        pricemin: state.minPrice,
                        pricemax: state.maxPrice,
                        keyword: state.keyword,
                        cate: productRow.dataset.cate || '',
                        type: productRow.dataset.type || '',
                        typetwo: productRow.dataset.typetwo || '',
                        per_page: Number(productRow.dataset.perPage || 12),
                        page: page || 1
                    })
                })
                .then((response) => {
                    if (!response.ok) throw new Error('filter failed');
                    return response.json();
                })
                .then((response) => {
                    if (token !== shopFilterToken) return;
                    productRow.innerHTML = response.items_html || '';
                    if (paginationWrap) paginationWrap.innerHTML = response.pagination_html || '';
                    applyGridColumns(shopGridCols);
                    updateVisibleCount(response.total || 0);
                })
                .catch(() => {})
                .then(() => {
                    if (token === shopFilterToken) productRow.classList.remove('is-loading');
                });
            };
            if (immediate) run();
            else shopFilterTimer = window.setTimeout(run, 280);
        }

        document.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('js-tag-filter')) {
                applyDomFilter(1, true);
            }
        });
        if (keywordInput) {
            keywordInput.addEventListener('input', function () { applyDomFilter(1, false); });
            keywordInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyDomFilter(1, true);
                }
            });
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', function () { applyDomFilter(1, true); });
        }
        if (applyPriceBtn) {
            applyPriceBtn.addEventListener('click', function (event) {
                event.preventDefault();
                applyDomFilter(1, true);
            });
        }
        [minPriceInput, maxPriceInput].forEach(function (input) {
            if (!input) return;
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyDomFilter(1, true);
                }
            });
        });
        if (clearBtn) {
            clearBtn.addEventListener('click', function (event) {
                event.preventDefault();
                tagCheckboxes().forEach((checkbox) => { checkbox.checked = false; });
                if (keywordInput) keywordInput.value = '';
                if (minPriceInput) minPriceInput.value = '';
                if (maxPriceInput) maxPriceInput.value = '';
                if (sortSelect) sortSelect.value = 'newest';
                applyDomFilter(1, true);
            });
        }
        document.addEventListener('click', function (event) {
            const link = event.target.closest('.shop-pagination a');
            if (!link) return;
            event.preventDefault();
            const url = new URL(link.href, window.location.origin);
            applyDomFilter(Number(url.searchParams.get('page') || 1), true);
            if (listMain) listMain.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        window.shopApplyGridColumns = applyGridColumns;
    }

    function initShopListUi() {
        const filterToggle = document.querySelector('.js-shop-filter-toggle');
        const sidebarClose = document.getElementById('js-shop-sidebar-close');
        const sidebarOverlay = document.getElementById('js-shop-sidebar-overlay');
        const productRow = document.querySelector('.all-products .product-list-row');
        const gridViewItems = document.querySelectorAll('.grid-view li');

        function closeSidebar() {
            document.body.classList.remove('shop-filter-open');
            if (filterToggle) {
                filterToggle.setAttribute('aria-expanded', 'false');
            }
        }

        function openSidebar() {
            document.body.classList.add('shop-filter-open');
            if (filterToggle) {
                filterToggle.setAttribute('aria-expanded', 'true');
            }
        }

        if (filterToggle) {
            filterToggle.addEventListener('click', function () {
                if (document.body.classList.contains('shop-filter-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });
        }
        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }

        const gridClassMap = {
            2: ['col-lg-6', 'col-md-6', 'col-sm-6', 'col-6'],
            3: ['col-xl-4', 'col-lg-4', 'col-md-4', 'col-sm-6', 'col-6'],
            4: ['col-xl-3', 'col-lg-3', 'col-md-4', 'col-sm-6', 'col-6'],
        };
        const gridRemoveClasses = [
            'col-xl-3', 'col-xl-4', 'col-lg-3', 'col-lg-4', 'col-lg-6',
            'col-md-4', 'col-md-6', 'col-sm-6', 'col-6'
        ];

        function applyGridColumns(cols) {
            if (!productRow) return;
            const classes = gridClassMap[cols] || gridClassMap[4];
            productRow.querySelectorAll('.product-list-col.item').forEach((item) => {
                item.classList.remove(...gridRemoveClasses);
                classes.forEach((className) => item.classList.add(className));
            });
        }

        gridViewItems.forEach((item) => {
            item.addEventListener('click', function () {
                gridViewItems.forEach((el) => el.classList.remove('active'));
                item.classList.add('active');
                const cols = Number(item.dataset.cols || 4);
                shopGridCols = cols;
                applyGridColumns(cols);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initProductFilters();
            initShopListUi();
        });
    } else {
        initProductFilters();
        initShopListUi();
    }
</script>
@endsection
@section('content')
@php
    $seoContent = languageName($content ?? '');
    $hasSeoContent = trim(strip_tags($seoContent)) !== '' && $seoContent !== 'none';
@endphp
<div class="bodywrap shop-list-page">
   <section class="bread-crumb">
      <div class="container">
         <ul class="breadcrumb">
            <li class="home">
               <a href="{{ route('home') }}"><span>{{ getLanguage('home') }}</span></a>
               <span class="mr_lr">
                  <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
               </span>
            </li>
            @if (!empty($cate_name ?? ''))
            <li>
               <a href="{{ route('allListProCate', ['danhmuc' => $cate_slug ?? '']) }}"><span>{{ $cate_name }}</span></a>
               <span class="mr_lr">
                  <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
               </span>
            </li>
            @endif
            <li><strong><span>{{ $title }}</span></strong></li>
         </ul>
      </div>
   </section>

   <section class="page shop-list-section">
      <div class="container">
         <div class="wrap_background_aside padding-top-15 margin-bottom-40 pg_page">
            <div class="row">
               <div class="col-12">
                  <div class="page-title category-title">
                     <h1 class="title-head">{{ $title }}</h1>
                  </div>
               </div>
            </div>

            <div class="row shop-list-layout">
               <div class="col-lg-3 col-md-12 shop-sidebar-col">
                  <aside class="shop-sidebar" id="shop-filter-sidebar">
                     <div class="shop-sidebar-widget">
                        <h2 class="widget-title">{{ getLanguage('shopCategory') }}</h2>
                        <ul class="shop-cate-list">
                           <li class="{{ empty($cate_slug ?? '') ? 'active' : '' }}">
                              <a href="{{ route('allProduct') }}">{{ getLanguage('shopAllProducts') }}</a>
                           </li>
                           @foreach ($categoryhome as $cateItem)
                           <li class="{{ ($cate_slug ?? '') === $cateItem->slug ? 'active' : '' }}">
                              <a href="{{ route('allListProCate', ['danhmuc' => $cateItem->slug]) }}">{{ languageName($cateItem->name) }}</a>
                           </li>
                           @endforeach
                        </ul>
                     </div>

                     <div class="shop-sidebar-widget">
                        <h2 class="widget-title">{{ getLanguage('shopPriceRange') }}</h2>
                        <div class="price-filter-row">
                           <input id="js-dom-filter-price-min" type="number" min="0" class="form-control" placeholder="{{ getLanguage('shopPriceFrom') }}">
                           <input id="js-dom-filter-price-max" type="number" min="0" class="form-control" placeholder="{{ getLanguage('shopPriceTo') }}">
                        </div>
                        <button type="button" id="js-dom-filter-price-apply" class="shop-filter-btn">{{ getLanguage('shopApplyPrice') }}</button>
                     </div>

                     <div class="shop-sidebar-widget">
                        <h2 class="widget-title">{{ getLanguage('shopFilterByTag') }}</h2>
                        <div class="dom-filter-head">
                           <span>{{ getLanguage('shopShowing') }} <b id="js-dom-filter-count">{{ $list->total() }}</b></span>
                           <a href="#" id="js-dom-filter-clear">{{ getLanguage('shopClearFilter') }}</a>
                        </div>
                        @if (!empty($filter) && count($filter) > 0)
                           @foreach ($filter as $tagCate)
                              @if (!empty($tagCate->tags) && count($tagCate->tags) > 0)
                              <div class="mb-3">
                                 <p class="tag-filter-group-title">{{ $tagCate->name }}</p>
                                 <div class="tag-filter-list">
                                    @foreach ($tagCate->tags as $tag)
                                    @php
                                        $tagValue = $tag->slug . '-' . $tagCate->slug;
                                        $tagChecked = !empty($tag_slug) && !empty($tag_cate_slug)
                                            && $tag->slug === $tag_slug
                                            && $tagCate->slug === $tag_cate_slug;
                                    @endphp
                                    <div class="tag-filter-item">
                                       <label for="tag-filter-{{ $tagCate->id }}-{{ $tag->id }}">
                                          <input
                                             type="checkbox"
                                             class="js-tag-filter"
                                             id="tag-filter-{{ $tagCate->id }}-{{ $tag->id }}"
                                             data-group="{{ $tagCate->slug }}"
                                             value="{{ $tagValue }}"
                                             {{ $tagChecked ? 'checked' : '' }}
                                          >
                                          {{ $tag->name }}
                                       </label>
                                    </div>
                                    @endforeach
                                 </div>
                              </div>
                              @endif
                           @endforeach
                        @else
                           <p class="shop-filter-empty">{{ getLanguage('shopNoTags') }}</p>
                        @endif
                     </div>
                  </aside>
                  <button type="button" class="shop-sidebar-close" id="js-shop-sidebar-close" aria-label="{{ getLanguage('shopCloseFilter') }}">&times;</button>
               </div>

               <div class="col-lg-9 col-md-12 shop-list-main">
                  <div class="shop-toolbar">
                     <p class="shop-toolbar-count">{{ getLanguage('shopShowingCount') }} <b id="js-dom-filter-count-toolbar">{{ $list->total() }}</b> {{ getLanguage('shopProductUnit') }}</p>
                     <div class="shop-toolbar-actions">
                        <button type="button" class="shop-filter-toggle js-shop-filter-toggle" aria-expanded="false" aria-controls="shop-filter-sidebar">
                           <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M2 4h12v1.5H2V4zm0 3.25h12v1.5H2v-1.5zm0 3.25h12V12H2v-1.5z"/></svg>
                           <span>{{ getLanguage('shopFilter') }}</span>
                        </button>
                        <input id="js-dom-filter-keyword" type="search" class="shop-keyword-input" placeholder="{{ getLanguage('shopSearchInList') }}" aria-label="{{ getLanguage('shopSearchLabel') }}">
                        <select id="js-dom-sort-select" class="shop-sort-select" aria-label="{{ getLanguage('shopSortLabel') }}">
                           <option value="newest">{{ getLanguage('shopSortNewest') }}</option>
                           <option value="oldest">{{ getLanguage('shopSortOldest') }}</option>
                           <option value="price_asc">{{ getLanguage('shopSortPriceAsc') }}</option>
                           <option value="price_desc">{{ getLanguage('shopSortPriceDesc') }}</option>
                        </select>
                        <ul class="grid-view" aria-label="{{ getLanguage('shopGridView') }}">
                           <li data-cols="2" title="{{ getLanguage('shopGrid2') }}">
                              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="1" y="1" width="6" height="14" rx="1"/><rect x="9" y="1" width="6" height="14" rx="1"/></svg>
                           </li>
                           <li data-cols="3" title="{{ getLanguage('shopGrid3') }}">
                              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="1" y="1" width="4" height="14" rx="1"/><rect x="6" y="1" width="4" height="14" rx="1"/><rect x="11" y="1" width="4" height="14" rx="1"/></svg>
                           </li>
                           <li class="active" data-cols="4" title="{{ getLanguage('shopGrid4') }}">
                              <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><rect x="1" y="1" width="3" height="6" rx="1"/><rect x="5.5" y="1" width="3" height="6" rx="1"/><rect x="10" y="1" width="3" height="6" rx="1"/><rect x="1" y="9" width="3" height="6" rx="1"/><rect x="5.5" y="9" width="3" height="6" rx="1"/><rect x="10" y="9" width="3" height="6" rx="1"/></svg>
                           </li>
                        </ul>
                     </div>
                  </div>

                  <div class="all-products">
                     <div
                        class="row row-fix product-list-row"
                        data-cate="{{ $cate_slug ?? '' }}"
                        data-type="{{ $type_slug ?? '' }}"
                        data-typetwo="{{ $type_two_slug ?? '' }}"
                        data-per-page="{{ $list->perPage() }}"
                     >
                        @include('layouts.product.filter_grid_items', ['product' => $list])
                     </div>
                  </div>

                  <nav class="shop-pagination" aria-label="{{ getLanguage('shopPagination') }}">
                     {{ $list->links() }}
                  </nav>
               </div>
            </div>

            @if ($hasSeoContent)
            <div class="row">
               <div class="col-12">
                  <div class="shop-list-seo">{!! $seoContent !!}</div>
               </div>
            </div>
            @endif
         </div>
      </div>
   </section>

   <div class="shop-sidebar-overlay" id="js-shop-sidebar-overlay"></div>
</div>
@endsection
