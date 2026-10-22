@php
    $projectListUrl = route('duanTieuBieu');
    $projects = $duan ?? collect();
@endphp
<section class="section_project section_product_noibat">
    <div class="container">
        <div class="title_index">
            <h2 class="title-module-2">
                <a href="{{ $projectListUrl }}" title="Dự án nổi bật">
                    Dự án nổi bật
                    <span class="icon_title">
                        <svg height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg">
                            <g id="_19" data-name="19">
                                <path
                                    d="m12 19a1 1 0 0 1 -.71-1.71l5.3-5.29-5.3-5.29a1 1 0 0 1 1.41-1.41l6 6a1 1 0 0 1 0 1.41l-6 6a1 1 0 0 1 -.7.29z" />
                                <path
                                    d="m6 19a1 1 0 0 1 -.71-1.71l5.3-5.29-5.3-5.29a1 1 0 0 1 1.42-1.42l6 6a1 1 0 0 1 0 1.41l-6 6a1 1 0 0 1 -.71.3z" />
                            </g>
                        </svg>
                    </span>
                </a>
            </h2>
        </div>

        <div class="project-home-swiper swiper-container">
            <div class="swiper-wrapper">
                @forelse ($projects as $item)
                <div class="swiper-slide">
                    @include('layouts.project.item', ['item' => $item])
                </div>
                @empty
                <div class="swiper-slide">
                    <p class="project-home-empty">Chưa có dự án nổi bật.</p>
                </div>
                @endforelse
            </div>
            @if ($projects->count() > 1)
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            @endif
        </div>

        <div class="see-more">
            <a title="Xem tất cả" href="{{ $projectListUrl }}">Xem tất cả</a>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        if (!$('.project-home-swiper .swiper-slide').length) {
            return;
        }
        new Swiper('.project-home-swiper', {
            slidesPerView: 1.15,
            spaceBetween: 15,
            grabCursor: true,
            watchOverflow: true,
            roundLengths: true,
            autoplay: false,
            navigation: {
                nextEl: '.project-home-swiper .swiper-button-next',
                prevEl: '.project-home-swiper .swiper-button-prev',
            },
            breakpoints: {
                576: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                992: {
                    slidesPerView: 3,
                    spaceBetween: 18
                },
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 18
                }
            }
        });
    });
</script>
