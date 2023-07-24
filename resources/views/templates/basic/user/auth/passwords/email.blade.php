@extends($activeTemplate . 'layouts.frontend')
@section('content')
    @include($activeTemplate . 'partials.breadcrumb')
    <div class="section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-5">
                    <div class="card custom--card">
                        <div class="card-body">
                            <div class="mb-4">
                                <p>@lang('To recover your account please provide your email or username to find your account.')</p>
                            </div>
                            <form method="POST" action="{{ route('user.password.email') }}">
                                @csrf
                                <div class="col-12 mb-3">
                                    <label class="form-label">@lang('Email or Username')</label>
                                    <input type="text" class="form-control form--control" name="value" value="{{ old('value') }}" required autofocus="off">
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="base-btn w-100">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
