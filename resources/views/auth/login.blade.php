@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8" style="background: linear-gradient(135deg, #f9f3d5 0%, #e3f1f2 40%, #e3f2f4 70%, #f9e9ed 100%);">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ url('/') }}" class="text-2xl font-extrabold tracking-tight inline-block mb-4" style="color: #182438;">
                Kid<span style="color: #ff8555;">Link</span>
            </a>
            <h2 class="text-3xl font-extrabold tracking-tight" style="color: #182438;">Welcome Back</h2>
            <p class="mt-2" style="color: #5f6873;">Sign in to your KidLink account</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #182438;">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-3 border rounded-lg outline-none transition focus:ring-2"
                    style="border-color: #ebe8e1; background: #faf8f3; color: #182438;"
                    onfocus="this.style.borderColor='#ff8555'; this.style.boxShadow='0 0 0 3px rgba(255,133,85,0.15)'"
                    onblur="this.style.borderColor='#ebe8e1'; this.style.boxShadow='none'"
                    placeholder="Enter your email">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2" style="color: #182438;">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border rounded-lg outline-none transition focus:ring-2"
                    style="border-color: #ebe8e1; background: #faf8f3; color: #182438;"
                    onfocus="this.style.borderColor='#ff8555'; this.style.boxShadow='0 0 0 3px rgba(255,133,85,0.15)'"
                    onblur="this.style.borderColor='#ebe8e1'; this.style.boxShadow='none'"
                    placeholder="Enter your password">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm" style="color: #5f6873;">
                    <input type="checkbox" name="remember" class="rounded" style="accent-color: #ff8555;">
                    Remember me
                </label>
            </div>
            <button type="submit"
                class="w-full text-white py-3 rounded-lg font-semibold transition transform hover:scale-[1.02]"
                style="background: #ff8555; box-shadow: 0 4px 12px rgba(255,133,85,0.2);"
                onmouseover="this.style.background='#f47548'"
                onmouseout="this.style.background='#ff8555'">
                Sign In
            </button>
        </form>

        <p class="text-center mt-6 text-sm" style="color: #5f6873;">
            Don't have an account?
            <span class="font-semibold">Contact your daycare administrator</span>
        </p>
    </div>
</div>
@endsection
