<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts: Outfit for a more modern, tech feel -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">


        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body { font-family: 'Outfit', sans-serif; }
            .glass-panel {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="h-full bg-white antialiased text-slate-900">
        <div class="flex min-h-screen w-full">
            
            <!-- Left Side: Visual (Swapped) -->
            <div class="hidden lg:flex w-1/2 bg-slate-900 relative overflow-hidden items-center justify-center">
                
                <!-- Abstract Background -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900">
                    <div class="absolute top-0 -right-20 w-[600px] h-[600px] bg-indigo-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-40 animate-blob"></div>
                    <div class="absolute -bottom-32 -left-20 w-[600px] h-[600px] bg-purple-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-40 animate-blob animation-delay-2000"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-pink-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-40 animate-blob animation-delay-4000"></div>
                    <!-- Grid Overlay -->
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0wIDBoNDB2NDBIMHoiIGZpbGw9Im5vbmUiLz4KPC9zdmc+')] opacity-10" style="background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 40px 40px;"></div>
                </div>

                <!-- Content Overlay (No Logo as requested) -->
                <div class="relative z-10 p-12 text-white">
                    <div class="p-8 max-w-md">
                        <h2 class="text-4xl font-bold mb-6 leading-tight tracking-tight">System do fakturowania nowej generacji.</h2>
                        <p class="text-indigo-200 text-xl font-light leading-relaxed">Zarządzaj swoją firmą szybciej, łatwiej i bezpieczniej.</p>
                    </div>
                </div>

            </div>

            <!-- Right Side: Form (Swapped) -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-12 lg:px-24 xl:px-32 relative bg-white">
                <div class="w-full max-w-sm mx-auto">
                    {{ $slot }}
                </div>

                <div class="mt-12 text-center">
                    <p class="text-xs text-slate-400 font-medium tracking-wide">
                        &copy; {{ date('Y') }} {{ config('app.name') }}, Paweł Łaba :D
                    </p>
                </div>
            </div>

        </div>
    </body>
</html>
