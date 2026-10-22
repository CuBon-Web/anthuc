@extends('layouts.main.master')
@section('title')
@php
    $blogLocale = session('locale', app()->getLocale());
    $blogPageTitle = ($blogLocale === 'vi' && !empty($blog_detail->seo_title)) ? $blog_detail->seo_title : languageName($blog_detail->title);
@endphp
    {{ $blogPageTitle }}
@endsection
@section('description')
@php
    $blogPageDescription = ($blogLocale === 'vi' && !empty($blog_detail->meta_description)) ? $blog_detail->meta_description : languageName($blog_detail->description);
@endphp
    {{ $blogPageDescription }}
@endsection
@section('image')
    {{ url('' . $blog_detail->image) }}
@endsection
@section('schema')
    @php
        $cleanText = function ($value) {
            $text = (string) $value;
            // Remove zero-width chars that usually appear from copy/paste.
            return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        };
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $postTitle = $cleanText(languageName($blog_detail->title));
        $postDescription = $cleanText(
            ($blogLocale === 'vi' && !empty($blog_detail->meta_description))
                ? $blog_detail->meta_description
                : strip_tags(languageName($blog_detail->description))
        );
        $postContentText = trim($cleanText(strip_tags(languageName($blog_detail->content))));
        preg_match_all('/[\p{L}\p{N}]+/u', $postContentText, $wordMatches);
        $postWordCount = count($wordMatches[0]);
        $postUrl = url()->current();
        $homeUrl = route('home');
        $categoryUrl = route('listCateBlog', ['slug' => $blog_detail->category]);
        $siteName = $setting->webname ?? ($setting->company ?? 'Website');
        $publisherName = $setting->company ?? $siteName;
        $publisherLogo = !empty($setting->logo) ? url($setting->logo) : url('' . $blog_detail->image);
    @endphp
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": {!! json_encode(url('/') . '#website', $jsonFlags) !!},
      "url": {!! json_encode(url('/'), $jsonFlags) !!},
      "name": {!! json_encode($siteName, $jsonFlags) !!}
    },
    {
      "@type": "Organization",
      "@id": {!! json_encode(url('/') . '#organization', $jsonFlags) !!},
      "name": {!! json_encode($publisherName, $jsonFlags) !!},
      "url": {!! json_encode(url('/'), $jsonFlags) !!},
      "logo": {
        "@type": "ImageObject",
        "url": {!! json_encode($publisherLogo, $jsonFlags) !!}
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": {!! json_encode($postUrl . '#breadcrumb', $jsonFlags) !!},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Trang chủ",
          "item": {!! json_encode($homeUrl, $jsonFlags) !!}
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": {!! json_encode($cleanText($blog_detail->cate ? languageName($blog_detail->cate->name) : ''), $jsonFlags) !!},
          "item": {!! json_encode($categoryUrl, $jsonFlags) !!}
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": {!! json_encode($postTitle, $jsonFlags) !!},
          "item": {!! json_encode($postUrl, $jsonFlags) !!}
        }
      ]
    },
    {
      "@type": "BlogPosting",
      "@id": {!! json_encode($postUrl . '#article', $jsonFlags) !!},
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": {!! json_encode($postUrl, $jsonFlags) !!}
      },
      "headline": {!! json_encode($postTitle, $jsonFlags) !!},
      "description": {!! json_encode($postDescription, $jsonFlags) !!},
      "articleSection": {!! json_encode($cleanText($blog_detail->cate ? languageName($blog_detail->cate->name) : ''), $jsonFlags) !!},
      "keywords": {!! json_encode(implode(', ', $blogTags ?? []), $jsonFlags) !!},
      "inLanguage": "vi-VN",
      "wordCount": {{ $postWordCount }},
      "datePublished": {!! json_encode(optional($blog_detail->created_at)->toIso8601String(), $jsonFlags) !!},
      "dateModified": {!! json_encode(optional($blog_detail->updated_at)->toIso8601String(), $jsonFlags) !!},
      "image": [
        {
          "@type": "ImageObject",
          "url": {!! json_encode(url(''.$blog_detail->image), $jsonFlags) !!}
        }
      ],
      "author": {
        "@type": "Person",
        "name": {!! json_encode($cleanText($blog_detail->author ?: 'Admin'), $jsonFlags) !!}
      },
      "publisher": {
        "@type": "Organization",
        "@id": {!! json_encode(url('/') . '#organization', $jsonFlags) !!}
      }
    }
  ]
}
</script>
@endsection
@section('css')
<link href="{{ asset('frontend/css/blog-detail.css') }}" rel="stylesheet" type="text/css" media="all" />
@endsection
@section('js')

