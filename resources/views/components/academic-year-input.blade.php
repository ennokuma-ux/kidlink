{{--
    Academic year field that accepts the year however it is typed and tidies it
    to "2025-2026" when the field loses focus (the server does the same with
    App\Support\AcademicYear::normalize(), so nothing depends on this script).

        <x-academic-year-input name="academic_year" :value="$student->academic_year ?? null" />

    Typing 2025, 25-26, 2025/26 or "SY 2025-2026" all become 2025-2026. Anything
    else ("Summer 2026") is kept exactly as typed. Suggestions are a convenience.
--}}
@props(['name' => 'academic_year', 'value' => null, 'id' => null, 'required' => false])

@php
    $id = $id ?: $name;
    $current = old($name, $value);
    $listId = $id.'-options';
@endphp

<input type="text"
       name="{{ $name }}"
       id="{{ $id }}"
       value="{{ $current }}"
       list="{{ $listId }}"
       autocomplete="off"
       maxlength="{{ \App\Support\AcademicYear::MAX_LENGTH }}"
       placeholder="{{ __('e.g. 2025-2026 or Summer 2026') }}"
       @required($required)
       data-academic-year
       {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none']) }}>
<datalist id="{{ $listId }}">
    @foreach(\App\Support\AcademicYear::options() as $option)
        <option value="{{ $option }}"></option>
    @endforeach
</datalist>

@once
<script>
(function () {
    // Mirrors App\Support\AcademicYear::resolve(). Returns "2025-2026" for a standard year, else null (a custom label is left alone).
    function tidy(raw) {
        var text = String(raw || '').trim();
        if (!text) return null;
        var bare = text.replace(/^(?:a\.?\s*y\.?|s\.?\s*y\.?|academic\s+year|school\s+year)\s*:?\s*/i, '');
        var compact = bare.match(/^(\d{8}|\d{6})$/);
        var pair = bare.match(/^(\d{1,4})(?:\s*(?:-|\u2013|\u2014|\/|to)\s*(\d{1,4}))?$/i);
        var groups = compact ? [compact[1].slice(0, 4), compact[1].slice(4)] : (pair ? [pair[1], pair[2]].filter(Boolean) : []);
        if (groups.length < 1 || groups.length > 2) return null;

        function full(digits, after) {
            var n = parseInt(digits, 10);
            if (digits.length === 4) return n;
            if (after === null) return digits.length === 2 ? 2000 + n : null;
            if (digits.length === 2) { var y = Math.floor(after / 100) * 100 + n; return y <= after ? y + 100 : y; }
            if (digits.length === 1) { var z = Math.floor(after / 10) * 10 + n; return z <= after ? z + 10 : z; }
            return null;
        }

        var start = full(groups[0], null);
        if (start === null) return null;
        var end = groups.length === 1 ? start + 1 : full(groups[1], start);
        if (end === null || start < {{ \App\Support\AcademicYear::MIN_YEAR }} || end > {{ \App\Support\AcademicYear::MAX_YEAR }} || end !== start + 1) return null;
        return start + '-' + end;
    }

    document.addEventListener('change', function (event) {
        var input = event.target;
        if (!input.matches || !input.matches('input[data-academic-year]')) return;
        var cleaned = tidy(input.value);
        if (cleaned) input.value = cleaned;
    });
})();
</script>
@endonce
