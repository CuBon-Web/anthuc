@extends('layouts.main.master')
@php
    $pageTitle = $title ?? 'Dự án';
    $pageDescription = languageName($description ?? '') ?: 'Danh sách dự án tiêu biểu';
    $ogImage = firstBeforeAfterImage($image ?? '') ?: ($setting->logo ?? '');
    $currentCateSlug = optional($Cate ?? null)->slug;
    $projectCates = $projectCate ?? collect();
@endphp
@section('title')
    {{ $pageTitle }}
@endsection
@section('description')
    {{ $pageDescription }}
@endsection
@section('image')
    {{ url($ogImage) }}
@endsection
@section('css')
    <link href="{{ asset('frontend/css/breadcrumb_style.scss.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/css/paginate.scss.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('frontend/css/project-page.css') }}" rel="stylesheet" type="text/css" />
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
                @if ($currentCateSlug)
                <li>
                    <a href="{{ route('duanTieuBieu') }}"><span>Dự án</span></a>
                    <span class="mr_lr">
                        <svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="8" height="8"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>
                    </span>
                </li>
                @endif
                <li><strong><span>{{ $pageTitle }}</span></strong></li>
            </ul>
        </div>
    </section>

    <section class="project-section">
        <div class="container">
            <div class="project-page-title">
                <h1>{{ $pageTitle }}</h1>
                @if ($pageDescription)
                <p>{{ $pageDescription }}</p>
                @endif
            </div>

            <div class="row">
                @if ($projectCates->count())
                <div class="col-lg-3 col-md-12">
                    <aside class="project-sidebar">
                        <h2>Danh mục dự án</h2>
                        <ul class="project-cate-list">
                            <li class="{{ empty($currentCateSlug) ? 'active' : '' }}">
                                <a href="{{ route('duanTieuBieu') }}">Tất cả dự án</a>
                            </li>
                            @foreach ($projectCates as $pc)
                            <li class="{{ $currentCateSlug === $pc->slug ? 'active' : '' }}">
                                <a href="{{ route('projectCategory', ['slug' => $pc->slug]) }}">{{ $pc->name }}</a>
                            </li>
                            @endforeach
                        </ul>
                    </aside>
                </div>
                <div class="col-lg-9 col-md-12">
                @else
                <div class="col-12">
                @endif
                    @if ($list->count())
                    <div class="project-grid">
                        @foreach ($list as $item)
                            @include('layouts.project.item', ['item' => $item])
                        @endforeach
                    </div>
                    <div class="project-pagination nav_pagi">
                        {{ $list->links() }}
                    </div>
                    @else
                    <div class="project-empty">
                        <p>Chưa có dự án nào trong mục này.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
