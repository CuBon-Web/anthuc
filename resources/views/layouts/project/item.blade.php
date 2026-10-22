@php
    $projectThumb = firstBeforeAfterImage($item->images ?? '');
    $projectName = $item->name;
    $projectCateName = $item->cateProject->name ?? '';
    $projectUrl = route('duanTieuBieuDetail', ['slug' => $item->slug]);
@endphp
<article class="project-card">
    <a href="{{ $projectUrl }}" class="project-card__link" title="{{ $projectName }}">
        <div class="project-card__thumb">
            @if ($projectThumb)
            <img width="640" height="480" src="{{ url($projectThumb) }}" alt="{{ $projectName }}" loading="lazy">
            @else
            <span class="project-card__placeholder">Chưa có ảnh</span>
            @endif
            <div class="project-card__overlay">
                <span class="project-card__view">Xem dự án</span>
            </div>
        </div>
        <div class="project-card__body">
            @if ($projectCateName)
            <span class="project-card__cate">{{ $projectCateName }}</span>
            @endif
            <h3 class="project-card__title">{{ $projectName }}</h3>
        </div>
    </a>
</article>
