@extends('layouts.teacher')

@section('title', __('teacher.settings_title'))
@section('page-title', __('teacher.settings_title'))

@section('content')
<style>
    input.password-entry::-ms-reveal,
    input.password-entry::-ms-clear,
    input.password-entry::-webkit-credentials-auto-fill-button {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">{{ __('teacher.profile_information') }}</h3>
        <p class="text-sm text-[#5f6873] mb-4">Your name and email can only be changed by an administrator. You can update your contact number below.</p>
        <form method="POST" action="{{ route('teacher.settings.profile') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.first_name') }}</label>
                <input type="text" value="{{ $user->first_name }}" readonly
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg bg-[#fffbf0] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.last_name') }}</label>
                <input type="text" value="{{ $user->last_name }}" readonly
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg bg-[#fffbf0] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.email') }}</label>
                <input type="email" value="{{ $user->email }}" readonly
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg bg-[#fffbf0] text-[#5f6873] cursor-not-allowed outline-none">
            </div>
            <div>
                <label for="contact" class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.contact') }}</label>
                <input id="contact" type="text" name="contact" value="{{ old('contact', $user->contact) }}" maxlength="255" autocomplete="tel"
                    class="w-full px-4 py-3 border border-[#f1e7cc] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none @error('contact') border-[#ff595e] @enderror">
                @error('contact')
                    <p class="mt-1 text-sm text-[#ff595e]">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">Save contact</button>
        </form>
    </div>



    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">{{ __('teacher.change_password') }}</h3>
        <form method="POST" action="{{ route('teacher.settings.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.current_password') }}</label>
                <input type="password" name="current_password" required autocomplete="off"
                    class="password-entry w-full px-4 py-3 border-2 border-[#e7dcc5] rounded-xl focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.new_password') }}</label>
                <input type="password" name="password" required minlength="8" autocomplete="off"
                    class="password-entry w-full px-4 py-3 border-2 border-[#e7dcc5] rounded-xl focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.confirm_password') }}</label>
                <input type="password" name="password_confirmation" required minlength="8" autocomplete="off"
                    class="password-entry w-full px-4 py-3 border-2 border-[#e7dcc5] rounded-xl focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.update_password') }}</button>
        </form>
    </div>
</div>
@endsection

