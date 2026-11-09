@props(['value', 'suffix' => '%'])

@if(is_null($value))
    <span class="text-[#6b7280]" title="{{ __('ui.no_data') }}">&mdash;</span>
@else
    <span @class([
        'font-bold',
        'text-[#ff595e]' => $value < 50,
        'text-[#ffca3a]' => $value >= 50 && $value < 75,
        'text-[#8ac926]' => $value >= 75,
    ])>{{ $value }}{{ $suffix }}</span>
@endif
