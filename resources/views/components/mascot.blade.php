@props([
    'mascot' => null,
    'state' => 'happy',
    'size' => 120,
    'buddy' => false,
    'speech' => null,
    'float' => false,
    'side' => false,
    'bounce' => false,
    'circle' => false,
    'eager' => false,
])

@php
    $mascot = $mascot ?: \App\Support\Mascot::forCurrentStudent();
    if (! \App\Support\Mascot::exists($mascot)) {
        $mascot = \App\Support\Mascot::KIDO;
    }

    $expression = \App\Support\Mascot::expression($mascot, $state);
    $src = asset(\App\Support\Mascot::expressions($mascot)[$expression]);

    $buddyStates = $buddy
        ? collect(\App\Support\Mascot::buddyStates($mascot))->map(fn ($path) => asset($path))->all()
        : [];

    $buddySpeech = $buddy
        ? [
            'start' => __('mascot.buddy_start'),
            'correct' => __('mascot.buddy_correct'),
            'wrong' => __('mascot.buddy_wrong'),
            'next' => __('mascot.buddy_next'),
            'hint' => __('gamekit.buddy_hint'),
        ]
        : [];

    $classes = trim(implode(' ', array_filter([
        'kidlink-mascot',
        'kidlink-mascot--'.($mascot === \App\Support\Mascot::LINKA ? 'linka' : 'kido'),
        $float ? 'kidlink-mascot--float' : '',
        ($float || $circle) ? 'kidlink-mascot--circle' : '',
        $side ? 'kidlink-mascot--side' : '',
        $bounce ? 'kidlink-mascot--bounce' : '',
        $buddy ? 'kidlink-mascot--buddy' : '',
    ])));
@endphp

<div
    {{ $attributes->merge(['class' => $classes, 'style' => '--mascot-size: '.((int) $size).'px']) }}
    @if($buddy)
        data-mascot-buddy
        data-mascot-state="{{ $expression }}"
        data-expressions="{{ json_encode($buddyStates) }}"
        data-speech="{{ json_encode($buddySpeech) }}"
    @endif
>
    <div class="kidlink-mascot__stage">
        <img
            src="{{ $src }}"
            alt="{{ __('mascot.alt_'.$mascot) }}"
            width="{{ $size }}"
            height="{{ $size }}"
            class="kidlink-mascot__img"
            loading="{{ $eager ? 'eager' : 'lazy' }}"
            @if($eager) fetchpriority="high" @endif
            decoding="async"
        >
        @if($speech || $buddy)
            <div
                class="kidlink-mascot__speech"
                @if($buddy) data-mascot-speech @endif
            >{{ $speech ?? __('mascot.buddy_start') }}</div>
        @endif
    </div>
</div>
