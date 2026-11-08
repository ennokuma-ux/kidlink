@props(['name'])
<i {{ $attributes->merge(['class' => 'ico ico-' . $name]) }} aria-hidden="true"></i>
