@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="search-page">
        <div class="search-page__filter">
            <div class="filter-sidebar--backdrop"></div>
            @include($activeTemplate . 'partials.search_bar')
        </div>
        <div class="search-page__body">
            <div class="search-category">
                @php
                    $categories = App\Models\Category::active()
                        ->whereHas('images', function ($query) {
                            $query->where('status', 1);
                        })
                        ->get();
                @endphp
                @foreach ($categories as $category)
                    <div class="search-category__list">
                        <button class="search-category__btn search-param" data-param="category" data-param_value="{{ $category->slug }}" data-search_type="single">{{ __($category->name) }}</button>
                    </div>
                @endforeach
            </div>

            <!-- Tab Menu  -->
            @if (request()->filter)
                <h1 class="text-center text-muted my-4">@lang('Showing results for') <span class="fw-bold text--dark">{{ request()->filter }}</span></h1>
            @endif

            <div class="tab-menu">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="tab-menu__content">
                                <a href="javascript:void(0)" class="tab-menu__link search-images @if (request()->type == 'image') active @endif"> <i class="las la-image"></i> @lang('Images') ({{ $imageCount }}) </a>

                                <a href="javascript:void(0)" class="tab-menu__link search-collections @if (request()->type == 'collection') active @endif"> <i class="las la-folder-plus"></i> @lang('Collections') ({{ $collectionCount }}) </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tab Menu End -->

            @if (request()->type == 'image' && $images->count())
                @include($activeTemplate . 'partials.image_grid', ['images' => $images, 'class' => 'gallery'])
                @if ($images->hasPages())
                    <div class="search-page__pagination text-center py-3">
                        {{ $images->appends(request()->all())->links($activeTemplate . 'partials.paginate') }}
                    </div>
                @endif
            @elseif(request()->type == 'collection' && $collections->count())
                <div class="pb-2">
                    <div class="row g-4 justify-content-center">
                        @include($activeTemplate . 'partials.collection_grid', ['collections' => $collections])
                    </div>
                </div>

                @if ($collections->hasPages())
                    <div class="search-page__pagination text-center py-3">
                        {{ $collections->appends(request()->all())->links($activeTemplate . 'partials.paginate') }}
                    </div>
                @endif
            @else
                <div class="d-flex justify-content-center align-items-center my-4">
                    <img src="{{ getImage('assets/images/empty_message.png') }}" alt="@lang('Image')">
                </div>
            @endif
        </div>
    </div>
@endsection

@push('modal')
    @include($activeTemplate . 'partials.login_modal')
    @include($activeTemplate . 'partials.collection_modal')
    @include($activeTemplate . 'partials.share_modal')
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
    </script>
    <script src="{{ asset($activeTemplateTrue . 'js/like.js') }}"></script>
@endpush
