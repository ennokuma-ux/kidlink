@extends('layouts.teacher')

@section('title', __('teacher.settings_title'))
@section('page-title', __('teacher.settings_title'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">{{ __('teacher.profile_information') }}</h3>
        <form method="POST" action="{{ route('teacher.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.first_name') }}</label>
                <input type="text" name="first_name" value="{{ $user->first_name }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.last_name') }}</label>
                <input type="text" name="last_name" value="{{ $user->last_name }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.email') }}</label>
                <input type="email" name="email" value="{{ $user->email }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.contact') }}</label>
                <input type="text" name="contact" value="{{ $user->contact }}"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.save_changes') }}</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">{{ __('teacher.change_password') }}</h3>
        <form method="POST" action="{{ route('teacher.settings.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.current_password') }}</label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.new_password') }}</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">{{ __('teacher.confirm_password') }}</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">{{ __('teacher.update_password') }}</button>
        </form>
    </div>
</div>
@endsection

