@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="photo-page">
        <div class="container">
            <div class="row g-4 gy-md-0">
                <div class="col-md-7 col-lg-8 col-xl-9">
                    <div class="photo-view">
                        <img src="{{ imageUrl(getFilePath('stockImage'), $image->image_name) }}" alt="@lang('Image')" class="photo-view__img" />
                    </div>
                    <div class="photo-info">
                        @php echo $image->description @endphp
                    </div>
                    <div class="related-category">
                        <h5 class="related-category__title">@lang('Keywords')</h5>
                        <ul class="list list--row flex-wrap related-category__list">
                            @foreach ($image->tags as $tag)
                                <li>
                                    <a href="{{ route('search', ['type' => 'image', 'tag' => $tag]) }}" class="search-category__btn">{{ __($tag) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-5 col-lg-4 col-xl-3">
                    <div class="user align-items-center">
                        <div class="user__img user__img--lg">
                            <img src="{{ getImage(getFilePath('userProfile') . '/' . @$image->user->image, null, 'user') }}" alt="@lang('image')" class="user__img-is" />
                        </div>
                        <div class="user__content">
                            <span class="user__name"><a href="{{ route('member.images', @$image->user->username) }}">{{ __(@$image->user->fullname) }}</a>/{{ shortNumber($image->user->images->count()) }} @lang('images')</span>
                            @if ($image->user_id != auth()->id())
                                <ul class="list list--row flex-wrap" style="--gap: 0.5rem">
                                    @php
                                        $liked = null;
                                        $followed = null;
                                        $user = auth()->user();
                                        if ($user) {
                                            $liked = $user->likes->where('image_id', $image->id)->first();
                                            $followed = $user->followings->where('following_id', $image->user->id)->first();
                                        }
                                    @endphp

                                    @if ($liked)
                                        <li>
                                            <button type="button" class="follow-btn unlike-btn active" data-has_icon="0" data-image="{{ $image->id }}">@lang('Unlike')</button>
                                        </li>
                                    @else
                                        <li>
                                            <button type="button" class="follow-btn like-btn" data-has_icon="0" data-image="{{ $image->id }}">@lang('Like')</button>
                                        </li>
                                    @endif

                                    @if ($followed)
                                        <li>
                                            <button type="button" class="follow-btn unfollow active" data-following_id="{{ $image->user->id }}">@lang('Unfollow')</button>
                                        </li>
                                    @else
                                        <li>
                                            <button type="button" class="follow-btn follow" data-following_id="{{ $image->user->id }}">@lang('Follow')</button>
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </div>
                    </div>

                    <div class="photo-details my-4">
                        <div class="photo-details__head">
                            <div class="photo-details__title">
                                <span class="photo-details__icon">
                                    <i class="las la-camera-retro"></i>
                                </span>
                                <span class="photo-details__title-link">{{ __($image->title) }} </span>
                            </div>
                        </div>
                        <div class="photo-details__body">
                            <ul class="list" style="--gap: 0.5rem">
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Image type') </span>
                                        <span class="d-inline-block sm-text lh-1">
                                            @if ($image->extensions)
                                                {{ __(strtoupper(implode(', ', $image->extensions))) }}
                                            @endif
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Resolution') </span>
                                        <span class="d-inline-block sm-text lh-1"> {{ $image->resolution }} </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Extensions') </span>
                                        <span class="d-inline-block sm-text lh-1"> {{ __(strtoupper(implode(', ', $image->extensions))) }} </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Published') </span>
                                        <span class="d-inline-block sm-text lh-1">
                                            {{ showDateTime($image->created_at, 'F d, Y') }}
                                        </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Views') </span>
                                        <span class="d-inline-block sm-text lh-1"> {{ $image->total_view }} </span>
                                    </div>
                                </li>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="d-inline-block sm-text lh-1"> @lang('Downloads') </span>
                                        <span class="d-inline-block sm-text lh-1"> {{ $image->total_downloads }} </span>
                                    </div>
                                </li>
                                @if (!$image->is_free)
                                    <li>
                                        <div class="d-flex align-items-center justify-content-between gap-3">
                                            <span class="d-inline-block sm-text lh-1"> @lang('Price') </span>
                                            <span class="d-inline-block sm-text lh-1"> {{ showAmount($image->price) }} {{ __($general->cur_text) }} </span>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <form action="{{ route('image.download', encrypt($image->id)) }}" method="GET" class="download-form">
                        @if (!$image->is_free && $image->user_id != @auth()->id())
                            @php
                                $user = auth()->user();
                            @endphp
                            @if (!$user)
                                <button type="button" class="common-btn w-100 login-btn">
                                    <span class="common-btn__icon">
                                        <i class="las la-download"></i>
                                    </span>
                                    @lang('Download')
                                </button>
                            @else
                                @if (!$user->purchasedPlan)
                                    <button type="button" class="common-btn w-100 downloadBtn" data-description="@lang('This image is premium. You don\'t have any active plan, so if you want to download this resource, download charge will be taken from your wallet balance')">
                                        <span class="common-btn__icon">
                                            <i class="las la-download"></i>
                                        </span>
                                        @lang('Download')
                                    </button>
                                @elseif($user->purchasedPlan->daily_limit <= $todayDownload && !$alreadyDownloaded)
                                    <button type="button" class="common-btn w-100 downloadBtn" data-description="@lang('This image is premium. Your active plan\'s daily limit has been over, so if you want to download this resource, download charge will be taken from your wallet balance')">
                                        <span class="common-btn__icon">
                                            <i class="las la-download"></i>
                                        </span>
                                        @lang('Download')
                                    </button>
                                @elseif($user->purchasedPlan->monthly_limit <= $monthlyDownload && !$alreadyDownloaded)
                                    <button type="button" class="common-btn w-100 downloadBtn" data-description="@lang('This image is premium. Your active plan\'s monthly limit has been over, so if you want to download this resource, download charge will be taken from your wallet balance')">
                                        <span class="common-btn__icon">
                                            <i class="las la-download"></i>
                                        </span>
                                        @lang('Download')
                                    </button>
                                @else
                                    <input type="hidden" name="from_account" value="0">
                                    <button type="submit" class="common-btn w-100">
                                        <span class="common-btn__icon">
                                            <i class="las la-download"></i>
                                        </span>
                                        @lang('Download')
                                    </button>
                                @endif
                            @endif
                        @else
                            <input type="hidden" name="from_account" value="0">
                            <button type="submit" class="common-btn w-100">
                                <span class="common-btn__icon">
                                    <i class="las la-download"></i>
                                </span>
                                @lang('Download')
                            </button>
                        @endif
                    </form>
                    <div class="mt-4">
                        <h5 class="mt-0 mb-2">@lang('Share')</h5>
                        <ul class="list list--row social-list">
                            <li>
                                <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" class="t-link social-list__icon">
                                    <i class="lab la-facebook-f"></i>
                                </a>
                            </li>
                            <li>
                                <a target="_blank" href="https://twitter.com/intent/tweet?text={{ $image->title }}&amp;url={{ urlencode(url()->current()) }}" class="t-link social-list__icon">
                                    <i class="lab la-twitter"></i>
                                </a>
                            </li>
                            <li>
                                <a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ $image->title }}&amp;summary={{ $image->title }}" class="t-link social-list__icon">
                                    <i class="lab la-linkedin-in"></i>
                                </a>
                            </li>
                            <li>
                                <a target="_blank" href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ $image->description }}" class="t-link social-list__icon">
                                    <i class="lab la-pinterest-p"></i>
                                </a>

                            </li>
                        </ul>
                    </div>
                </div>
                @if ($relatedImages->count())
                    <div class="col-12">
                        <div class="related-photo">
                            <h5 class="related-photo__title">@lang('Related Photos')</h5>
                            @include($activeTemplate . 'partials.image_grid', ['images' => $relatedImages, 'class' => 'gallery--sm'])
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="photo-modal">
            <div class="photo-modal__img">
                <img src="{{ imageUrl(getFilePath('stockImage'), $image->thumb) }}" alt="image" class="photo-modal__image">
            </div>
            <div class="photo-modal__content">
                <h6 class="photo-modal__title">@lang('Give Thanks!')</h6>
                <p class="photo-modal__description">
                    @lang('Give thanks to ')@<span class="fw-bold">{{ @$image->user->username }}</span> @lang('for sharing this photo for free, the easiest way, sharing on social network')
                </p>
                <ul class="list list--row social-list">
                    <li>
                        <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" class="t-link social-list__icon">
                            <i class="lab la-facebook-f"></i>
                        </a>
                    </li>
                    <li>
                        <a target="_blank" href="https://twitter.com/intent/tweet?text={{ $image->title }}&amp;url={{ urlencode(url()->current()) }}" class="t-link social-list__icon">
                            <i class="lab la-twitter"></i>
                        </a>
                    </li>
                    <li>
                        <a target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ $image->title }}&amp;summary={{ $image->title }}" class="t-link social-list__icon">
                            <i class="lab la-linkedin-in"></i>
                        </a>
                    </li>
                    <li>
                        <a target="_blank" href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ $image->description }}" class="t-link social-list__icon">
                            <i class="lab la-pinterest-p"></i>
                        </a>

                    </li>
                </ul>
                <button type="button" class="photo-modal__close">
                    <i class="las la-times"></i>
                </button>
            </div>
        </div>
    </div>


