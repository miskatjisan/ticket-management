@php
    $user = auth()->user();
@endphp
<div class="dashboard-sidebar">
    <div class="dashboard-sidebar__nav-toggle">
        <span class="dashboard-sidebar__nav-toggle-text">@lang('My Account')</span>
        <button type="button" class="btn dashboard-sidebar__nav-toggle-btn">
            <i class="las la-bars"></i>
        </button>
    </div>
    <div class="dashboard-menu">
        <div class="dashboard-menu__head">
            <span class="dashboard-menu__head-text"> @lang('My Account') </span>
            <button type="button" class="btn dashboard-menu__head-close">
                <i class="las la-times"></i>
            </button>
        </div>
        <div class="dashboard-menu__body" data-simplebar>
            <div class="profile">
                <div class="profile__bg" id="profileCoverImage" style="background-image: url('{{ getImage(getFilePath('userProfile') . '/' . $user->cover_photo, null, 'cover-photo') }}');">
                </div>
                <div class="profile__user">
                    <img src="{{ getImage(getFilePath('userProfile') . '/' . $user->image, null, 'user') }}" alt="@lang('image')" class="profile__img" id="showProfileImage">
                </div>
            </div>
            <ul class="list dashboard-menu__list">
                <li>
                    <a href="{{ route('user.home') }}" class="dashboard-menu__link {{ menuActive('user.home') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-home"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Dashboard') </span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.collection.all') }}" class="dashboard-menu__link {{ menuActive('user.collection.all') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-folder-plus"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Collections') </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('user.download.history') }}" class="dashboard-menu__link {{ menuActive('user.download.history') }}">
                        <span class="dashboard-menu__icon">
                            <i class="las la-cloud-download-alt"></i>
                        </span>
                        <span class="dashboard-menu__text"> @lang('Download History') </span>
                    </a>
                </li>

                <li>
                    <div class="accordion" id="images">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#imageCollapse" aria-expanded="false">
                                    <span class="accordion-button__icon">
                                        <i class="las la-image"></i>
                                    </span>
                                    <span class="accordion-button__text"> @lang('Manage Images') </span>
                                </button>
                            </h2>
                            <div id="imageCollapse" class="accordion-collapse collapse" data-bs-parent="#images">
                                <div class="accordion-body">
                                    <ul class="list dashboard-menu__inner">
                                        <li>
                                            <a href="{{ route('user.image.pending') }}" class="dashboard-menu__inner-link {{ menuActive('user.image.pending') }}">
                                                @lang('Pending images')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.image.rejected') }}" class="dashboard-menu__inner-link {{ menuActive('user.image.rejected') }}">
                                                @lang('Rejected images')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.image.approved') }}" class="dashboard-menu__inner-link {{ menuActive('user.image.approved') }}">
                                                @lang('Approved images')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.image.all') }}" class="dashboard-menu__inner-link {{ menuActive('user.image.all') }}">
                                                @lang('All images')
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li>
                    <div class="accordion" id="finances">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#financeCollapse" aria-expanded="false">
                                    <span class="accordion-button__icon">
                                        <i class="las la-wallet"></i>
                                    </span>
                                    <span class="accordion-button__text"> @lang('Finances') </span>
                                </button>
                            </h2>
                            <div id="financeCollapse" class="accordion-collapse collapse" data-bs-parent="#finances">
                                <div class="accordion-body">
                                    <ul class="list dashboard-menu__inner">
                                        <li>
                                            <a href="{{ route('user.deposit.index') }}" class="dashboard-menu__inner-link {{ menuActive('user.deposit.index') }}">
                                                @lang('Deposit')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.deposit.history') }}" class="dashboard-menu__inner-link {{ menuActive('user.deposit.history') }}">
                                                @lang('Deposit History')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.withdraw') }}" class="dashboard-menu__inner-link {{ menuActive('user.withdraw') }}">
                                                @lang('Withdraw')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.withdraw.history') }}" class="dashboard-menu__inner-link {{ menuActive('user.withdraw.history') }}">
                                                @lang('Withdraw History')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.transactions') }}" class="dashboard-menu__inner-link {{ menuActive('user.transactions') }}">
                                                @lang('Transactions')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.earning.log') }}" class="dashboard-menu__inner-link {{ menuActive('user.earning.log') }}">
                                                @lang('Earning Logs')
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                @if ($general->referral_system)
                    <li>
                        <div class="accordion" id="referrals">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#referral" aria-expanded="false">
                                        <span class="accordion-button__icon">
                                            <i class="las la-tree"></i>
                                        </span>
                                        <span class="accordion-button__text">
                                            @lang('Referrals')
                                        </span>
                                    </button>
                                </h2>
                                <div id="referral" class="accordion-collapse collapse" data-bs-parent="#referrals">
                                    <div class="accordion-body">
                                        <ul class="list dashboard-menu__inner">
                                            <li>
                                                <a href="{{ route('user.referral.all') }}" class="dashboard-menu__inner-link {{ menuActive('user.referral.all') }}">
                                                    @lang('Referrals')
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('user.referral.log') }}" class="dashboard-menu__inner-link {{ menuActive('user.referral.log') }}">
                                                    @lang('Referral Log')
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endif

                <li>
                    <div class="accordion" id="helpDesk">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#account" aria-expanded="false">
                                    <span class="accordion-button__icon">
                                        <i class="las la-address-card"></i>
                                    </span>
                                    <span class="accordion-button__text">
                                        @lang('Account')
                                    </span>
                                </button>
                            </h2>
                            <div id="account" class="accordion-collapse collapse" data-bs-parent="#helpDesk">
                                <div class="accordion-body">
                                    <ul class="list dashboard-menu__inner">
                                        <li>
                                            <a href="{{ route('ticket.index') }}" class="dashboard-menu__inner-link {{ menuActive(['ticket.index', 'ticket.open', 'ticket.view']) }}">
                                                @lang('Support Tickets')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('member.images', $user->username) }}" class="dashboard-menu__inner-link">
                                                @lang('Profile Settings')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.change.password') }}" class="dashboard-menu__inner-link {{ menuActive('user.change.password') }}">
                                                @lang('Change Password')
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user.twofactor') }}" class="dashboard-menu__inner-link {{ menuActive('user.twofactor') }}">
                                                @lang('2FA Security')
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
