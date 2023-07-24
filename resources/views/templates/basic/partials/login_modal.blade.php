<div class="modal custom--modal fade login-modal" id="loginModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="LoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="LoginModalLabel">@lang('Alert!')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-center">
                    @lang('Please login first')
                </p>
            </div>
            <div class="modal-footer">
                <a href="{{ route('user.login') }}" class="btn btn--dark">@lang('Login')</a>
            </div>
        </div>
    </div>
</div>
