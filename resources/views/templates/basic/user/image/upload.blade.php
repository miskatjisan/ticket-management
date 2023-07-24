@php
    $extensions = getFileExt('file_extensions');
    $url = route('user.image.store');
    if (@$image) {
        $url = route('user.image.update', $image->id);
    }
@endphp
@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="card custom--card">
        <div class="card-body">
            <h2 class="text-center">{{ __(@$general->instruction->heading) }}</h2>
            <p>{{ __(@$general->instruction->instruction) }}</p>
            <p class="text-center">@lang('Please download this file and include it in your zip')</p>
            <div class="text-center mt-3">
                <a href="{{ route('txt.download') }}" class="base-btn">@lang('Download Now')</a>
            </div>
        </div>
    </div>
    <div class="row upload-wrapper no-gutters justify-content-center">
        <div class="col-12 mt-4">
            <div class="card custom--card form-card">
                <div class="card-header">
                    <h5 class="card-title">{{ __($pageTitle) }}</h5>
                </div>
                <form action="{{ $url }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row g-0">
                            <div class="col-md-6 mb-3">
                                <div class="photo-upload-area">
                                    <div class="file-upload">
                                        <div class="image-upload-wrap @if (@$image) d-none @endif">
                                            <input class="file-upload-input validate" name="photo" type='file' onchange="readURL(this);" accept=".png,.jpeg,.jpg" @if (!@$image) required @endif />
                                            <div class="drag-text">
                                                <p class="title">@lang('Drag and drop a file or select add Image')</p>
                                            </div>
                                        </div>
                                        <div class="file-upload-content @if (@$image) d-block @endif">
                                            <img class="file-upload-image" src="{{ @$image ? imageUrl(getFilePath('stockImage'), $image->image_name) : '' }}" alt="@lang('your image')" />
                                            <button type="button" class="remove-image">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <button class="base-btn w-100 mt-3 upload-btn" type="button">@lang('Add Image')</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-0">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">@lang('Title')</label>
                                        <input type="text" name="title" class="form-control form--control" required value="{{ old('title', @$image->title) }}">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label required">@lang('Category')</label>
                                        <div class="form--select">
                                            <select class="form-select" name="category" required>
                                                <option value="">@lang('Select One')</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected($category->id == old('category', @$image->category_id))>{{ __($category->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">@lang('Premium / Free')</label>
                                        <div class="form--select">
                                            <select class="form-select" name="is_free" required>
                                                <option value="1" @selected(old('is_free', @$image->is_free) == 1)>@lang('Free')</option>
                                                <option value="0" @selected(old('is_free', @$image->is_free) == 0)>@lang('Premium')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3 price-div">
                                        <label class="form-label">@lang('Price')
                                            (@lang('You will get ')<span class="commission-text fw-bold"> {{ showAmount($general->per_download) . '%' }} </span> @lang(' in each download'))
                                        </label>
                                        <div class="input-group input--group">
                                            <input type="number" step="any" min="0" name="price" class="form-control form--control image-price" value="{{ old('price', showAmount(@$image->price)) }}">
                                            <span class="input-group-text">
                                                {{ __($general->cur_text) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">
                                            @lang('Resolution')
                                            <i class="las la-exclamation-circle text--base" data-bs-toggle="tooltip" title="@lang('Seperate width and height by x, e.g. 600x1200')" data-bs-custom-class="custom--tooltip"></i>
                                        </label>
                                        <input type="text" name="resolution" class="form-control form--control" placeholder="@lang('e.g. 600x1200')" value="{{ old('resolution', @$image->resolution) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">@lang('Colors')</label>
                                <select class="form-select form--control select2-tokenize" name="colors[]" multiple="multiple" required>
                                    @foreach ($colors as $color)
                                        <option value="{{ $color->color_code }}" @selected(@$image && in_array($color->color_code, $image->colors))>
                                            {{ __($color->name) }}
                                        </option>
                                    @endforeach

                                    @if (old('colors'))
                                        @foreach (old('colors') as $oldColor)
                                            <option value="{{ $oldColor }}" selected>{{ __(ucfirst($oldColor)) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">@lang('Extensions')</label>
                                <select class="form-select form--control select2-tokenize" name="extensions[]" multiple="multiple" required>
                                    @foreach ($extensions as $ext)
                                        <option value="{{ $ext }}" @selected(@$image && in_array($ext, $image->extensions))>{{ __(strtoupper($ext)) }}</option>
                                    @endforeach

                                    @if (old('extensions'))
                                        @foreach (old('extensions') as $oldExtension)
                                            <option value="{{ $oldExtension }}" selected>{{ __(ucfirst($oldExtension)) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">@lang('Tags (maximum 10 tags)')</label>
                                <select class="form-select form--control select2-auto-tokenize" name="tags[]" multiple="multiple" required>
                                    @if (@$image)
                                        @foreach ($image->tags as $tag)
                                            <option value="{{ $tag }}" selected>{{ __(ucfirst($tag)) }}</option>
                                        @endforeach
                                    @endif

                                    @if (old('tags'))
                                        @foreach (old('tags') as $oldTag)
                                            <option value="{{ $oldTag }}" selected>{{ __(ucfirst($oldTag)) }}</option>
                                        @endforeach
                                    @endif

                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">@lang('Description')</label>
                                <textarea rows="6" name="description" class="form-control form--control" id="description" required>{{ old('description', @$image->description) }}</textarea>

                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">@lang('Zip File')</label>
                                <input type="file" name="file" class="form-control form--control" accept=".zip,.7z,.rar,.tar,.wim" @if (!@$image) required @endif>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="base-btn w-100">@lang('Submit')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .photo-upload-area {
            background-color: #ffffff;
            padding: 0 30px;
            height: 100%;
        }

        .photo-upload-area .image-upload {
            width: 100%;
        }

        .file-upload {
            background-color: #ffffff;
            margin: 0 auto;
        }

        .file-upload .file-upload-btn {
            width: 100%;
            margin: 0;
            color: #fff;
            background: #0062FF;
            border: none;
            padding: 10px;
            border-radius: 4px;
            border-bottom: 4px solid #004ecc;
            transition: all .2s ease;
            outline: none;
            text-transform: uppercase;
            font-weight: 700;
        }

        .file-upload .file-upload-btn:hover {
            background: #0058e6;
            color: #ffffff;
            transition: all .2s ease;
            cursor: pointer;
        }

        .file-upload .file-upload-btn.active {
            border: 0;
            transition: all .2s ease;
        }

        .file-upload .file-upload-input {
            position: absolute;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            outline: none;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload .image-upload-wrap {
            position: relative;
            border: 2px dashed #dddddd;
            border-radius: 5px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            -ms-border-radius: 5px;
            -o-border-radius: 5px;
        }

        .file-upload .image-dropping:hover,
        .file-upload .image-upload-wrap:hover {
            background-color: #e4e4e4;
        }

        .file-upload .image-title-wrap {
            padding: 0 15px 15px 15px;
            olor: #222;
        }

        .file-upload .drag-text {
            text-align: center;
            padding: 150px 50px;
        }

        .file-upload .drag-text .title {
            font-size: 22px;
            color: #6d6d6d;
            text-shadow: 0 5px 5px rgba(0, 0, 0, 0.15);
        }

        .file-upload .file-upload-image {
            width: 100%;
            border-radius: 5px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            -ms-border-radius: 5px;
            -o-border-radius: 5px;
        }
    </style>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';

        $('.upload-btn').on('click', function() {
            $('.file-upload-input').trigger('click');
        })

        $('.select2-auto-tokenize').select2({
            dropdownParent: $('.form-card'),
            tags: true,
            tokenSeparators: [',']
        });

        $('.select2-tokenize').select2({
            dropdownParent: $('.form-card'),
            tags: false,
            tokenSeparators: [',']
        });

        // image upload js
        function readURL(input) {
            if (!['image/jpeg', 'image/png', 'image/jpg'].includes(input.files[0].type)) {
                notify('error', 'File type doesn\'t match');
                return false;
            }

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('.image-upload-wrap').hide();

                    $('.file-upload-image').attr('src', e.target.result);
                    $('.file-upload-content').show();

                    $('.image-title').html(input.files[0].name);
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                removeUpload();
            }
        }

        function removeUpload() {
            $('.file-upload-input').replaceWith($('.file-upload-input').clone());
            $('.file-upload-content').hide();
            $('.image-upload-wrap').show();
        }

        $('.image-upload-wrap').bind('dragover', function() {
            $('.image-upload-wrap').addClass('image-dropping');
        });

        $('.image-upload-wrap').bind('dragleave', function() {
            $('.image-upload-wrap').removeClass('image-dropping');
        });

        $('.remove-image').on('click', function() {
            removeUpload();
        });

        $('form').submit(function(e) {
            var fields = $(this).serializeArray();
            $.each(fields, function(i, field) {
                if (field.value != '') {
                    $('.upload-btn').prop('disabled', true);
                    $('.upload-btn').text('Uploading...');
                }
            })
        });

        $('[name=resolution]').keypress(function(e) {
            let value = $(this).val();
            if (e.keyCode === 13) {
                if (value.indexOf('x') == -1) {
                    e.preventDefault();
                    $(this).val(value + " x ");
                }
            }
        });

        $('[name=is_free]').on('change', function() {
            let priceDiv = $('.price-div');
            if ($(this).val() == 0) {
                priceDiv.removeClass('d-none');
            } else {
                priceDiv.addClass('d-none');
            }
        }).change();

        $('.image-price').on('focusout', function() {
            let amount = parseFloat($(this).val());
            if (amount > 0) {
                let percentage = @json($general->per_download);
                let commission = amount * parseFloat(percentage) / 100;
                $('.commission-text').text(`${commission} {{ __($general->cur_text) }}`);
            } else {
                $('.commission-text').text(`{{ showAmount($general->per_download) . '%' }}`);
            }
        });
    </script>
@endpush
