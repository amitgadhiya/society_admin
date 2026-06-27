@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="text-center mb-10">
        <div class="inline-block bg-gradient-to-br from-blue-400 to-blue-500 p-4 rounded-full mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-2">Society Management System</h1>
        <p class="text-blue-100">Admin Portal</p>
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

            <!-- Mobile Field -->
            <div class="mb-5">
                <label for="mobile" class="block text-sm font-medium text-slate-700 mb-2">Mobile Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <input
                        id="mobile"
                        type="tel"
                        name="mobile"
                        value="{{ old('mobile') }}"
                        class="w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('mobile') border-red-500 @enderror"
                        placeholder="Enter your mobile number"
                        required
                        autocomplete="tel"
                    >
                </div>
                @error('mobile')
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
