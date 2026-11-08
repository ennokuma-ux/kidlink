@extends('layouts.app')

@section('title', __('page.login'))

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8" style="background: linear-gradient(135deg, #f9f3d5 0%, #e3f1f2 40%, #e3f2f4 70%, #f9e9ed 100%);">
    <div class="fixed top-5 right-5 z-10">
        <x-lang_switcher />
    </div>
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="mb-6">
            <a href="{{ route('home') }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium transition hover:gap-2.5"
                style="color: #5f6873;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('login.back_home') }}
            </a>
        </div>

        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="text-2xl font-extrabold tracking-tight inline-block mb-4" style="color: #182438;">
                Kid<span style="color: #ff8555;">Link</span>
            </a>
            <h2 class="text-3xl font-extrabold tracking-tight" style="color: #182438;">{{ __('login.welcome_back') }}</h2>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #182438;">{{ __('login.email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border rounded-lg outline-none transition focus:ring-2"
                    style="border-color: #f1e7cc; background: #fffbf0; color: #182438;"
                    onfocus="this.style.borderColor='#ff8555'; this.style.boxShadow='0 0 0 3px rgba(255,133,85,0.15)'"
                    onblur="this.style.borderColor='#f1e7cc'; this.style.boxShadow='none'"
                    placeholder="{{ __('login.email_placeholder') }}">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #182438;">{{ __('login.password') }}</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-3 border rounded-lg outline-none transition focus:ring-2 pr-12"
                        style="border-color: #f1e7cc; background: #fffbf0; color: #182438;"
                        onfocus="this.style.borderColor='#ff8555'; this.style.boxShadow='0 0 0 3px rgba(255,133,85,0.15)'"
                        onblur="this.style.borderColor='#f1e7cc'; this.style.boxShadow='none'"
                        placeholder="{{ __('login.password_placeholder') }}">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b949d] hover:text-[#5f6873]" aria-label="{{ __('login.show_password') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm" style="color: #5f6873;">
                    <input type="checkbox" name="remember" class="rounded" style="accent-color: #ff8555;">
                    {{ __('login.remember') }}
                </label>
            </div>
            <button type="submit"
                class="w-full text-white py-3 rounded-lg font-semibold transition transform hover:scale-[1.02]"
                style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.2);"
                onmouseover="this.style.background='#f47548'"
                onmouseout="this.style.background='#ff8555'">
                {{ __('login.submit') }}
            </button>
        </form>

        <p class="text-center mt-6 text-sm" style="color: #5f6873;">
            {{ __('login.no_account') }}
            <span class="font-semibold">{{ __('login.contact_admin') }}</span>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
        } else {
            input.type = 'password';
            button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        }
    }
</script>
@endpush
