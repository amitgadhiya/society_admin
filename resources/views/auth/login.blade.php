@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="text-center mb-10">
        <div class="inline-block bg-gradient-to-br from-blue-400 to-blue-500 p-4 rounded-full mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-2">Rejection Analysis</h1>
        <p class="text-blue-100">Overall Equipment Effectiveness</p>
    </div>

    <!-- Login Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Welcome Back</h2>

        <!-- Display Errors -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Field -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <input 
                        id="email" 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror"
                        placeholder="you@example.com"
                        required 
                        autocomplete="email"
                    >
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm6-10V7a3 3 0 00-3-3H9a3 3 0 00-3 3v4h12V7z"></path>
                        </svg>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('password') border-red-500 @enderror"
                        placeholder="••••••••"
                        required 
                        autocomplete="current-password"
                    >
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="w-4 h-4 rounded border-slate-300 text-blue-500 focus:ring-blue-500 cursor-pointer"
                        {{ old('remember') ? 'checked' : '' }}
                    >
                    <span class="ml-2 text-sm text-slate-700">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium transition duration-200">
                        Forgot password?
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
            >
                Sign In
            </button>
        </form>
    </div>

    <!-- Footer Links -->
    <div class="text-center text-blue-100">
        <p class="text-sm">
            Don't have an account? 
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-blue-300 hover:text-white font-medium transition duration-200">
                    Sign up here
                </a>
            @endif
        </p>
    </div>
@endsection
