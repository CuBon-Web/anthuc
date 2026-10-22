@php
    $img = json_decode($pro->images, true) ?? [];
    $productUrl = route('detailProduct', [
        'cate' => $pro->cate_slug,
        'type' => $pro->type_slug ? $pro->type_slug : 'loai',
        'id' => $pro->slug,
    ]);

    $productName = languageName($pro->name);
    $cateName = $pro->cate ? languageName($pro->cate->name) : '';
    $thumb = $img[0] ?? '';
    $productDesc = trim(strip_tags(languageName($pro->description ?? '')));

    $originalPrice = (float) $pro->price;
    $salePrice = (float) $pro->discount;
    $variantMinPrice = isset($pro->variant_min_price) ? (float) $pro->variant_min_price : null;
    $variantMaxPrice = isset($pro->variant_max_price) ? (float) $pro->variant_max_price : null;
    if (!is_null($variantMinPrice)) {
        $salePrice = $variantMinPrice;
    }

    $discountPercent = 0;
    if ($originalPrice > 0 && $salePrice > 0 && $salePrice < $originalPrice) {
        $discountPercent = 100 - ceil(($salePrice / $originalPrice) * 100);
    }

    $hasVariant = (int) $pro->status_variant === 1;
    $priceText = 'Liên hệ';
    $compareText = '';

    if (!is_null($variantMinPrice) && !is_null($variantMaxPrice)) {
        if ($variantMinPrice > 0 && $variantMaxPrice > 0 && $variantMinPrice != $variantMaxPrice) {
            $priceText = number_format($variantMinPrice, 0, ',', '.') . 'đ - ' . number_format($variantMaxPrice, 0, ',', '.') . 'đ';
        } elseif ($variantMinPrice > 0) {
            $priceText = number_format($variantMinPrice, 0, ',', '.') . 'đ';
        } elseif ($variantMaxPrice > 0) {
            $priceText = number_format($variantMaxPrice, 0, ',', '.') . 'đ';
        }
        if ($originalPrice > 0) {
            $compareText = number_format($originalPrice, 0, ',', '.') . 'đ';
        }
    } elseif ($salePrice > 0) {
        $priceText = number_format($salePrice, 0, ',', '.') . 'đ';
        if ($originalPrice > 0 && $salePrice < $originalPrice) {
            $compareText = number_format($originalPrice, 0, ',', '.') . 'đ';
        }
    } elseif ($originalPrice > 0) {
        $priceText = number_format($originalPrice, 0, ',', '.') . 'đ';
    }

    if ($discountPercent > 0) {
        $productTag = '-' . $discountPercent . '%';
    } elseif (!empty($pro->discountStatus)) {
        $productTag = 'BÁN CHẠY';
    } elseif (!empty($pro->home_status)) {
        $productTag = 'MỚI';
    } else {
        $productTag = $cateName;
    }
@endphp
<article class="product-card">
  <div class="product-media">
    <a href="{{ $productUrl }}" title="{{ $productName }}">
      <img src="{{ $thumb ? url($thumb) : asset('frontend/images/lazy.png') }}" alt="{{ $productName }}" loading="lazy">
    </a>
    @if ($productTag)
    <span class="product-tag">{{ $productTag }}</span>
    @endif
    <button class="product-wish" type="button" aria-label="Yêu thích {{ $productName }}"><i class="bi bi-heart"></i></button>
  </div>
  <div class="product-body">
    @if ($cateName)
    <div class="product-type">{{ $cateName }}</div>
    @endif
    <h3 class="product-name"><a href="{{ $productUrl }}" title="{{ $productName }}">{{ $productName }}</a></h3>
    <p class="product-desc">{{ $productDesc }}</p>
    <div class="product-bottom">
      <span class="product-price">
        {{ $priceText }}
        @if ($compareText)
        <small class="compare-price">{{ $compareText }}</small>
        @endif
      </span>
      @if ($hasVariant)
      <a class="product-link" href="{{ $productUrl }}">Chọn mua <i class="bi bi-arrow-right"></i></a>
      @else
      <form action="{{ route('add.to.cart') }}" method="post" class="variants product-action" data-cart-form data-id="product-actions-{{ $pro->id }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $pro->id }}">
        <input type="hidden" name="quantity" value="1">
        <input type="hidden" name="price" value="{{ $salePrice > 0 ? $salePrice : $originalPrice }}">
        <button class="product-link add_to_cart" title="Thêm vào giỏ" type="button">Chọn mua <i class="bi bi-arrow-right"></i></button>
      </form>
      @endif
    </div>
  </div>
</article>
