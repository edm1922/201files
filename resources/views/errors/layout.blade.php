<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo2.png') }}">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-card">
            
            <div class="error-icon">
                @yield('icon')
            </div>

            <div class="error-code">
                @yield('code')
            </div>

            <h1 class="error-title">
                @yield('title')
            </h1>

            <p class="error-message">
                @yield('message')
            </p>

            <a href="{{ url('/') }}" class="btn-error-home shadow-sm">
                <i class="fas fa-home"></i>
                Return
            </a>
        </div>
    </div>
</body>
</html>
