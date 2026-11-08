@props([
    'title' => '',
    'tag' => null,
    'heading' => null,
    'homeLabel' => null,
    'homeClass' => 'btn-primary',
    'homeUrl' => null,
    'actions' => null,
    'footnote' => null,
])

@php
    $homeLabel = $homeLabel ?: __('errors.back_home');
    $homeUrl = $homeUrl ?: url('/');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root { color-scheme: light dark; }
        body {
            margin: 0;
            padding: 2.5rem 1.5rem;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            background: #f4f5f7;
            color: #1f2430;
        }
        .card {
            max-width: 40rem;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e5ea;
            border-radius: 12px;
            padding: 2rem;
        }
        .tag {
            display: inline-block;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #8a4b00;
            background: #fff4e0;
            border: 1px solid #ffd9a0;
            border-radius: 999px;
            padding: .2rem .6rem;
            margin-bottom: .9rem;
        }
        h1 { margin: 0 0 .5rem; font-size: 1.4rem; }
        h2 { font-size: .95rem; margin: 1.6rem 0 .5rem; }
        p { margin: 0 0 1rem; }
        code, pre { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: .85rem; }
        code { background: #f0f1f4; border-radius: 4px; padding: .1rem .3rem; }
        pre {
            background: #1f2430;
            color: #e8eaed;
            border-radius: 8px;
            padding: .8rem 1rem;
            overflow-x: auto;
        }
        ol { margin: 0; padding-left: 1.2rem; }
        li { margin-bottom: .6rem; }
        .actions { display: flex; flex-wrap: wrap; gap: .6rem; margin: 1.4rem 0 .5rem; }
        .btn {
            display: inline-block;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            padding: .65rem 1.1rem;
            cursor: pointer;
        }
        .btn-primary { background: #ff8555; color: #fff; border: 1px solid #ff8555; }
        .btn-primary:hover { background: #f47548; border-color: #f47548; }
        .btn-quiet { background: #fff; color: #1f2430; border: 1px solid #d5d8de; }
        .btn-quiet:hover { background: #f4f5f7; }
        .muted { color: #6b7280; font-size: .85rem; }
    </style>
</head>
<body>
<div class="card">
    @if($tag)<span class="tag">{{ $tag }}</span>@endif
    @if($heading)<h1>{{ $heading }}</h1>@endif

    <div style="margin: 0 0 1rem;">{{-- Friendly mascot, no database access required --}}<x-mascot mascot="kido" state="question" :size="96" /></div>

    {{ $slot }}

    <div class="actions">
        {{ $actions }}
        <a class="btn {{ $homeClass }}" href="{{ $homeUrl }}">{{ $homeLabel }}</a>
    </div>

    @if($footnote)<p class="muted">{{ $footnote }}</p>@endif
</div>
</body>
</html>