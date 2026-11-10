@extends('layouts.parent')

@section('title', 'Edit Parent Profile')
@section('page-title', 'Edit Parent Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('parent.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-orange-600">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to dashboard
        </a>
    </div>
    <form method="POST" action="{{ route('parent.profile.update') }}" class="space-y-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm md:p-8">
        @csrf
        @method('PUT')
        <div>
            <h2 class="text-xl font-bold text-gray-800">Account information</h2>
            <p class="mt-1 text-sm text-gray-500">Update the contact details used for your parent account.</p>
        </div>
        @if($errors->any())
        <div class="rounded-lg border border-[#ffc9cb] bg-[#fff1f1] p-4 text-sm text-[#c9343a]">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="first_name" class="mb-1.5 block text-sm font-semibold text-gray-700">First name</label>
                <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
            </div>
            <div>
                <label for="last_name" class="mb-1.5 block text-sm font-semibold text-gray-700">Last name</label>
                <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
            </div>
        </div>
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-gray-700">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
        </div>
        <div>
            <label for="contact" class="mb-1.5 block text-sm font-semibold text-gray-700">Contact number</label>
            <input id="contact" name="contact" value="{{ old('contact', $user->contact) }}" maxlength="40" autocomplete="tel" class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
        </div>
        <div class="border-t border-gray-100 pt-5">
            <h3 class="font-semibold text-gray-800">Change password <span class="font-normal text-gray-500">(optional)</span></h3>
            <p class="mt-1 text-sm text-gray-500">Leave both password fields blank to keep your current password.</p>
            <div class="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-gray-700">New password</label>
                    <input id="password" type="password" name="password" minlength="8" autocomplete="off" class="password-entry w-full rounded-xl border-2 border-gray-300 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold text-gray-700">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" minlength="8" autocomplete="off" class="password-entry w-full rounded-xl border-2 border-gray-300 px-3 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100">
                </div>
            </div>
        </div>
        <div class="flex flex-wrap justify-end gap-3 border-t border-gray-100 pt-5">
            <a href="{{ route('parent.dashboard') }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600">Save Changes</button>
        </div>
    </form>
</div>
@endsection
