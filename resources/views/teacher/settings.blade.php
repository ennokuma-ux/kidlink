@extends('layouts.teacher')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">Profile Information</h3>
        <form method="POST" action="{{ route('teacher.settings.update') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">First Name</label>
                <input type="text" name="first_name" value="{{ $user->first_name }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Last Name</label>
                <input type="text" name="last_name" value="{{ $user->last_name }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Email</label>
                <input type="email" name="email" value="{{ $user->email }}" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Contact</label>
                <input type="text" name="contact" value="{{ $user->contact }}"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">Save Changes</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-[#182438] mb-6">Change Password</h3>
        <form method="POST" action="{{ route('teacher.settings.password') }}" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Current Password</label>
                <input type="password" name="current_password" required
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">New Password</label>
                <input type="password" name="password" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-[#182438] mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" required minlength="8"
                    class="w-full px-4 py-3 border border-[#ebe8e1] rounded-lg focus:ring-2 focus:ring-[#ff8555] outline-none">
            </div>
            <button type="submit" class="bg-[#ff8555] text-white px-6 py-2.5 rounded-lg hover:bg-[#f47548] transition font-medium">Update Password</button>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mt-8">
    <h3 class="text-lg font-semibold text-[#182438] mb-6">Notification Preferences</h3>
    <div class="space-y-4">
        <label class="flex items-center gap-3">
            <input type="checkbox" checked class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
            <span class="text-[#182438]">Email notifications for new student registrations</span>
        </label>
        <label class="flex items-center gap-3">
            <input type="checkbox" checked class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
            <span class="text-[#182438]">Daily attendance summary</span>
        </label>
        <label class="flex items-center gap-3">
            <input type="checkbox" class="w-5 h-5 text-[#ff8555] rounded focus:ring-[#ff8555]">
            <span class="text-[#182438]">Weekly progress reports</span>
        </label>
    </div>
</div>
@endsection