@endsection
@section('content')
<div class="bodywrap blog-detail-page">

  <section class="bread-crumb">
    <div class="container">
      <ul class="breadcrumb">					
        <li class="home">
          <a href="/"><span>{{getLanguage('home')}}</span></a>						
          <span class="mr_lr">&nbsp;<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chevron-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" class="svg-inline--fa fa-chevron-right fa-w-10"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" class=""></path></svg>&nbsp;</span>
        </li>
        
        
        @if ($blog_detail->cate)
        <li>
          <a href="{{ route('listCateBlog', ['slug' => $blog_detail->category]) }}"><span>{{ languageName($blog_detail->cate->name) }}</span></a>
          <span class="mr_lr">&nbsp;<svg aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path fill="currentColor" d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"></path></svg>&nbsp;</span>
        </li>
        @endif
        <li><strong><span>{{languageName($blog_detail->title)}}</span></strong></li>
        
        
      </ul>
    </div>
  </section>
<div class="blog_wrapper layout-blog" itemscope itemtype="https://schema.org/Blog">
  <meta itemprop="name" content="{{languageName($blog_detail->title)}}">
  <meta itemprop="description" content="{{languageName($blog_detail->description)}}">
  <div class="container">
    <h1 class="title-page d-none">
        <span>{{languageName($blog_detail->title)}}</span>
    </h1>
    <div class="row article-main">
        <div class="right-content col-lg-9 col-12">
          <div class="article-details clearfix">
            <h1 class="article-title">{{languageName($blog_detail->title)}}</h1>
            @php $articleLead = trim(strip_tags(languageName($blog_detail->description))); @endphp
            @if ($articleLead !== '')
            <p class="article-lead">{{ $articleLead }}</p>
            @endif
            <div class="posts">
              <div class="time-post">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                {{ date_format($blog_detail->created_at, 'd/m/Y') }}
              </div>
              <div class="time-post">
                <i class="bi bi-person" aria-hidden="true"></i>
                <span>{{ languageName($blog_detail->author ?: 'Admin') }}</span>
              </div>
            </div>

            <div class="article-content rte">
                {!! languageName($blog_detail->content) !!}
            </div>
            <div class="share-group">
              <strong class="share-group__heading">{{getLanguage('share')}}</strong>
              <div class="share-group__list">
                <a class="share-group__item share-group__item--facebook" title="Chia sẻ lên Facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer.php?u={{ url()->current() }}">
                  <i class="bi bi-facebook" aria-hidden="true"></i>
                  <span class="visually-hidden">Facebook</span>
                </a>
                <a class="share-group__item share-group__item--pinterest" title="Chia sẻ lên Pinterest" target="_blank" rel="noopener" href="https://pinterest.com/pin/create/button/?url={{ url()->current() }}">
                  <i class="bi bi-pinterest" aria-hidden="true"></i>
                  <span class="visually-hidden">Pinterest</span>
                </a>
                <a class="share-group__item share-group__item--twitter" title="Chia sẻ lên X" target="_blank" rel="noopener" href="https://twitter.com/share?url={{ url()->current() }}">
                  <i class="bi bi-twitter-x" aria-hidden="true"></i>
                  <span class="visually-hidden">X</span>
                </a>
              </div>
            </div>													</div>
        </div>
        <div class="blog_left_base col-lg-3 col-12">
          <div class="aside-blog-right">
              <div class="blog_noibat">
                <h2 class="h2_sidebar_blog">
                    <a href="{{ route('allListBlog') }}" title="{{getLanguage('newPost')}}">{{getLanguage('newPost')}}</a>
                </h2>
                <div class="blog_content">
                    @foreach ($blognew as $item)
                    <div class="item clearfix">

                      <div class="post-thumb">
                          <a class="image-blog scale_hover" href="{{route('detailBlog',['slug'=>$item->slug])}}" title="{{languageName($item->title)}}">
                          <img width="600" height="380" class="img_blog" src="{{ !empty($item->image) ? url($item->image) : asset('frontend/images/lazy.png') }}" alt="{{ languageName($item->title) }}" loading="lazy">
                          </a>
                      </div>
                      <div class="contentright">
                          <h3><a title="{{languageName($item->title)}}" href="{{route('detailBlog',['slug'=>$item->slug])}}">{{languageName($item->title)}}</a></h3>
                      </div>
                    </div>
                    @endforeach
                </div>
              </div>
              <div class="clearfix clear-fix"></div>
          </div>
        </div>
    </div>
  </div>
</div>
</div>
@endsection
