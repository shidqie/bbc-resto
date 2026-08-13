<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <script>
        // Prevent FOUC: apply dark class before render
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Saung Babakan Cinta') }} - @yield('title', 'Point of Sale')</title>

    <!-- Google Fonts Design System Tokens -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anonymous+Pro:wght@400;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">

    <!-- Tailwind CSS CDN Fallback for LAN/Wi-Fi Access -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --font-primary: 'Plus Jakarta Sans', 'Google Sans', sans-serif;
            --font-mono: 'Anonymous Pro', monospace;
            --color-primary: #0D3024;
            --color-secondary: #3B82F6;
            --color-surface: #FFFFFF;
            --color-text: #111827;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        body {
            font-family: var(--font-primary);
            color: var(--color-text);
            background-color: #F8FAFC;
        }

        .font-mono-caps {
            font-family: var(--font-mono);
            font-size: 0.875rem; /* 14px */
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }

        /* NProgress custom styling */
        #nprogress .bar {
            background: #0D3024 !important;
            height: 3px !important;
            z-index: 99999 !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px #0D3024, 0 0 5px #0D3024 !important;
        }
        #nprogress .spinner-icon {
            border-top-color: #0D3024 !important;
            border-left-color: #0D3024 !important;
        }

        .page-fade-in {
            animation: fadeInPage 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeInPage {
            from { opacity: 0.85; transform: translateY(3px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="admin-shell antialiased bg-[#F8FAFC] text-[#111827] h-screen overflow-hidden flex" 
      x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== null ? localStorage.getItem('sidebarOpen') === 'true' : window.innerWidth > 1024 }" 
      x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))"
      @resize.window="if(window.innerWidth <= 1024) sidebarOpen = false">
    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 relative z-10 transition-all duration-300 bg-[#F8FAFC] overflow-y-auto page-fade-in">
        @include('partials.topbar')
        @yield('content')
    </main>

    {{-- Komponen UI Global: Toast & Modal Konfirmasi --}}
    <x-toast />
    <x-confirm-modal />

    <!-- Instant Link Prefetcher & NProgress Speed Booster -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof NProgress !== 'undefined') {
            NProgress.configure({ showSpinner: false, speed: 300 });
        }

        // Prefetch on hover & trigger instant progress bar on click
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
                if (typeof NProgress !== 'undefined') {
                    NProgress.start();
                }
            }
        });
    });

    window.addEventListener('beforeunload', function() {
        if (typeof NProgress !== 'undefined') {
            NProgress.start();
        }
    });

    // Global Interceptor untuk Konfirmasi Form Hapus (menggunakan Modal UI)
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form.hasAttribute('data-confirm-modal')) return; // sudah memakai modal komponen

            const confirmMsg = form.getAttribute('onsubmit');
            if (confirmMsg && confirmMsg.includes('confirm(')) {
                e.preventDefault();
                const match = confirmMsg.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
                const msg = match ? match[1] : 'Apakah Anda yakin?';
                const name = msg.replace(/\?$/, '').replace(/^Hapus\s+/i, '');

                if (window.confirmDialog) {
                    window.confirmDialog({
                        title: 'Konfirmasi Hapus',
                        name: name,
                        message: 'Data yang dihapus tidak dapat dikembalikan.',
                        form: form,
                        confirmText: 'Hapus',
                        cancelText: 'Batal',
                    });
                } else {
                    form.setAttribute('data-confirm-modal', '');
                    form.onsubmit = null;
                    form.submit();
                }
            }
        });
    });
    </script>

    @stack('scripts')

    <script>
        function toggleDarkMode() {
            const html = document.documentElement;
            html.classList.add('theme-transitioning');
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('darkMode', isDark);
            setTimeout(() => html.classList.remove('theme-transitioning'), 400);
        }
    </script>
</body>
</html>
