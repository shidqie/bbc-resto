<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Saung Babakan Cinta') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">

        <!-- Tailwind CSS CDN Fallback for LAN/Wi-Fi Access -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontSize: {
                            xs: ['13px', '1.45'],
                            sm: ['15px', '1.5'],
                            base: ['16px', '1.55'],
                            lg: ['18px', '1.5'],
                            xl: ['20px', '1.4'],
                            '2xl': ['24px', '1.3'],
                            '3xl': ['30px', '1.25'],
                            '4xl': ['36px', '1.2'],
                            '5xl': ['48px', '1.15'],
                            '6xl': ['60px', '1.1'],
                        },
                    }
                }
            }
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>

        <style>
            #nprogress .bar { background: #0D3024 !important; height: 3px !important; z-index: 99999 !important; }
            .page-fade-in { animation: fadeInPage 0.25s cubic-bezier(0.16, 1, 0.3, 1); }
            @keyframes fadeInPage { from { opacity: 0.85; transform: translateY(3px); } to { opacity: 1; transform: translateY(0); } }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#F8FAFC]">
        <div class="min-h-screen bg-[#F8FAFC]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if(isset($header) && !empty($header))
                <header class="bg-white shadow-xs">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="page-fade-in">
                @if(isset($slot) && !empty($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>

        <x-toast />
        <x-confirm-modal />

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof NProgress !== 'undefined') NProgress.configure({ showSpinner: false, speed: 300 });

            const prefetchedUrls = new Set();
            document.addEventListener('mouseover', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && link.origin === location.origin && !link.hash && !link.target && !prefetchedUrls.has(link.href)) {
                    prefetchedUrls.add(link.href);
                    const prefetchLink = document.createElement('link');
                    prefetchLink.rel = 'prefetch';
                    prefetchLink.href = link.href;
                    document.head.appendChild(prefetchLink);
                }
            });

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && link.origin === location.origin && !link.hash && link.target !== '_blank' && !e.ctrlKey && !e.metaKey) {
                    if (typeof NProgress !== 'undefined') NProgress.start();
                }
            });
        });
        </script>
    </body>
</html>
