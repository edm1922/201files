<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CSC DMS') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#dd270d',
                            dark: '#1f2937',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #ffffff;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }
        .form-input:focus {
            border-color: #dd270d;
            box-shadow: 0 0 0 3px rgba(221, 39, 13, 0.1);
        }
    </style>
</head>
<body class="antialiased text-gray-900 bg-white">
    <div class="min-h-screen flex flex-col items-center justify-center p-6">

        <!-- Login Card -->
        <div class="w-full max-w-md bg-white p-8 rounded-3xl border border-gray-100 shadow-xl">
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800">Welcome Back</h2>
                <p class="text-sm text-gray-500 mt-1">Please enter your details to sign in</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-100 text-green-700 text-sm rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Username -->
                <div class="mb-5">
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-xs"></i>
                        </div>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" 
                               required autofocus autocomplete="username"
                               class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-brand-red focus:border-brand-red transition duration-200 outline-none"
                               placeholder="Enter your username">
                    </div>
                    @error('username')
                        <p class="mt-2 text-xs text-brand-red font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400 text-xs"></i>
                        </div>
                        <input id="password" type="password" name="password" 
                               required autocomplete="current-password"
                               class="block w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-brand-red focus:border-brand-red transition duration-200 outline-none"
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs text-brand-red font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-8">
                    <label for="remember_me" class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input id="remember_me" type="checkbox" name="remember" class="sr-only">
                            <div class="block w-10 h-6 bg-gray-200 rounded-full group-hover:bg-gray-300 transition"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition transform"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-600 select-none">Remember Me</span>
                    </label>
                </div>

                <style>
                    #remember_me:checked ~ .dot {
                        transform: translateX(100%);
                        background-color: #ffffff;
                    }
                    #remember_me:checked ~ div:first-of-type {
                        background-color: #dd270d;
                    }
                </style>

                <!-- Actions -->
                <button type="submit" 
                        class="w-full py-4 bg-brand-red hover:bg-red-700 text-white font-bold rounded-2xl shadow-lg shadow-red-200 transition duration-300 transform active:scale-[0.98]">
                    Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
