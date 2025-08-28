<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Optik Melati') }} - Login</title>

    <!-- PWA Meta Tags -->
    <meta name="description" content="Aplikasi manajemen optik dan resep kacamata Optik Melati">
    <meta name="theme-color" content="#e74c3c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Optik Melati">
    <meta name="msapplication-TileColor" content="#e74c3c">
    <meta name="msapplication-config" content="/browserconfig.xml">

    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/image/optik-melati.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/image/optik-melati.png">
    <link rel="apple-touch-icon" href="/image/optik-melati.png">
    <link rel="manifest" href="/manifest.json">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif'],
                    },
                    colors: {
                        'optik-red': '#e74c3c',
                        'optik-orange': '#f39c12',
                    }
                }
            }
        }
    </script>

    <!-- PWA Styles -->
    <link rel="stylesheet" href="{{ asset('css/pwa.css') }}">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #e74c3c;
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #c0392b;
        }
        
        /* Smooth transitions */
        * {
            transition: all 0.2s ease-in-out;
        }
        
        /* Focus styles */
        input:focus, button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        /* Loading animation */
        .loading {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- PWA Install Button (akan muncul otomatis) -->
    
    <!-- Main Content -->
    <div class="min-h-screen">
        @yield('content')
    </div>

    <!-- PWA Scripts -->
    <script src="{{ asset('js/pwa.js') }}" defer></script>
    
    <!-- Custom Scripts -->
    <script>
        // Add loading state to form
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', function() {
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                `;
                submitBtn.disabled = true;
            });
        });
        
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.animate-fade-in');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.classList.add('opacity-100');
                }, index * 100);
            });
        });
    </script>
</body>
</html>
