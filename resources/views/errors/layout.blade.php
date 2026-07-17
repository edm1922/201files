<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('brand.name') }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset(config('brand.logo')) }}">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --company-primary: {{ config('brand.primary_color') }};
            --company-primary-hover: {{ config('brand.primary_color') }}dd;
            --company-primary-light: {{ config('brand.primary_color') }}1a;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 24px;
            overflow: hidden;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: #fff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .error-media {
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }

        .error-media img, .error-media i {
            width: 180px;
            height: 180px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .error-media i {
            font-size: 5rem;
            background: var(--company-primary-light);
            color: var(--company-primary);
        }

        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: var(--company-primary);
            line-height: 0.8;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .error-message {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background-color: var(--company-primary);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px var(--company-primary-light);
        }

        .btn-home:hover {
            background-color: var(--company-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(221, 39, 13, 0.25);
            color: #fff;
        }

        .background-blob {
            position: fixed;
            z-index: 0;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--company-primary-light) 0%, transparent 70%);
            border-radius: 50%;
            top: -200px;
            right: -200px;
            filter: blur(80px);
            opacity: 0.5;
        }

        .background-blob-2 {
            position: fixed;
            z-index: 0;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            filter: blur(80px);
            opacity: 0.5;
        }
    </style>
</head>

<body>
    <div class="background-blob"></div>
    <div class="background-blob-2"></div>

    <div class="container">
        <div class="error-media">
            @yield('media')
        </div>

        <div class="error-code">@yield('code')</div>
        <h1 class="error-title">@yield('title')</h1>
        <p class="error-message">
            @yield('message')
        </p>

        <a href="{{ url('/') }}" class="btn-home">
            <i class="fas fa-home"></i>
            Back to Dashboard
        </a>
    </div>
</body>

</html>
