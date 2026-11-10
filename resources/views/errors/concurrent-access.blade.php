@php
    $pageLabel = $pageLabel ?? 'This page';
    $leaseSeconds = $leaseSeconds ?? 90;
@endphp
<x-error-layout
    :title="$pageLabel.' is open on another device | KidLink'"
    tag="Already open on another device"
    :heading="$pageLabel.' is already open somewhere else'"
    :homeLabel="$homeLabel ?? 'Back to home'"
    :homeUrl="$homeUrl ?? url('/')"
    homeClass="btn-quiet"
    :footnote="isset($reference) ? 'Reference: '.$reference.'. If this keeps happening, give this code to your administrator.' : null">
    <p>
        Your KidLink account can only use <strong>{{ $pageLabel }}</strong> on one device at a time.
        It is currently open on another phone, tablet, computer or browser tab, so this one was paused
        to protect your data from conflicting changes.
    </p>

    <h2>What you can do</h2>
    <ol>
        <li>Close {{ $pageLabel }} on the other device, or sign out there.</li>
        <li>Wait up to {{ $leaseSeconds }} seconds, then press <strong>Try again</strong>.</li>
        <li>Or keep using the other device and open a different page here.</li>
    </ol>

    <x-slot name="actions">
        <a class="btn btn-primary" href="{{ $retryUrl ?? url()->current() }}">Try again</a>
        <a class="btn btn-quiet" href="{{ $homeUrl ?? url('/') }}" onclick="if (window.history.length > 1) { window.history.back(); return false; }">Go back</a>
    </x-slot>
</x-error-layout>
