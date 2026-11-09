{{-- Shared "choose students" pop-up for the activity planner. Driven by window.activityTarget (see activities.blade.php). --}}
<div id="studentPickerModal" class="fixed inset-0 bg-black/50 hidden z-[60] flex items-start sm:items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="studentPickerTitle">
    <div class="bg-white rounded-2xl w-full max-w-xl max-h-[90vh] flex flex-col shadow-2xl">
        <div class="flex items-center justify-between px-6 pt-5 pb-3">
            <h3 id="studentPickerTitle" class="text-lg font-bold text-[#182438]">{{ __('Choose students') }}</h3>
            <button type="button" data-picker-close class="text-[#6b7280] hover:text-[#5f6873] text-2xl leading-none" aria-label="{{ __('Close') }}">&times;</button>
        </div>

        <div class="px-6 pb-3 space-y-3 border-b border-[#f1e7cc]">
            <input type="search" id="pickerSearch" placeholder="{{ __('Search by name…') }}" autocomplete="off"
                   class="w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none text-sm">
            <div class="grid grid-cols-1 min-[420px]:grid-cols-3 gap-2">
                <select id="pickerSection" class="px-3 py-2 border border-[#f1e7cc] rounded-lg text-sm focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('All sections') }}</option>
                </select>
                <select id="pickerYear" class="px-3 py-2 border border-[#f1e7cc] rounded-lg text-sm focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('All academic years') }}</option>
                </select>
                <select id="pickerAge" class="px-3 py-2 border border-[#f1e7cc] rounded-lg text-sm focus:ring-2 focus:ring-[#ff8555] outline-none">
                    <option value="">{{ __('All ages') }}</option>
                </select>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-[#5f6873]"><span id="pickerSelectedCount" class="font-semibold text-[#182438]">0</span> {{ __('selected') }} · <span id="pickerShownCount">0</span> {{ __('shown') }}</span>
                <span class="flex items-center gap-3">
                    <button type="button" id="pickerSelectShown" class="font-semibold text-[#ff8555] hover:text-[#f47548]">{{ __('Select all shown') }}</button>
                    <button type="button" id="pickerClear" class="font-semibold text-[#6b7280] hover:text-[#182438]">{{ __('Clear') }}</button>
                </span>
            </div>
        </div>

        <ul id="pickerList" class="flex-1 overflow-y-auto px-3 py-2 divide-y divide-[#f1e7cc]"></ul>
        <p id="pickerEmpty" class="px-6 py-8 text-center text-sm text-[#6b7280]" hidden>{{ __('No students match.') }}</p>

        <div class="flex justify-end gap-3 px-6 py-4 border-t border-[#f1e7cc]">
            <button type="button" data-picker-close class="px-5 py-2.5 border border-[#f1e7cc] rounded-lg text-[#182438] hover:bg-[#fffbf0] transition">{{ __('Cancel') }}</button>
            <button type="button" id="pickerDone" class="px-5 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('Done') }}</button>
        </div>
    </div>
</div>
