@php
    $lifetimeMinutes = (int) round($lifetimeMinutes);
    $lifetimeLabel = $lifetimeMinutes >= 60
        ? __('errors.lifetime_hours', ['n' => (int) round($lifetimeMinutes / 60)])
        : __('errors.lifetime_minutes', ['n' => $lifetimeMinutes]);
@endphp

<x-error-layout
    :title="__('errors.session_timeout_title')"
    :tag="__('errors.session_timeout_tag')"
    :heading="__('errors.session_expired_h1')"
    :home-class="'btn-quiet'"
    :footnote="__('errors.signin_returns')"
>
    <p>
        {{ __('errors.signout_intro', ['lifetime' => $lifetimeLabel]) }}
        {{ __('errors.token_mismatch') }}
    </p>

    <p class="muted">
        {{ __('errors.nothing_saved') }}
        @if ($attempted)
            <br>{{ __('errors.page_working_on') }}
            <code>{{ $attempted }}</code>.
        @endif
    </p>

    @slot('actions')
        <a class="btn btn-primary" href="{{ $loginUrl }}">{{ __('errors.sign_in_again') }}</a>
        <a class="btn btn-quiet" href="{{ $attempted ?: url('/') }}">{{ __('errors.reload_page') }}</a>
    @endslot
</x-error-layout>