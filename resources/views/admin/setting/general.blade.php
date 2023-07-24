@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body" id="generalCard">
                    <form action="" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Site Title')</label>
                                    <input class="form-control" type="text" name="site_name" required value="{{ $general->site_name }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency')</label>
                                    <input class="form-control" type="text" name="cur_text" required value="{{ $general->cur_text }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency Symbol')</label>
                                    <input class="form-control" type="text" name="cur_sym" required value="{{ $general->cur_sym }}">
                                </div>
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Timezone')</label>
                                <select class="select2-basic" name="timezone">
                                    @foreach ($timezones as $timezone)
                                        <option value="'{{ @$timezone }}'">{{ __($timezone) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Site Base Color')</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0 border-0">
                                        <input type='text' class="form-control colorPicker" value="{{ $general->base_color }}" />
                                    </span>
                                    <input type="text" class="form-control colorCode" name="base_color" value="{{ $general->base_color }}" />
                                </div>
                            </div>
                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Per day Photo Upload limit')</label>
                                <input type="number" step="any" name="upload_limit" class="form-control" value="{{ $general->upload_limit }}" required>
                                <small class="text--info"><i class="las la-exclamation-circle"></i> @lang('-1 for unlimited photo upload')</small>
                            </div>

                            <div class="form-group col-md-4 col-sm-6">
                                <label> @lang('Contributor\'s Commission')</label>
                                <div class="input-group">
                                    <input type="number" step="any" name="per_download" class="form-control" value="{{ showAmount($general->per_download) }}" required>
                                    <span class="input-group-text">
                                        %
                                    </span>
                                </div>
                                <small class="text--info"><i class="las la-exclamation-circle"></i> @lang('Contributor\'s commission in each download')</small>
                            </div>
                            @if ($general->referral_system)
                                <div class="form-group col-md-4 col-sm-6">
                                    <label> @lang('Referral Commission')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="referral_commission" value="{{ $general->referral_commission }}" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text--info"><i class="las la-exclamation-circle"></i> @lang('Referral Commission in each plan purchased')</small>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 mb-none-30 justify-content-center">
        <div class="col-lg-6 col-md-12 mb-30">
            <div class="card">
                <div class="card-header">
                    <h5>@lang('Upload Instructions')</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.instruction') }}" enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="form-group ">
                            <label> @lang('Heading') </label>
                            <input class="form-control" type="text" name="heading" value="{{ @$general->instruction->heading }}">
                        </div>
                        <div class="form-group ">
                            <label> @lang('Instruction') </label>
                            <textarea class="form-control" rows="5" name="instruction">{{ @$general->instruction->instruction }}</textarea>
                        </div>

                        <div class="form-group ">
                            <label> @lang('Instruction file') (@lang('Please insert any  .txt file')) </label>
                            <input type="file" class="form-control" type="text" name="txt" accept="text/plain">
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-12 mb-30">
            <form action="{{ route('admin.watermark') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <div class="image-upload">
                        <div class="thumb">
                            <div class="avatar-preview">
                                <div class="profilePicPreview" style="background-image: url({{ getImage('assets/images/watermark.png') }})">
                                    <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <div class="avatar-edit">
                                <input type="file" class="profilePicUpload" name="watermark" id="profilePicUpload1" accept=".png">
                                <label for="profilePicUpload1" class="bg--success">@lang('Upload Watermark') </label>
                                <small>@lang('Supported file ') <strong>@lang('.png')</strong> , @lang('image will be resized into '){{ getFileSize('watermark') }}@lang('px')</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin_reviewer/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin_reviewer/css/spectrum.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('.colorPicker').spectrum({
                color: $(this).data('color'),
                change: function(color) {
                    $(this).parent().siblings('.colorCode').val(color.toHexString().replace(/^#?/, ''));
                }
            });

            $('.colorCode').on('input', function() {
                var clr = $(this).val();
                $(this).parents('.input-group').find('.colorPicker').spectrum({
                    color: clr,
                });
            });

            $('select[name=timezone]').val("'{{ config('app.timezone') }}'").select2();
            $('.select2-basic').select2({
                dropdownParent: $('#generalCard')
            });
        })(jQuery);
    </script>
@endpush
