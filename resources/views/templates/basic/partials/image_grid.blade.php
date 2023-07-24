@php
    $defaultImageContent = getContent('default_images.content', true);
    $defaultImage = getImage('assets/images/frontend/default_images/' . @$defaultImageContent->data_values->loading_image);
@endphp
<ul class="list list--row flex-wrap flex-images {{ @$class ?? 'gallery' }}" id="flexBox">
    @foreach ($images as $image)
        @php
            $imageUrl = imageUrl(getFilePath('stockImage'), $image->thumb);
        @endphp
        <li class="item" data-w="{{ $image->image_width }}" data-h="{{ $image->image_height }}">
            <a href="{{ route('image.detail', [slug($image->title), $image->id]) }}" class="gallery__link">
                <img src="{{ $defaultImage }}" data-image_src="{{ $imageUrl }}" alt="@lang('Image')" class="gallery__img lazy-loading-img" />
                @if (!$image->is_free)
                    <span class="gallery__premium">
                        <i class="fas fa-crown"></i>
                    </span>
                @endif
                <figcaption class="gallery__content">
                    <span class="gallery__title">
                        {{ __($image->title) }}
                    </span>
                    <span class="gallery__footer">
                        <span class="gallery__author">
                            <span class="gallery__user">
                                <img src="{{ $defaultImage }}" data-image_src="{{ getImage(getFilePath('userProfile') . '/' . $image->user->image, null, 'user') }}" alt="@lang('Contributor')" class="gallery__user-img lazy-loading-img" />
                            </span>
                            <span class="gallery__user-name">{{ $image->user->fullname }}</span>
                        </span>
                        <span class="gallery__like">
                            <span class="gallery__like-icon">
                                <i class="fas fa-heart"></i>
                            </span>
                            <span class="gallery__like-num">{{ shortNumber($image->total_like) }}</span>
                        </span>
                    </span>
                </figcaption>
            </a>

            @php
                $user = auth()->user();
                $like = $image->likes->where('user_id', @$user->id)->count();
                $collectionImage = $user ? $user->collectionImages->where('image_id', $image->id)->first() : null;
            @endphp

            <div class="gallery__share">
                <div class="list gallery__list">
                    <div>
                        <button class="gallery__btn @if ($like) unlike-btn @else like-btn @endif" data-has_icon="1" data-bs-toggle="tooltip" data-bs-placement="left" title="@if ($like) @lang('Unlike') @else @lang('like') @endif" data-bs-custom-class="custom--tooltip" data-image="{{ $image->id }}">
                            @if ($like)
                                <i class="las la-heart active"></i>
                            @else
                                <i class="lar la-heart"></i>
                            @endif
                        </button>
                    </div>
                    <div>
                        <button class="gallery__btn collect-btn" data-bs-toggle="tooltip" data-bs-placement="left" title="@if ($collectionImage) @lang('Collected') @else
                @lang('Collect') @endif" data-bs-custom-class="custom--tooltip" data-image_id="{{ $image->id }}">
                            <i class="las la-folder-plus"></i>
                        </button>
                    </div>
                    <div>
                        <button class="gallery__btn share-btn" data-bs-toggle="tooltip" data-bs-placement="left" title="Share" data-bs-custom-class="custom--tooltip" data-route="{{ route('image.detail', [slug($image->title), $image->id]) }}" data-url_len_code="{{ urlencode(route('image.detail', [slug($image->title), $image->id])) }}" data-image_title="{{ $image->title }}">
                            <i class="las la-share"></i>
                        </button>
                    </div>
                </div>
            </div>
        </li>
    @endforeach
</ul>
@push('script-lib')
    <script src="{{ asset($activeTemplateTrue . 'js/jquery.flex-images.min.js') }}"></script>
@endpush
@push('script')
    <script>
        "use strict";

        $('#flexBox').flexImages({
            rowHeight: 240
        });
    </script>
@endpush
