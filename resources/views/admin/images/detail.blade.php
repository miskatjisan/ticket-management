@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form action="{{ route('admin.images.update', $image->id) }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <img src="{{ imageUrl(getFilePath('stockImage'), $image->image_name) }}" alt="@lang('Image')">
                                </div>
                                <div class="py-2">
                                    <a href="{{ route('admin.images.file.download', $image->id) }}">@lang('Download Attachment')</a>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    @if (@$image->user)
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>@lang('Uploaded By')</label>
                                                <input type="text" class="form-control" value="{{ __($image->user->fullname) }}" disabled>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Category')</label>
                                            <select name="category" class="form-control" required>
                                                <option value="" disabled>@lang('Select One')</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($category->id == $image->category_id)>{{ __($category->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Title')</label>
                                            <input type="text" class="form-control" name="title" value="{{ $image->title }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Resolution')</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="resolution" value="{{ $image->resolution }}" required>
                                                <span class="input-group-text">@lang('px')</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($colors)
                                        <div class="col-md-6">
                                            <div class="form-group" id="extension">
                                                <label>@lang('Colors')</label>
                                                <select name="colors[]" class="form-control select2-tokenize" multiple="multiple" required>
                                                    @foreach ($colors as $color)
                                                        <option value="{{ $color->color_code }}" @selected($image->colors && in_array($color->color_code, $image->colors))>{{ __($color->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($extensions)
                                        <div class="col-md-6">
                                            <div class="form-group" id="extension">
                                                <label>@lang('Extensions')</label>
                                                <select name="extensions[]" class="form-control select2-tokenize" multiple="multiple" required>
                                                    @foreach ($extensions as $option)
                                                        <option value="{{ $option }}" @selected($image->extensions && in_array($option, $image->extensions))>{{ __(strtoupper($option)) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-12">
                                        <div class="form-group" id="tag">
                                            <label>@lang('Tags')</label>
                                            <select name="tags[]" class="form-control select2-auto-tokenize" multiple="multiple" required>
                                                @if (@$image->tags)
                                                    @foreach ($image->tags as $option)
                                                        <option value="{{ $option }}" selected>{{ __($option) }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Total Views')</label>
                                    <input type="text" class="form-control" value="{{ $image->total_view }}" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Total Likes')</label>
                                    <input type="text" class="form-control" value="{{ $image->total_like }}" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Total Downloads')</label>
                                    <input type="number" class="form-control" value="{{ $image->total_downloads }}" disabled>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Free Or Premium')</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Free')" data-off="@lang('Premium')" name="is_free" @if ($image->is_free) checked @endif>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Attribution')</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Enable')" data-off="@lang('Disable')" name="attribution" @if ($image->attribution) checked @endif>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('Active / Deactive')</label>
                                    <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Activated')" data-off="@lang('Deactivated')" name="is_active" @if ($image->is_active) checked @endif>
                                </div>
                            </div>

                            <div class="col-md-6 price @if ($image->is_free) d-none @endif">
                                <div class="form-group">
                                    <label>@lang('Price')</label>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control" name="price" value="{{ $image->price > 0 ? showAmount(@$image->price) : '' }}" @if (!$image->is_free) required @endif>
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status')</label>
                                    <select name="status" class="form-control status">
                                        <option value="" disabled selected>@lang('Select One')</option>
                                        <option value="0" @selected($image->status == 0) @disabled($image->status != 0)>@lang('Pending')</option>
                                        <option value="3" @selected($image->status == 3)>@lang('Rejected')</option>
                                        <option value="1" @selected($image->status == 1)>@lang('Approved')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea name="description" class="form-control" rows="5" required>{{ $image->description }}</textarea>
                                </div>
                            </div>

                            <div class="row reason">
                                <div class="text-center border-bottom my-3">
                                    <h5 class="py-2">@lang('Rejection Reason')</h5>
                                    @if($image->admin_id || $image->reviewer_id)
                                    <h6 class="mb-2">@lang('Previously Reviewed By') {{ $image->admin_id ? $image->admin->name : $image->reviewer->name }}</h6>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>@lang('Predefined Reason')</label>
                                        <select class="form-control predefined-reason">
                                            <option value="" disabled selected>@lang('Select One')</option>
                                            @foreach ($reasons as $reason)
                                                <option value="{{ $reason->description }}">{{ __($reason->title) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>@lang('Reason')</label>
                                        <textarea name="reason" rows="6" class="form-control" @if ($image->status == 3) required @endif>{{ $image->reason }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        #tag,
        #extension {
            position: relative;
        }

        .reason-title {
            background-color: #dddddd21;
        }
    </style>
@endpush

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.images.all') }}" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            if ($('.status option:selected').val() != 3) {
                $('.reason').hide();
            }

            $('.select2-auto-tokenize').select2({
                dropdownParent: $('#tag'),
                tags: true,
                tokenSeparators: [',']
            });

            $('.select2-tokenize').select2({
                dropdownParent: $('#extension'),
                tags: false,
                tokenSeparators: [',']
            });

            $('[name=is_free]').on('change', function() {
                if (!$(this).is(':checked')) {
                    $('.price').removeClass('d-none');
                    $('.price label').addClass('required');
                    $('[name=price]').attr('required', true);
                } else {
                    $('.price').addClass('d-none');
                    $('.price label').removeClass('required');
                    $('[name=price]').attr('required', false);
                }
            })

            $('.status').on('change', function() {
                if ($(this).val() == 3) {
                    $('[name=reason]').attr('required', true);
                    $('.reason').show('slow');
                } else {
                    $('[name=reason]').attr('required', false);
                    $('.reason').hide('slow');
                }
            });

            $('.predefined-reason').on('change', function() {
                $('[name=reason]').val($(this).val());
            });
        })(jQuery);
    </script>
@endpush
