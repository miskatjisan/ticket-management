@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('S.N')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Color Code')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($colors as $color)
                                    <tr>
                                        <td>
                                            {{ $colors->firstItem() + $loop->index }}
                                        </td>

                                        <td>
                                            <span class="d-flex align-items-center justify-content-center gap-2">
                                                <span class="color-indicator" style="@if ($color->color_code != 'ffffff' && $color->color_code != 'fff') border-color:#{{ $color->color_code }}; @endif background: #{{ $color->color_code }};"></span>
                                                {{ __($color->name) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $color->color_code }}
                                        </td>

                                        <td>
                                            <div class="d-flex justify-content-end flex-wrap gap-2">
                                                <button class="btn btn-outline--primary cuModalBtn btn-sm" data-modal_title="@lang('Update Color')" data-resource="{{ $color }}">
                                                    <i class="las la-pen"></i>@lang('Edit')
                                                </button>
                                                <button class="btn btn-outline--danger btn-sm confirmationBtn" data-question="@lang('Are you sure to delete this color?')" data-action="{{ route('admin.color.delete', $color->id) }}">
                                                    <i class="las la-trash-alt"></i>@lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($colors->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($colors) }}
                    </div>
                @endif
            </div>
        </div>
    </div>


    <div id="cuModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.color.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label> @lang('Color Code')</label>
                            <div class="input-group">
                                <span class="input-group-text p-0 border-0">
                                    <input type='text' class="form-control colorPicker" />
                                </span>
                                <input type="text" class="form-control colorCode" name="color_code" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .color-indicator {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 1px solid #cfd9e0;
            background: #fff;
        }
    </style>
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin_reviewer/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin_reviewer/css/spectrum.css') }}">
@endpush


@push('breadcrumb-plugins')
    <button class="btn btn-outline--primary btn-sm cuModalBtn" data-modal_title="@lang('Add Color')">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush

@push('script')
    <script>
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

        $('#cuModal').on('hidden.bs.modal', function() {
            $(this).find('.sp-preview-inner').css('background-color', 'rgb(0, 0, 0)');
        })
    </script>
@endpush
