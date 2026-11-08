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
        <a href="{{ route('announcements.index') }}" class="border border-[#ebe8e1] hover:bg-[#faf8f3] text-[#5f6873] px-6 py-2.5 rounded-lg transition font-medium self-start md:self-auto">
            {{ __('announcements.cancel') }}
        </a>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    <form method="POST" action="{{ route('announcements.store') }}" class="space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-medium text-[#182438] mb-2">{{ __('announcements.title') }} <span class="text-[#f05d56]">*</span></label>
            <input type="text" name="title" id="title" required maxlength="255"
                value="{{ old('title') }}"
                class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none @error('title') border-[#f05d56] @enderror"
                placeholder="{{ __('announcements.short_headline_placeholder') }}">
            @error('title')
                <p class="text-sm text-[#f05d56] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="body" class="block text-sm font-medium text-[#182438] mb-2">{{ __('announcements.announcement') }} <span class="text-[#f05d56]">*</span></label>
            <textarea name="body" id="body" rows="8" required maxlength="5000"
                class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none resize-none @error('body') border-[#f05d56] @enderror"
                placeholder="{{ __('announcements.write_full_announcement_here') }}">{{ old('body') }}</textarea>
            @error('body')
                <p class="text-sm text-[#f05d56] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-[#ebe8e1]">
            <a href="{{ route('announcements.index') }}" class="px-6 py-2.5 border border-[#ebe8e1] rounded-lg text-[#5f6873] hover:bg-[#faf8f3] transition">{{ __('announcements.cancel') }}</a>
            <button type="submit" class="px-6 py-2.5 bg-[#ff8555] text-white rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('announcements.publish') }}</button>
        </div>
    </form>
</div>
@endsection
