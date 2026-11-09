{{--
    Who an activity is for: a group (section) or individual students.
    Include inside the add and edit forms:  @include('teacher.partials.activity-target', ['prefix' => 'add'])
    The picker pop-up is shared and lives once at the bottom of the page
    (teacher.partials.activity-student-picker).
--}}
@php
    $mode = old('target_mode', 'section');
    $input = 'w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none';
@endphp
<div data-activity-target>
    <input type="hidden" name="target_mode" value="{{ $mode }}" data-target-mode>

    <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('Who is this activity for?') }}</label>
    <div class="grid grid-cols-2 gap-2 p-1 rounded-xl bg-[#fffbf0] border border-[#f1e7cc]" role="group">
        <button type="button" data-mode-button="section"
                class="px-3 py-2 rounded-lg text-sm font-semibold transition">{{ __('Group') }}</button>
        <button type="button" data-mode-button="individual"
                class="px-3 py-2 rounded-lg text-sm font-semibold transition">{{ __('Individual students') }}</button>
    </div>

    <div class="mt-3" data-panel="section">
        <label class="block text-xs font-semibold text-[#5f6873] mb-1" for="{{ $prefix }}Group">{{ __('Group') }}</label>
        <select name="group_name" id="{{ $prefix }}Group" data-target-group class="{{ $input }}">
            <option value="">{{ __('All my students') }}</option>
            @foreach($classNames as $className)
                <option value="{{ $className }}" @selected(old('group_name') === $className)>{{ $className }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-3" data-panel="individual" hidden>
        <div class="flex items-center justify-between gap-3">
            <p class="text-sm text-[#5f6873]"><span class="font-semibold text-[#182438]" data-target-count>0</span> {{ __('selected') }}</p>
            <button type="button" data-open-picker
                    class="px-4 py-2 rounded-lg border border-[#ff8555] text-[#c2410c] bg-white hover:bg-[#fff0e6] text-sm font-semibold transition">
                {{ __('Choose students') }}
            </button>
        </div>
        <div class="mt-2 flex flex-wrap gap-2" data-target-chips></div>
        <p class="mt-2 text-xs text-[#ff595e]" data-target-error hidden>{{ __('Choose at least one student.') }}</p>
        <div data-target-ids></div>
    </div>
</div>
