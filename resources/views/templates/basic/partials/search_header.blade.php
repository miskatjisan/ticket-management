@php
    $categories = App\Models\Category::active()
        ->limit(5)
        ->get();
@endphp
<div class="header-top">
    <div class="container-fluid">
        <div class="header-top__content">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ getImage(getFilePath('logoIcon') . '/logo_dark.png') }}" alt="@lang('Logo')" class="img-fluid logo__is" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggle" aria-expanded="false">
                    <span class="menu-toggle"></span>
                </button>
            </nav>
            <div class="collapse navbar-collapse" id="navbarToggle">
                <div class="nav-container">
                    <!-- Primary Menu  -->
                    <ul class="list primary-menu">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="primary-menu__link"> @lang('Home') </a>
                        </li>
                        <li class="nav-item has-sub">
                            <a href="javascript:void(0)" class="primary-menu__link">@lang('Explore')</a>
                            <ul class="primary-menu__sub">
                                <li>
                                    <a href="{{ route('members') }}" class="t-link primary-menu__sub-link">
                                        <span class="d-inline-block xl-text lh-1">
                                            <i class="las la-user-friends"></i>
                                        </span>
                                        <span class="d-block flex-grow-1">
                                            @lang('Members')
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('collections') }}" class="t-link primary-menu__sub-link">
                                        <span class="d-inline-block xl-text lh-1">
                                            <i class="las la-plus-square"></i>
                                        </span>
                                        <span class="d-block flex-grow-1">
                                            @lang('Collections')
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('images', ['scope' => 'premium']) }}" class="t-link primary-menu__sub-link">
                                        <span class="d-inline-block xl-text lh-1 text--warning">
                                            <i class="las la-crown"></i>
                                        </span>
                                        <span class="d-block flex-grow-1">
                                            @lang('Premium')
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <hr class="primary-menu__divider">
                                </li>
                                <li>
                                    <a href="{{ route('images', ['scope' => 'featured']) }}" class="t-link primary-menu__sub-link">@lang('Featured')</a>
                                </li>
                                <li>
                                    <a href="{{ route('images', ['scope' => 'popular']) }}" class="t-link primary-menu__sub-link">@lang('Popular')</a>
                                </li>

                                <li>
                                    <a href="{{ route('images', ['scope' => 'most-download']) }}" class="t-link primary-menu__sub-link">@lang('Most downloads')</a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item has-sub">
                            <a href="javascript::void(0)" class="primary-menu__link">@lang('Category')</a>
                            <ul class="primary-menu__sub">
                                @foreach ($categories as $category)
                                    <li>
                                        <a href="{{ route('search', ['type' => 'image', 'category' => $category->slug]) }}" class="t-link primary-menu__sub-link">
                                            {{ __($category->name) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>

                        @if ($general->language)
                            <li class="nav-item">
                                <div class="select-lang select-lang--dark">
                                    <select class="langSel form-select">
                                        @foreach ($language as $lang)
                                            <option value="{{ $lang->code }}" @selected(session()->get('lang') == $lang->code)>@lang($lang->name)</option>
                                        @endforeach
                                    </select>
                                </div>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a href="{{ route('plans') }}" class="primary-menu__link">@lang('Pricing')</a>
                        </li>

                        @auth
                            <li class="nav-item has-sub user-dropdown user-dropdown--sm ms-lg-auto">
                                @include($activeTemplate . 'partials.user_profile_menu')
                            </li>
                        @else
                            <li class="nav-item ms-lg-auto">
                                <a href="{{ route('user.login') }}" class="primary-menu__link">@lang('Login')</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('user.register') }}" class="signup-btn my-2 ms-3 ms-lg-0">@lang('Sign Up')</a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="header-secondary">
    <div class="container-fluid">
        <!-- Search -->
        <div class="primary-search">
            <!-- Logo  -->
            <a href="{{ route('home') }}" class="logo">
                <img src="{{ getImage(getFilePath('logoIcon') . '/logo_dark.png') }}" alt="@lang('logo')" class="img-fluid logo__is" />
            </a>
            <!-- Logo End -->
            <div class="search-bar">
                <div class="search-bar__icon">
                    <i class="las la-search"></i>
                </div>
                <input type="text" class="form-control form--control search-bar__input search-input" placeholder="@lang('Search anything').." value="{{ request()->filter ?? '' }}" />
            </div>
            <button type="button" class="primary-search__filter">
                <i class="las la-sliders-h"></i>
            </button>
        </div>
    </div>
</div>
