@extends('layouts.announcements')

@section('title', __('announcements.new_announcement'))
@section('page-title', __('announcements.new_announcement_title'))

@section('content')
<div class="bg-white rounded-xl shadow-sm p-6 mb-6 max-w-3xl">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-[#182438]">{{ __('announcements.new_announcement') }}</h2>
            <p class="text-sm text-[#5f6873] mt-1">{{ __('announcements.visible_to_every_teacher_and_parent') }}</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="border border-[#f1e7cc] hover:bg-[#fffbf0] text-[#5f6873] px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            {{ __('announcements.cancel') }}
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    @if ($errors->any())
        <div role="alert" aria-live="assertive" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
            <p class="font-bold">{{ __('Please correct the following before publishing:') }}</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('announcements.store') }}" class="space-y-6"
          x-data="{
              poll: @js((bool) old('poll_enabled')),
              options: @js(array_pad(array_values((array) old('poll_options', [])), \App\Models\AnnouncementPoll::MIN_OPTIONS, '')),
              min: {{ \App\Models\AnnouncementPoll::MIN_OPTIONS }},
              max: {{ \App\Models\AnnouncementPoll::MAX_OPTIONS }},
              add() { if (this.options.length < this.max) { this.options.push(''); this.$nextTick(() => { const f = this.$refs.optionList.querySelectorAll('input[type=text]'); f[f.length - 1]?.focus(); }); } },
              remove(i) { if (this.options.length > this.min) this.options.splice(i, 1); }
          }">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-[#182438] mb-2">{{ __('announcements.title') }} <span class="text-[#ff595e]">*</span></label>
            <input type="text" name="title" id="title" required maxlength="255"
                value="{{ old('title') }}"
                class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none @error('title') border-[#ff595e] @enderror"
                placeholder="{{ __('announcements.short_headline_placeholder') }}">
            @error('title')
                <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="body" class="block text-sm font-medium text-[#182438] mb-2">{{ __('announcements.announcement') }} <span class="text-[#ff595e]">*</span></label>
            <textarea name="body" id="body" rows="8" required maxlength="5000"
                class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none resize-none @error('body') border-[#ff595e] @enderror"
                placeholder="{{ __('announcements.write_full_announcement_here') }}">{{ old('body') }}</textarea>
            @error('body')
                <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Label and pin --}}
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <span class="block text-sm font-medium text-[#182438] mb-2">{{ __('Tag') }}</span>
                <div class="inline-flex flex-wrap gap-2">
                    @php $oldTag = old('tag', ''); @endphp
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="" class="sr-only peer" @checked($oldTag === '')>
                        <span class="block px-4 py-2 rounded-lg border border-[#f1e7cc] text-sm font-semibold text-[#5f6873] peer-checked:bg-[#182438] peer-checked:text-white peer-checked:border-[#182438] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]">{{ __('None') }}</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="urgent" class="sr-only peer" @checked($oldTag === 'urgent')>
                        <span class="block px-4 py-2 rounded-lg border border-[#ffd97a] text-sm font-semibold text-[#8a5a00] peer-checked:bg-[#ffca3a] peer-checked:text-[#3d2a00] peer-checked:border-[#ffca3a] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]">{{ __('Urgent') }}</span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="tag" value="emergency" class="sr-only peer" @checked($oldTag === 'emergency')>
                        <span class="block px-4 py-2 rounded-lg border border-[#ff595e] text-sm font-semibold text-[#c0261f] peer-checked:bg-[#c0261f] peer-checked:text-white peer-checked:border-[#c0261f] peer-focus-visible:ring-2 peer-focus-visible:ring-[#ff8555]">{{ __('Emergency') }}</span>
                    </label>
                </div>
                @error('tag')
                    <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-[#182438] mb-2">{{ __('Pin') }}</span>
                <label class="inline-flex items-center gap-3 cursor-pointer rounded-lg border border-[#f1e7cc] px-4 py-2.5 hover:bg-[#fffbf0] transition">
                    <input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned')) class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                    <span class="text-sm text-[#182438]">{{ __('Keep at the top of the board') }}</span>
                </label>
            </div>
        </div>

        {{-- Optional poll --}}
        <div class="rounded-xl border border-[#f1e7cc] p-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="poll_enabled" value="1" x-model="poll" class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                <span class="text-sm font-semibold text-[#182438]">{{ __('Add a poll to this announcement') }}</span>
            </label>

            <div x-show="poll" x-cloak style="display:none;" class="mt-4 space-y-4">
                <div>
                    <label for="poll_question" class="block text-sm font-medium text-[#182438] mb-2">{{ __('Poll question') }} <span class="text-[#ff595e]">*</span></label>
                    <input type="text" name="poll_question" id="poll_question" maxlength="255" value="{{ old('poll_question') }}" :required="poll"
                        class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none @error('poll_question') border-[#ff595e] @enderror"
                        placeholder="{{ __('e.g. Which day works best for the parents’ meeting?') }}">
                    @error('poll_question')
                        <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-[#182438] mb-2">{{ __('Options') }} <span class="text-[#8b949d] font-normal">({{ __('2 to 6') }})</span></span>
                    <div class="space-y-2" x-ref="optionList">
                        <template x-for="(option, i) in options" :key="i">
                            <div class="flex items-center gap-2">
                                <input type="text" name="poll_options[]" maxlength="150" x-model="options[i]" :required="poll && i < min"
                                    :placeholder="@js(__('Option')) + ' ' + (i + 1)"
                                    class="flex-1 px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
                                <button type="button" @click="remove(i)" x-show="options.length > min" :aria-label="@js(__('Remove option'))"
                                    class="w-9 h-9 rounded-lg text-xl leading-none text-[#8b949d] hover:text-[#ff595e] hover:bg-[#ffe9ea] transition">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="add()" x-show="options.length < max"
                        class="mt-3 text-sm font-semibold text-[#6a4c93] hover:underline">+ {{ __('Add option') }}</button>
                    @foreach(['poll_options', 'poll_options.*'] as $field)
                        @error($field)
                            <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="poll_multiple" value="1" @checked(old('poll_multiple')) class="w-4 h-4 text-[#ff8555] rounded focus:ring-[#ff8555]">
                        <span class="text-sm text-[#182438]">{{ __('Allow choosing more than one option') }}</span>
                    </label>
                    <div>
                        <label for="poll_closes_at" class="block text-sm font-medium text-[#182438] mb-1">{{ __('Closes on') }} <span class="text-[#8b949d] font-normal">({{ __('optional') }})</span></label>
                        <input type="datetime-local" name="poll_closes_at" id="poll_closes_at" value="{{ old('poll_closes_at') }}"
                            class="w-full px-4 py-2.5 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none @error('poll_closes_at') border-[#ff595e] @enderror">
                        @error('poll_closes_at')
                            <p class="text-sm text-[#ff595e] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-xs text-[#8b949d]">{{ __('Everyone who can read the board can vote and change their answer until the poll closes. Only totals are shown, not who voted for what. A poll cannot be edited once published.') }}</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#f1e7cc]">
            <a href="{{ route('announcements.index') }}" class="px-6 py-2.5 border border-[#f1e7cc] rounded-lg text-[#5f6873] hover:bg-[#fffbf0] transition">{{ __('announcements.cancel') }}</a>
            <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('announcements.publish') }}</button>
        </div>
    </form>
</div>
@endsection