@endsection
@push('modal')
    <!-- Download modal -->
    <div class="modal custom--modal fade" id="downloadModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="downloadModalLabel">@lang('Download Alert!')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('image.download', encrypt($image->id)) }}" method="GET" class="download-form">
                    <input type="hidden" name="from_account" value="1">
                    <div class="modal-body">
                        <div class="alert-description fw-bold text--danger">

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="common-btn w-100">@lang('Download')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include($activeTemplate . 'partials.collection_modal')
    @include($activeTemplate . 'partials.share_modal')
    @include($activeTemplate . 'partials.login_modal')
@endpush
@push('script')
    <script>
        "use strict";

        let likeRoutes = {
            updateLike: "{{ route('user.image.like.update') }}"

        };
        let likeParams = {
            loggedStatus: @json(Auth::check()),
            csrfToken: "{{ csrf_token() }}"
        }

        let followRoutes = {
            updateFollow: "{{ route('user.follow.update') }}",
        }

        let followParams = {
            loggedStatus: @json(Auth::check()),
            csrfToken: "{{ csrf_token() }}",
            appendStatus: 0
        }

        $('.login-btn').on('click', function() {
            let modal = $('#loginModal');
            modal.modal('show');
        });

        $('.downloadBtn').on('click', function() {
            let modal = $('#downloadModal');
            let description = $(this).data('description');
            modal.find('.alert-description').text(description);
            modal.modal('show');
        });

        $('.photo-modal__close').on('click', function() {
            $('.photo-modal').removeClass('active');
        });

        $('.download-form').on('submit', function() {
            setTimeout(() => {
                let session = "{{ session()->get('is_download') }}";
                if (session == 'downloaded') {
                    $('.photo-modal').addClass('active');
                }
            }, 2000);
        })
    </script>
    <script src="{{ asset($activeTemplateTrue . 'js/like.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/follow.js') }}"></script>
@endpush
