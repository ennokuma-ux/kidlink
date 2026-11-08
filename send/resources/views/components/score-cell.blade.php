@props(['value', 'suffix' => '%'])

@if(is_null($value))
    <span class="text-[#8b949d]" title="{{ __('ui.no_data') }}">&mdash;</span>
@else
    <span @class([
        'font-bold',
        'text-[#f05d56]' => $value < 50,
        'text-[#d9b348]' => $value >= 50 && $value < 75,
        'text-[#68b82f]' => $value >= 75,
    ])>{{ $value }}{{ $suffix }}</span>
@endif
