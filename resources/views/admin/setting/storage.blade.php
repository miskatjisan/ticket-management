@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">

                    <form action="" method="POST">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-12">
                                <div class="alert alert-danger p-3">
                                    @lang('Please Remember, Be very carefull about changing storage or changing FTP host,  Because if you change setting, make sure you copy all image and file directory of uploaded photos to your new FTP or LOCAL storage. Otherwise photos won\'t be shown to the site.   e.g: Change LOCAL To FTP,  then copy all your directory of images ("images" and "files") to your FTP directory and FTP to LOCAL ( assets/images/stock/image and assets/images/stock/file)')
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="form-label">@lang('Select Upload Storage')</label>
                                <select name="storage_type" class="form-control">
                                    <option value="1" {{ $general->storage_type == 1 ? 'selected' : '' }}>@lang('Local Storage')</option>
                                    <option value="2" {{ $general->storage_type == 2 ? 'selected' : '' }}>@lang('FTP Storage')</option>
                                </select>
                            </div>
                        </div>

                        <div class="row config">
                        </div>

                        <div class="row aws">
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Update')</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(function() {
            "use strict";
            $('select[name=storage_type]').on('change', function() {
                var val = $(this).val();
                var ftp = `<div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('FTP Hosting Root Access Path')</label>
                                    <input class="form-control  form-control-lg" type="text" name="host_domain" placeholder="@lang('https://yourdomain.com/foldername')"
                                           value="{{ @$general->ftp->host_domain }}" required>
                                    <small class="text-danger">@lang('https://yourdomain.com/foldername')</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label"> @lang('Host') </label>
                                    <input class="form-control form-control-lg" type="text" name="host" placeholder="@lang('Host')"
                                           value="{{ @$general->ftp->host }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Username') </label>
                                    <input class="form-control  form-control-lg" type="text" name="username" placeholder="@lang('Username')"
                                           value="{{ @$general->ftp->username }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Password') </label>
                                    <input class="form-control  form-control-lg" type="text" name="password" placeholder="@lang('Password')"
                                           value="{{ @$general->ftp->password }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Port') </label>
                                    <input class="form-control  form-control-lg" type="text" name="port" placeholder="@lang('Port')"
                                           value="{{ @$general->ftp->port }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">@lang('Upload Root Folder') </label>
                                    <input class="form-control  form-control-lg" type="text" name="root_path" placeholder="@lang('/html_public/something')" value="{{ @$general->ftp->root_path }}" required>
                                </div>
                            </div>`

                if (val == 1) {
                    $('.config').children().remove();
                    $('.aws').children().remove();
                } else if (val == 2) {
                    $('.config').html(ftp);
                    $('.aws').children().remove();
                }
            }).change();

        });
    </script>
@endpush
