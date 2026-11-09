{{--
    Academic year as two year boxes, "From" and "To". They are joined into the
    hidden field `name` as "2025-2026", which the server stores through
    App\Support\AcademicYear::store(). Typing only the first year fills in the
    next one. Years are not forced to be consecutive.

        <x-academic-year-input name="academic_year" :value="$student->academic_year ?? null" />

    A custom label saved earlier (e.g. "Summer 2026") is kept as is until the
    boxes are edited. To set the value from script use
    window.setAcademicYear(hiddenInput, '2025-2026').
--}}
@props(['name' => 'academic_year', 'value' => null, 'id' => null, 'required' => false])

@php
    $id = $id ?: $name;
    $current = (string) old($name, $value);
    $years = \App\Support\AcademicYear::parse($current);
    $min = \App\Support\AcademicYear::MIN_YEAR;
    $max = \App\Support\AcademicYear::MAX_YEAR;
    $box = 'w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none';
@endphp

<div data-academic-year-field>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $current }}" data-academic-year-value {{ $attributes->only('x-ref') }}>
    <div class="grid grid-cols-2 gap-3">
        <label class="block">
            <span class="block text-xs font-semibold text-[#5f6873] mb-1">{{ __('From year') }}</span>
            <input type="number" inputmode="numeric" min="{{ $min }}" max="{{ $max }}" step="1"
                   value="{{ $years['start'] ?? '' }}" placeholder="{{ now()->year }}"
                   data-academic-year-start aria-label="{{ __('Academic year starts') }}"
                   @required($required) class="{{ $box }}">
        </label>
        <label class="block">
            <span class="block text-xs font-semibold text-[#5f6873] mb-1">{{ __('To year') }}</span>
            <input type="number" inputmode="numeric" min="{{ $min }}" max="{{ $max }}" step="1"
                   value="{{ $years['end'] ?? '' }}" placeholder="{{ now()->year + 1 }}"
                   data-academic-year-end aria-label="{{ __('Academic year ends') }}"
                   @required($required) class="{{ $box }}">
        </label>
    </div>
    <p class="mt-1 text-xs text-[#6b7280]" data-academic-year-note @if($current === '' || $years) hidden @endif>
        {{ __('Saved as') }}: <span data-academic-year-saved>{{ $current }}</span>
    </p>
</div>

@once
<script>
(function () {
    function fields(root) {
        return {
            hidden: root.querySelector('[data-academic-year-value]'),
            start: root.querySelector('[data-academic-year-start]'),
            end: root.querySelector('[data-academic-year-end]'),
            note: root.querySelector('[data-academic-year-note]'),
        };
    }

    function compose(root, changed) {
        var f = fields(root);
        var s = f.start.value.trim(), e = f.end.value.trim();
        // Typing the first year suggests the next one until the end year is set by hand.
        if (changed === 'start' && /^\d{4}$/.test(s) && (e === '' || f.end.dataset.auto === '1')) {
            f.end.value = String(parseInt(s, 10) + 1);
            f.end.dataset.auto = '1';
            e = f.end.value;
        }
        if (changed === 'end') f.end.dataset.auto = '';
        f.hidden.value = s && e ? s + '-' + e : (s || '');
        if (f.note) f.note.hidden = true;
    }

    document.addEventListener('input', function (event) {
        var input = event.target;
        if (!input.matches) return;
        var root = input.closest('[data-academic-year-field]');
        if (!root) return;
        if (input.matches('[data-academic-year-start]')) compose(root, 'start');
        else if (input.matches('[data-academic-year-end]')) compose(root, 'end');
    });

    window.setAcademicYear = function (hidden, value) {
        if (!hidden) return;
        var root = hidden.closest('[data-academic-year-field]');
        var f = fields(root);
        var text = String(value || '').trim();
        var m = text.match(/^(\d{4})\s*[-–\/]\s*(\d{4})$/);
        hidden.value = text;
        f.start.value = m ? m[1] : '';
        f.end.value = m ? m[2] : '';
        f.end.dataset.auto = '';
        if (f.note) {
            f.note.hidden = !text || !!m;
            var saved = f.note.querySelector('[data-academic-year-saved]');
            if (saved) saved.textContent = text;
        }
    };
})();
</script>
@endonce
