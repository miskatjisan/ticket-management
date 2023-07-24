@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="custom--table-container table-responsive--md">
        <table class="table custom--table">
            <thead>
                <tr>
                    <th class="sm-text">@lang('Category')</th>
                    <th class="sm-text">@lang('Image')</th>
                    <th class="sm-text">@lang('Date')</th>
                    <th class="sm-text">@lang('Contributor')</th>
                    <th class="sm-text">@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($downloads as $key=>$download)
                    <tr>
                        <td class="sm-text">
                            {{ __($download->image->category->name) }}
                        </td>
                        <td class="sm-text">
                            {{ __($download->image->title) }}
                        </td>
                        <td class="sm-text">
                            {{ showDateTime($download->craeted_at, 'd M, Y') }}
                        </td>

                        <td class="sm-text">
                            {{ __($download->contributor->fullname) }} <br>
                            <a href="{{ route('member.images', $download->contributor->username) }}">
                                <span>@</span>{{ $download->contributor->username }}
                            </a>
                        </td>

                        <td>
                            <a href="{{ route('user.image.download.file', $download->image_id) }}" class="btn btn--base btn-sm">
                                <i class="las la-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center sm-text">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($downloads->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ paginateLinks($downloads) }}
            </div>
        @endif
    </div>
@endsection
