
$(function () {
  // Header shadow and back-to-top visibility
  const $header = $('#siteHeader');
  const $backToTop = $('#backToTop');
  const updateScrollUI = function () {
	const y = window.scrollY || $(window).scrollTop();
	$header.toggleClass('scrolled', y > 120);
	$backToTop.toggleClass('show', y > 500);
  };
  updateScrollUI();
  $(window).on('scroll', updateScrollUI);
  $backToTop.on('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

  // Mobile menu
  const setMobileNav = function (open) {
	$('#mainNav').toggleClass('open', open);
	$('#navOverlay').toggleClass('show', open);
	$('body').toggleClass('nav-open', open);
	$('#mobileMenuBtn').attr('aria-expanded', open ? 'true' : 'false').attr('aria-label', open ? 'Đóng menu' : 'Mở menu');
  };
  $('#mobileMenuBtn').on('click', function () { setMobileNav(!$('#mainNav').hasClass('open')); });
  $('#mobileNavClose, #navOverlay').on('click', function () { setMobileNav(false); });
  $('#mainNav a').on('click', function () { setMobileNav(false); });
  $('.nav-drop-btn').on('click', function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (window.innerWidth >= 992) return;
	const open = !$(this).closest('.nav-drop').hasClass('open');
	$(this).closest('.nav-drop').toggleClass('open', open);
	$(this).attr('aria-expanded', open ? 'true' : 'false');
  });
  $('.nav-sub-btn').on('click', function (e) {
	e.preventDefault();
	e.stopPropagation();
	if (window.innerWidth >= 992) return;
	const $item = $(this).closest('.nav-drop-item');
	const open = !$item.hasClass('open');
	$item.siblings('.nav-drop-item').removeClass('open').find('.nav-sub-btn').attr('aria-expanded', 'false');
	$item.toggleClass('open', open);
	$(this).attr('aria-expanded', open ? 'true' : 'false');
  });
  $(document).on('keydown', function (e) { if (e.key === 'Escape') setMobileNav(false); });
  $(window).on('resize', function () {
	if (window.innerWidth >= 992) {
	  setMobileNav(false);
	  $('.nav-drop, .nav-drop-item').removeClass('open');
	  $('.nav-drop-btn, .nav-sub-btn').attr('aria-expanded', 'false');
	}
  });

  // Search
  var $searchPanel = $('#searchPanel');
  var $searchForm = $('#searchForm');
  var $searchInput = $('#searchInput');
  var $searchSuggest = $('#searchSuggest');
  var searchTimer = null;

  function closeSearch() {
	$searchPanel.addClass('d-none');
	$('#searchToggle').attr('aria-expanded', 'false');
	$searchSuggest.empty().attr('hidden', true);
  }

  $('#searchToggle').on('click', function () {
	var open = $searchPanel.hasClass('d-none');
	$searchPanel.toggleClass('d-none', !open);
	$(this).attr('aria-expanded', open ? 'true' : 'false');
	if (open) {
	  $searchInput.trigger('focus');
	  $searchInput.trigger('input');
	} else {
	  $searchSuggest.empty().attr('hidden', true);
	}
  });

  $searchInput.on('input', function () {
	var term = $.trim($searchInput.val());
	clearTimeout(searchTimer);
	if (term.length < 2) {
	  $searchSuggest.empty().attr('hidden', true);
	  return;
	}
	searchTimer = setTimeout(function () {
	  $.ajax({
		url: $searchForm.data('suggest-url'),
		method: 'POST',
		dataType: 'json',
		data: {
		  keyword: term,
		  _token: $('meta[name="csrf-token"]').attr('content')
		}
	  }).done(function (res) {
		if ($.trim($searchInput.val()) !== term) return;
		var html = res && res.html ? res.html : '';
		if (res && res.count > 0) {
		  html += '<a class="search-suggest-all" href="' + $searchForm.attr('action') + '?keyword=' + encodeURIComponent(term) + '">Xem tất cả ' + res.count + ' kết quả</a>';
		}
		if (!html) {
		  $searchSuggest.empty().attr('hidden', true);
		  return;
		}
		$searchSuggest.html(html).removeAttr('hidden');
	  });
	}, 250);
  });

  $searchForm.on('submit', function (e) {
	if (!$.trim($searchInput.val())) {
	  e.preventDefault();
	  showToast('Vui lòng nhập tên sản phẩm cần tìm.');
	  $searchInput.trigger('focus');
	}
  });

  $(document).on('click', function (e) {
	if (!$(e.target).closest('#searchPanel, #searchToggle').length) {
	  $searchSuggest.attr('hidden', true);
	}
	if (!$(e.target).closest('.language-select').length) {
	  $('.language-select details').removeAttr('open');
	}
  });
  $(document).on('keydown', function (e) {
	if (e.key === 'Escape') closeSearch();
  });

  // Wishlist interaction
  $('.product-wish').on('click', function () {
	const $icon = $(this).find('i');
	const liked = $icon.hasClass('bi-heart-fill');
	$icon.toggleClass('bi-heart-fill', !liked).toggleClass('bi-heart', liked);
	$(this).css('color', liked ? 'var(--forest)' : 'var(--wine)');
	showToast(liked ? 'Đã bỏ khỏi danh sách yêu thích.' : 'Đã thêm vào danh sách yêu thích.');
  });

  // Demo cart (front-end only)
  const cart = {};
  const prices = {
	'Dầu gội dược liệu': 320000,
	'Sữa tắm dược liệu': 280000,
	'Dung dịch vệ sinh dược liệu': 260000,
	'Sữa rửa mặt dược liệu': 250000
  };
  const formatVND = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';
  function renderCart() {
	const names = Object.keys(cart);
	const count = names.reduce((sum, name) => sum + cart[name], 0);
	const total = names.reduce((sum, name) => sum + cart[name] * prices[name], 0);
	$('#cartCount').text(count);
	$('#cartTotal').text(formatVND(total));
	if (!names.length) {
	  $('#cartItems').html(cartLabel('empty', 'Giỏ hàng đang trống. Hãy chọn sản phẩm bạn yêu thích.'));
	  return;
	}
	const $list = $('<div class="vstack gap-3"></div>');
	names.forEach(name => {
	  const $row = $('<div class="d-flex justify-content-between align-items-center gap-3"></div>');
	  const $info = $('<div></div>').append($('<strong class="d-block text-forest"></strong>').text(name))
		.append($('<small class="text-secondary"></small>').text(formatVND(prices[name]) + ' × ' + cart[name]));
	  const $controls = $('<div class="d-flex align-items-center gap-2"></div>');
	  const $minus = $('<button class="btn btn-sm btn-outline-secondary" type="button"></button>').text('−').attr('aria-label', cartLabel('qty-minus', 'Giảm số lượng'));
	  const $plus = $('<button class="btn btn-sm btn-outline-secondary" type="button"></button>').text('+').attr('aria-label', cartLabel('qty-plus', 'Tăng số lượng'));
	  $minus.on('click', () => { cart[name]--; if (cart[name] <= 0) delete cart[name]; renderCart(); });
	  $plus.on('click', () => { cart[name]++; renderCart(); });
	  $controls.append($minus, $('<span></span>').text(cart[name]), $plus);
	  $row.append($info, $controls); $list.append($row);
	});
	$('#cartItems').empty().append($list);
  }
  $('.add-to-cart').on('click', function () {
	const name = $(this).data('product');
	cart[name] = (cart[name] || 0) + 1;
	renderCart();
	showToast('Đã thêm ' + name + ' vào giỏ hàng.');
  });
  function cartLabel(name, fallback) {
	var value = $('#cartCanvas').attr('data-label-' + name);
	return value ? value : fallback;
  }

  function escapeHtml(value) {
	return $('<div>').text(value == null ? '' : String(value)).html();
  }

  window.renderCartDrawer = function (payload, openDrawer) {
	var items = (payload && payload.items) || [];
	var total = 0;
	var count = 0;
	if (payload && typeof payload.cart_total !== 'undefined') total = payload.cart_total;
	else if (payload && typeof payload.total !== 'undefined') total = payload.total;
	if (payload && typeof payload.cart_count !== 'undefined') count = payload.cart_count;
	$('#cartCount, .count_item_pr').text(count);
	$('#cartTotal').text(formatVND(total));

	if (!items.length) {
	  $('#cartItems').html('<span class="text-secondary">' + escapeHtml(cartLabel('empty', 'Giỏ hàng đang trống. Hãy chọn sản phẩm bạn yêu thích.')) + '</span>');
	} else {
	  var html = items.map(function (item) {
		var image = item.image || '';
		var qty = parseInt(item.quantity, 10) || 1;
		var variant = item.variant
		  ? '<small class="d-block text-secondary">' + escapeHtml(item.variant) + '</small>'
		  : '';
		return '<div class="d-flex gap-3 align-items-start mb-3">' +
		  '<img src="' + escapeHtml(image) + '" alt="" width="64" height="64" class="rounded flex-shrink-0" style="width:64px;height:64px;object-fit:cover;background:#f6efe0">' +
		  '<div class="flex-grow-1">' +
			'<strong class="d-block text-forest">' + escapeHtml(item.name) + '</strong>' +
			variant +
			'<small class="text-secondary">' + formatVND(item.price) + '</small>' +
			'<div class="d-flex align-items-center gap-2 mt-1">' +
			  '<button type="button" class="btn btn-sm btn-outline-secondary cart-drawer-qty px-2 py-0" data-cart-key="' + escapeHtml(item.key) + '" data-qty="' + Math.max(1, qty - 1) + '" aria-label="' + escapeHtml(cartLabel('qty-minus', 'Giảm số lượng')) + '">−</button>' +
			  '<span>' + qty + '</span>' +
			  '<button type="button" class="btn btn-sm btn-outline-secondary cart-drawer-qty px-2 py-0" data-cart-key="' + escapeHtml(item.key) + '" data-qty="' + (qty + 1) + '" aria-label="' + escapeHtml(cartLabel('qty-plus', 'Tăng số lượng')) + '">+</button>' +
			  '<button type="button" class="btn btn-link btn-sm text-secondary p-0 ms-1 cart-drawer-remove" data-cart-key="' + escapeHtml(item.key) + '">' + escapeHtml(cartLabel('remove', 'Xóa')) + '</button>' +
			'</div>' +
		  '</div>' +
		  '<span class="fw-semibold text-nowrap">' + formatVND(item.line_total) + '</span>' +
		'</div>';
	  }).join('');
	  $('#cartItems').html(html);
	}

	if (openDrawer) {
	  var canvas = document.getElementById('cartCanvas');
	  if (canvas && window.bootstrap) {
		window.bootstrap.Offcanvas.getOrCreateInstance(canvas).show();
	  }
	}
  };

  function cartDrawerRequest(url, data) {
	data._token = $('meta[name="csrf-token"]').attr('content') || '';
	return $.ajax({
	  url: url,
	  method: 'POST',
	  dataType: 'json',
	  data: data
	});
  }

  $(document).on('click', '#cartCanvas .cart-drawer-qty', function (e) {
	e.preventDefault();
	var $button = $(this);
	var key = $button.attr('data-cart-key');
	var qty = parseInt($button.attr('data-qty'), 10) || 1;
	var url = $('#cartCanvas').data('update-url') || '/update-cart';
	if (!key || $('#cartItems').hasClass('is-loading')) return;
	$('#cartItems').addClass('is-loading');
	cartDrawerRequest(url, { id: key, quantity: qty }).done(function (res) {
	  window.renderCartDrawer(res, false);
	}).fail(function () {
	  showToast('Không thể cập nhật số lượng.');
	}).always(function () {
	  $('#cartItems').removeClass('is-loading');
	});
  });

  $(document).on('click', '#cartCanvas .cart-drawer-remove', function (e) {
	e.preventDefault();
	var key = $(this).attr('data-cart-key');
	var url = $('#cartCanvas').data('remove-url') || '/remove-from-cart';
	if (!key || $('#cartItems').hasClass('is-loading')) return;
	$('#cartItems').addClass('is-loading');
	cartDrawerRequest(url, { id: key }).done(function (res) {
	  window.renderCartDrawer(res, false);
	}).fail(function () {
	  showToast('Không thể xóa sản phẩm.');
	}).always(function () {
	  $('#cartItems').removeClass('is-loading');
	});
  });

  $(document).on('click', '.product-action .add_to_cart', function (e) {
	e.preventDefault();
	var $button = $(this);
	var $cardForm = $button.closest('form');
	if (!$cardForm.length || $button.hasClass('is-loading')) return;
	$button.addClass('is-loading');
	$.ajax({
	  url: $cardForm.attr('action') || '/add-to-cart',
	  method: 'POST',
	  dataType: 'json',
	  data: $cardForm.serialize()
	}).done(function (res) {
	  window.renderCartDrawer(res, true);
	}).fail(function () {
	  showToast('Không thể thêm sản phẩm vào giỏ hàng.');
	}).always(function () {
	  $button.removeClass('is-loading');
	});
  });

  $('#checkoutBtn').on('click', function () {
	var count = parseInt($('#cartCount').text(), 10) || 0;
	var url = $('#cartCanvas').data('checkout-url');
	if (!count) { showToast(cartLabel('empty-toast', 'Giỏ hàng của bạn đang trống.')); return; }
	if (url) { window.location.href = url; return; }
	showToast(cartLabel('empty-toast', 'Giỏ hàng của bạn đang trống.'));
  });

  // Newsletter demo
  $('#newsletterForm').on('submit', function (e) {
	e.preventDefault();
	showToast('Cảm ơn bạn đã đăng ký nhận tin từ An Thực.');
	this.reset();
  });

  // Active menu based on clicked anchor
  $('#mainNav').on('click', '> a, > .nav-drop > .nav-drop-head > a', function () {
	$('#mainNav > a, .nav-drop > .nav-drop-head > a').removeClass('active');
	$(this).addClass('active');
  });

  function showToast(message) {
	$('#toastMessage').text(message);
	bootstrap.Toast.getOrCreateInstance(document.getElementById('siteToast'), { delay: 2800 }).show();
  }

  // Featured products horizontal navigation
  const productsGrid = document.getElementById('productsGrid');
  if (productsGrid) {
	const $productsPrev = $('#productsPrev');
	const $productsNext = $('#productsNext');
	const updateProductNav = function () {
	  const maxScroll = productsGrid.scrollWidth - productsGrid.clientWidth;
	  $productsPrev.prop('disabled', productsGrid.scrollLeft <= 2);
	  $productsNext.prop('disabled', productsGrid.scrollLeft >= maxScroll - 2);
	};
	const scrollProducts = function (dir) {
	  if (!productsGrid.firstElementChild) return;
	  const step = productsGrid.firstElementChild.getBoundingClientRect().width;
	  productsGrid.scrollBy({ left: dir * step, behavior: 'smooth' });
	};
	$productsPrev.on('click', function () { scrollProducts(-1); });
	$productsNext.on('click', function () { scrollProducts(1); });
	$(productsGrid).on('scroll', updateProductNav);
	$(window).on('resize', updateProductNav);
	updateProductNav();
  }

  new Swiper('.heroSwiper', {
	effect: 'fade',
	fadeEffect: { crossFade: true },
	speed: 900,
	loop: true,
	autoplay: { delay: 6000, disableOnInteraction: false },
	pagination: { el: '.hero-pagination', clickable: true },
	navigation: { nextEl: '.hero-next', prevEl: '.hero-prev' }
  });

  new Swiper('.reviewsSwiper', {
	slidesPerView: 1,
	spaceBetween: 16,
	loop: true,
	grabCursor: true,
	autoplay: { delay: 5500, disableOnInteraction: false },
	pagination: { el: '.reviewsSwiper .swiper-pagination', clickable: true },
	navigation: { nextEl: '.reviews-next', prevEl: '.reviews-prev' },
	breakpoints: {
	  768: { slidesPerView: 2, spaceBetween: 18 },
	  992: { slidesPerView: 3, spaceBetween: 22 }
	}
  });

  // AOS scroll animation
  if (window.AOS) AOS.init({ duration: 650, once: true, offset: 70, easing: 'ease-out-cubic' });
  $('#year').text(new Date().getFullYear());
});