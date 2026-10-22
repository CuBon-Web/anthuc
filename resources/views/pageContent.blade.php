@extends('layouts.main.master')
@section('title')
@php $pageTitle = languageName($pagecontentdetail->title); @endphp
{{ $pageTitle }}
@endsection
@section('description')
{{ $pageTitle }}
@endsection
@section('image')
{{url(''.$banner[0]->image)}}
@endsection
@section('css')
<link href="{{ asset('frontend/css/page-content.css') }}?v=3" rel="stylesheet" type="text/css" media="all" />
@endsection
@section('js')
@endsection
@section('content')
    <div class="bodywrap content-page">
        <section class="bread-crumb">
            <div class="container">
                <ul class="breadcrumb">
                    <li class="home">
                        <a href="{{ route('home') }}"><span>{{ getLanguage('home') }}</span></a>
                        <span class="mr_lr">&nbsp;<svg aria-hidden="true" focusable="false" data-prefix="fas"
                                data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 320 512" class="svg-inline--fa fa-chevron-right fa-w-10">
                                <path fill="currentColor"
                                    d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"
                                    class=""></path>
                            </svg>&nbsp;</span>
                    </li>
                    <li><strong><span>{{ $pageTitle }}</span></strong></li>
                </ul>
            </div>
        </section>
        <section class="content-sheet">
            <div class="container">
                <article class="content-card">
                    <h1>{{ $pageTitle }}</h1>
                    <div class="content-prose rte">
                        {!! languageName($pagecontentdetail->content) !!}
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection
