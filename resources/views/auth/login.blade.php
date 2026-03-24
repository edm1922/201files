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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="login-page-wrapper">

    <div class="login-container">
        <!-- Left Side: Overlay (Welcome) -->
        <div class="overlay-panel">
            <img src="{{ asset('logo2.png') }}" alt="CSC-DMS Logo" style="height: 150px; margin-bottom: 2px;">

            <h2>Welcome back</h2>
            <p>Enter your credentials to access the Document Management System.</p>
        </div>

        <!-- Right Side: Sign In Form -->
        <div class="form-panel">
            <!-- <img src="{{ asset('logo2.png') }}" alt="CSC-DMS Logo" style="height: 80px; margin-bottom: 20px;"> -->
            
            <form method="POST" action="{{ route('login') }}" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                @csrf
                
                <h1>Sign in</h1>
                
                <span>Please enter your credentials.</span>

                <!-- Session Status -->
                @if (session('status'))
                    <div style="margin-bottom: 15px; color: #1fb355; font-size: 0.85rem; font-weight: 600;">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Errors -->
                @if ($errors->any())
                    <div style="margin-bottom: 15px; color: #dd270d; font-size: 0.8rem; font-weight: 600; text-align: center;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required autofocus autocomplete="username" />
                
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password" />
                
                <!-- <a href="#">Forgot your password?</a> -->
                
                <button type="submit" class="btn-signin">Sign In</button>
            </form>
        </div>
    </div>

</body>
</html>
