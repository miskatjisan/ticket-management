@php
    $content = getContent('plan.content', true);
@endphp
@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="section price-section">
        <div class="section__head">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-xl-7 col-xxl-6">
                        <h2 class="mt-0 text-center">{{ __(@$content->data_values->title) }}</h2>
                        <p class="t-short-para mb-0 mx-auto text-center">
                            {{ __(@$content->data_values->subtitle) }}
                            @if ($plans->count())
                                <div class="form-check form-switch flex-row d-flex align-items-center justify-content-center">
                                    <label class="form-check-label" for="period">@lang('Monthly')</label>
                                    <input class="form-check-input mx-2" name="plan_period" value="monthly" type="checkbox" id="period">
                                    <label class="form-check-label" for="period">@lang('Yearly')</label>
                                </div>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row g-4 g-lg-3 g-xl-4 justify-content-center">
                @forelse ($plans as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="price-card price-card--base plan-card" data-monthly_price="{{ showAmount($plan->monthly_price) }}" data-yearly_price="{{ showAmount($plan->yearly_price) }}">
                            <div class="price-card__head">
                                <ul class="list">
                                    <li>
                                        <div class="list list--row align-items-end" style="--gap: 0.2rem">
                                            <h1 class="price-card__price m-0 plan-price">{{ __(showAmount($plan->monthly_price)) }}</h1>
                                            <span class="price-card__time plan-period"> /@lang('Month') </span>
                                        </div>
                                    </li>
                                </ul>
                                <div class="price-card__head-content">
                                    <h4 class="mt-0 mb-1 price-card__title">{{ __($plan->name) }}</h4>
                                    <span class="price-card__subtitle"> {{ __($plan->title) }} </span>
                                </div>
                            </div>
                            <div class="price-card__body">
                                <ul class="list list--check">
                                    <li class="sm-text">{{ $plan->dailyLimitText }} @lang('daily downloads')</li>
                                    <li class="sm-text">{{ $plan->monthlyLimitText }} @lang('monthly downloads')</li>
                                </ul>
                            </div>
                            <div class="price-card__footer">
                                <button type="button" class="base-btn rounded-pill w-100 purchase-btn" data-id="{{ $plan->id }}" data-plan_name="{{ __($plan->name) }}" data-monthly_limit="{{ $plan->monthlyLimitText }}" data-daily_limit="{{ $plan->dailyLimitText }}">
                                    @lang('Purchase Now')
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="d-flex justify-content-center align-items-center">
                        <img src="{{ getImage('assets/images/empty_message.png') }}" alt="@lang('Image')">
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection


@push('modal')
    <!--  Purchase Modal  -->
    <div class="modal custom--modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">@lang('Purchase Plan')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @auth
                    <form action="{{ route('user.plan.purchase') }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="period">
                            <input type="hidden" name="plan">
                            <div class="row gy-3">
                                <p class="text-center">@lang('By purchasing') <span class="fw-bold plan_name"></span> @lang(' plan, you will get ') <span class="daily_limit fw-bold"></span>@lang(' images download opurtunity per day and') <span class="monthly_limit fw-bold"></span> @lang(' images per month.')</p>

                                <div class="form-group">
                                    <label class="form-label required" for="payment_type">@lang('Payment Type')</label>
                                    <div class="form--select">
                                        <select name="payment_type" id="payment_type" class="form-select" required>
                                            <option value="">@lang('Select One')</option>
                                            <option value="direct">@lang('Direct Payment')</option>
                                            <option value="wallet">@lang('From Wallet')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                        </div>
                    </form>
                @else
                    <div class="modal-body">
                        <p>@lang('Please login first')</p>
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('#period').on('change', function() {
                let planCards = $('.plan-card');
                let period = $(this).val();
                let monthlyPrice = 0;
                let yearlyPrice = 0;
                let afterText = null;

                if (period == 'monthly') {
                    $(this).val('yearly');
                    period = 'yearly';
                    afterText = '/Year';
                } else {
                    $(this).val('monthly');
                    period = 'monthly';
                    afterText = '/Month';
                }

                $.each(planCards, function(index, element) {
                    let price = $(element).data(period + '_price');
                    $(element).find('.plan-price').text(price);
                    $(element).find('.plan-period').text(afterText);
                });
            });

            $('.purchase-btn').on('click', function() {
                let plan = $(this).data();
                let period = $('[name=plan_period]').val();
                let modal = $('#purchaseModal');

                modal.find('[name=plan]').val(plan.id);
                modal.find('[name=period]').val(period);

                modal.find('.plan_name').text(plan.plan_name);
                modal.find('.daily_limit').text(plan.daily_limit);
                modal.find('.monthly_limit').text(plan.monthly_limit);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
