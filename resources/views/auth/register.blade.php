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

    <!-- Register Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8 mb-6">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Create Account</h2>

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

        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name Field -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                <input 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('name') border-red-500 @enderror"
                    placeholder="John Doe"
                    required 
                    autocomplete="name"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('email') border-red-500 @enderror"
                    placeholder="you@example.com"
                    required 
                    autocomplete="email"
                >
                @error('email')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 @error('password') border-red-500 @enderror"
                    placeholder="••••••••"
                    required 
                    autocomplete="new-password"
                >
                @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">Confirm Password</label>
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                    placeholder="••••••••"
                    required 
                    autocomplete="new-password"
                >
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
            >
                Create Account
            </button>
        </form>
    </div>

    <!-- Footer Links -->
    <div class="text-center text-blue-100">
        <p class="text-sm">
            Already have an account? 
            <a href="{{ route('login') }}" class="text-blue-300 hover:text-white font-medium transition duration-200">
                Sign in here
            </a>
        </p>
    </div>
@endsection
