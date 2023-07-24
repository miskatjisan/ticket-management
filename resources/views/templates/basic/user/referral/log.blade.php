@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="custom--table-container table-responsive--md">
        <table class="table custom--table">
            <thead>
                <tr>
                    <th>@lang('User')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Time')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $k => $data)
                    <tr>
                        <td>
                            {{ __($data->referee->fullname) }} <br>
                            <a href="{{ route('member.images', $data->referee->username) }}">{{ $data->referee->username }}</a>
                        </td>
                        <td>
                            {{ getAmount($data->amount) }} {{ __($general->cur_text) }}
                        </td>

                        <td>
                            {{ showDateTime($data->created_at) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center sm-text">{{ __($emptyMessage) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($logs->hasPages())
            <div class="mt-3 d-flex justify-content-end">
                {{ paginateLinks($logs) }}
            </div>
        @endif
    </div>
@endsection
