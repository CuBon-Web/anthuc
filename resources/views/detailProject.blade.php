@extends('layouts.main.master')
@php
    $projectName = $detail->name;
    $projectDesc = languageName($detail->description ?? '');
    $projectContent = languageName($detail->content ?? '');
    $projectCateName = optional($detail->cateProject)->name;
    $projectCateSlug = optional($detail->cateProject)->slug;
    $gallery = $gallery ?? [];
    $cover = $gallery[0] ?? firstBeforeAfterImage($detail->images ?? '');
@endphp
@section('title')
    {{ $projectName }}
@endsection
@section('description')
    {{ $projectDesc ?: $projectName }}
@endsection
@section('image')
    {{ url($cover ?: ($setting->logo ?? '')) }}
@endsection
@section('css')
    <link href="{{ asset('frontend/css/breadcrumb_style.scss.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/css/project-page.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var lightbox = document.getElementById('projectLightbox');
        if (!lightbox) return;
        var imageEl = lightbox.querySelector('img');
        var images = Array.from(document.querySelectorAll('[data-project-gallery]')).map(function (btn) {
            return btn.getAttribute('data-src');
        }).filter(Boolean);
        var current = 0;

        function openAt(index) {
            if (!images.length) return;
            current = (index + images.length) % images.length;
            imageEl.src = images[current];
            lightbox.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closeBox() {
            lightbox.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-project-gallery]').forEach(function (btn, index) {
            btn.addEventListener('click', function () {
                openAt(index);
            });
        });

        lightbox.querySelector('.project-lightbox__close').addEventListener('click', closeBox);
        lightbox.querySelector('.project-lightbox__nav.prev').addEventListener('click', function () {
            openAt(current - 1);
        });
        lightbox.querySelector('.project-lightbox__nav.next').addEventListener('click', function () {
            openAt(current + 1);
        });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) closeBox();
        });
        document.addEventListener('keydown', function (event) {
            if (!lightbox.classList.contains('is-open')) return;
            if (event.key === 'Escape') closeBox();
            if (event.key === 'ArrowLeft') openAt(current - 1);
            if (event.key === 'ArrowRight') openAt(current + 1);
        });
    });
</script>
@endsection
@section('content')
<div class="bodywrap project-page">
    <section class="bread-crumb">
        <div class="container">
            <ul class="breadcrumb">
                <li class="home">
                    <a href="{{ route('home') }}"><span>Trang chủ</span></a>
                    <span class="mr_lr">
                        <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
                    </span>
                </li>
                <li>
                    <a href="{{ route('duanTieuBieu') }}"><span>Dự án</span></a>
                    <span class="mr_lr">
                        <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
                    </span>
                </li>
                @if ($projectCateSlug)
                <li>
                    <a href="{{ route('projectCategory', ['slug' => $projectCateSlug]) }}"><span>{{ $projectCateName }}</span></a>
                    <span class="mr_lr">
                        <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
                    </span>
                </li>
                @endif
                <li><strong><span>{{ $projectName }}</span></strong></li>
            </ul>
        </div>
    </section>

    <section class="project-section">
        <div class="container">
            <div class="project-detail-hero">
                <div class="project-detail-mainimg">
                    @if ($cover)
                    <button type="button" data-project-gallery data-src="{{ url($cover) }}" aria-label="Xem ảnh lớn">
                        <img src="{{ url($cover) }}" alt="{{ $projectName }}">
                    </button>
                    @endif
                </div>
                <div class="project-detail-info">
                    <h1>{{ $projectName }}</h1>
                    @if ($projectDesc)
                    <div class="project-detail-desc">{{ $projectDesc }}</div>
                    @endif
                    <ul class="project-detail-meta">
                        @if ($projectCateName)
                        <li><span>Danh mục</span><b>{{ $projectCateName }}</b></li>
                        @endif
                        @if (!empty($detail->location))
                        <li><span>Địa điểm</span><b>{{ $detail->location }}</b></li>
                        @endif
                        @if (!empty($detail->operate))
                        <li><span>Thời gian thực hiện</span><b>{{ $detail->operate }}</b></li>
                        @endif
                        @if (!empty($detail->scale))
                        <li><span>Quy mô</span><b>{{ $detail->scale }}</b></li>
                        @endif
                    </ul>
                    <a href="{{ route('lienHe') }}" class="project-detail-cta">Liên hệ tư vấn</a>
                </div>
            </div>

            @if ($projectContent)
            <div class="project-detail-content">
                <h2>Thông tin dự án</h2>
                <div class="rte">{!! $projectContent !!}</div>
            </div>
            @endif

            @if (count($gallery) > 1)
            <div class="project-gallery">
                <h2>Hình ảnh dự án</h2>
                <div class="project-gallery-grid">
                    @foreach ($gallery as $index => $imgUrl)
                    @if ($index > 0)
                    <button type="button" data-project-gallery data-src="{{ url($imgUrl) }}" aria-label="Xem ảnh phóng to">
                        <img src="{{ url($imgUrl) }}" alt="{{ $projectName }}">
                    </button>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            <div class="project-nav">
                @if ($projectPrev)
                <a href="{{ route('duanTieuBieuDetail', ['slug' => $projectPrev->slug]) }}" title="{{ $projectPrev->name }}">
                    <span class="label">Dự án trước</span>
                    <strong>{{ $projectPrev->name }}</strong>
                </a>
                @elseif ($projectCateSlug)
                <a href="{{ route('projectCategory', ['slug' => $projectCateSlug]) }}">
                    <span class="label">Quay lại</span>
                    <strong>{{ $projectCateName }}</strong>
                </a>
                @else
                <a href="{{ route('duanTieuBieu') }}">
                    <span class="label">Quay lại</span>
                    <strong>Tất cả dự án</strong>
                </a>
                @endif

                @if ($projectNext)
                <a class="next" href="{{ route('duanTieuBieuDetail', ['slug' => $projectNext->slug]) }}" title="{{ $projectNext->name }}">
                    <span class="label">Dự án tiếp</span>
                    <strong>{{ $projectNext->name }}</strong>
                </a>
                @endif
            </div>

            @if (!empty($duanlq) && $duanlq->count())
            <div class="project-related">
                <h2>Dự án khác</h2>
                <div class="project-grid">
                    @foreach ($duanlq as $item)
                        @include('layouts.project.item', ['item' => $item])
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<div class="project-lightbox" id="projectLightbox" role="dialog" aria-modal="true" aria-label="Thư viện ảnh dự án">
    <button type="button" class="project-lightbox__close" aria-label="Đóng">×</button>
    <button type="button" class="project-lightbox__nav prev" aria-label="Ảnh trước">‹</button>
    <img src="" alt="{{ $projectName }}">
    <button type="button" class="project-lightbox__nav next" aria-label="Ảnh tiếp">›</button>
</div>
@endsection
