{{--
    Shared <head> for every layout.

    One place for the metadata Lighthouse checks (title, description, canonical,
    social cards, icons, robots) and for the asset order that keeps the page
    fast: one compiled stylesheet instead of the 400 KB Tailwind runtime, and
    no render-blocking scripts.

    Pass ['noindex' => true] for signed-in areas so dashboards stay out of
    search results.
--}}
@php
    $noindex = $noindex ?? false;

    $pageTitle = trim(View::yieldContent('title'));
    $metaTitle = $pageTitle !== ''
        ? $pageTitle.' - KidLink'
        : 'KidLink - '.__('misc.daycare_management');

    $metaDescription = trim(View::yieldContent('meta_description')) ?: __(
        'KidLink: early childhood learning, attendance and progress for teachers, parents and children.'
    );

    $canonicalUrl = url()->current();
    $ogImage = asset('assets/images/og-kidlink.png');

    $versioned = static function (string $path): string {
        $full = public_path($path);

        return asset($path).(is_file($full) ? '?v='.filemtime($full) : '');
    };
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#ff8555">
<meta name="color-scheme" content="light">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $metaTitle }}</title>
<meta name="description" content="{{ $metaDescription }}">

@if($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KidLink">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="KidLink, early childhood learning with Kido and Linka">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="icon" href="{{ asset('favicon-32.png') }}" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">

{{-- The Latin Inter file is the only font the first screen needs. --}}
<link rel="preload" as="font" type="font/woff2"
      href="{{ asset('fonts/UcC73FwrK3iLTeHuS_nVMrMxCp50SjIa1ZL7.woff2') }}" crossorigin>

<link rel="stylesheet" href="{{ $versioned('css/fonts.css') }}">
<link rel="stylesheet" href="{{ $versioned('css/tailwind.css') }}">
<link rel="stylesheet" href="{{ $versioned('css/kidlink-theme.css') }}">
<link rel="stylesheet" href="{{ $versioned('css/mascot.css') }}">
<link rel="stylesheet" href="{{ $versioned('css/icons.css') }}">

<script defer src="{{ $versioned('js/alpine.min.js') }}"></script>
<script defer src="{{ $versioned('js/mascot.js') }}"></script>

@stack('head')
