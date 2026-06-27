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

    <!-- Password Reset Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
        <h2 class="text-2xl font-bold text-slate-900 mb-2">Reset Password</h2>
        <p class="text-slate-600 text-sm mb-6">Enter your email address and we'll send you a link to reset your password.</p>

        <!-- Success Message -->
        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        <!-- Error Messages -->
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

        <!-- Forgot Password Form -->
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Field -->
            <div class="mb-6">
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
                        autofocus
                    >
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
            >
                Send Password Reset Link
            </button>
        </form>
    </div>

    <!-- Footer Links -->
    <div class="text-center text-blue-100">
        <p class="text-sm">
            <a href="{{ route('login') }}" class="text-blue-300 hover:text-white font-medium transition duration-200">
                Back to login
            </a>
        </p>
    </div>
@endsection
