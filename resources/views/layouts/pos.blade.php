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

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-saung.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-saung.png') }}">

    <!-- Google Fonts Design System Tokens -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/phosphor/phosphor.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            font-family: 'Outfit', sans-serif !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #f3f4f6 !important;
            padding: 12px 14px !important;
            background: #ffffff !important;
            width: auto !important;
        }
        .flatpickr-months {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 4px 4px 10px 4px !important;
            position: relative !important;
        }
        .flatpickr-current-month {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            color: #111827 !important;
            padding: 0 !important;
            position: static !important;
            width: 100% !important;
            transform: none !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #f9fafb !important;
            background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%234b5563%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 8px center !important;
            background-size: 12px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 4px 24px 4px 10px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #111827 !important;
            cursor: pointer !important;
            outline: none !important;
            box-shadow: none !important;
            transition: all 0.2s ease !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
            background-color: #f3f4f6 !important;
            border-color: #d1d5db !important;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months:focus {
            outline: none !important;
            border-color: #0D3024 !important;
            box-shadow: 0 0 0 2px rgba(13, 48, 36, 0.12) !important;
        }
        .flatpickr-current-month .numInputWrapper {
            display: inline-flex !important;
            align-items: center !important;
            width: 64px !important;
            border-radius: 8px !important;
            background: #f9fafb !important;
            border: 1px solid #e5e7eb !important;
            padding: 2px 4px !important;
            margin-left: 2px !important;
        }
        .flatpickr-current-month .numInputWrapper input.cur-year {
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #111827 !important;
            border: none !important;
            outline: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 2px 0 !important;
        }
        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 28px !important;
            height: 28px !important;
            border-radius: 8px !important;
            background: #f3f4f6 !important;
            color: #4b5563 !important;
            fill: #4b5563 !important;
            padding: 0 !important;
            top: 8px !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
        }
        .flatpickr-months .flatpickr-prev-month {
            left: 6px !important;
        }
        .flatpickr-months .flatpickr-next-month {
            right: 6px !important;
        }
        .flatpickr-months .flatpickr-prev-month:hover,
        .flatpickr-months .flatpickr-next-month:hover {
            background: #e5e7eb !important;
            color: #111827 !important;
            fill: #111827 !important;
        }
        .flatpickr-months .flatpickr-prev-month svg,
        .flatpickr-months .flatpickr-next-month svg {
            width: 12px !important;
            height: 12px !important;
            fill: currentColor !important;
            stroke: currentColor !important;
        }
        span.flatpickr-weekday {
            color: #6b7280 !important;
            font-weight: 700 !important;
            font-size: 12px !important;
        }
        .flatpickr-day {
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            color: #1f2937 !important;
            height: 36px !important;
            line-height: 36px !important;
            max-width: 36px !important;
            margin: 1px !important;
            border: 1px solid transparent !important;
            transition: all 0.15s ease !important;
        }
        .flatpickr-day:hover {
            background: #E8F0EC !important;
            color: #0D3024 !important;
        }
        .flatpickr-day.today {
            border: 1.5px solid #0D3024 !important;
            color: #0D3024 !important;
            font-weight: 800 !important;
            background: transparent !important;
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background: #0D3024 !important;
            border-color: #0D3024 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 6px -1px rgba(13, 48, 36, 0.25) !important;
        }
        .flatpickr-day.today.selected {
            background: #0D3024 !important;
            color: #ffffff !important;
        }
        .flatpickr-day.flatpickr-disabled,
        .flatpickr-day.flatpickr-disabled:hover,
        .flatpickr-day.prevMonthDay,
        .flatpickr-day.nextMonthDay {
            color: #d1d5db !important;
            background: transparent !important;
            border-color: transparent !important;
            cursor: not-allowed !important;
            opacity: 0.55 !important;
        }
    </style>

    <!-- Tailwind CSS CDN Fallback for LAN/Wi-Fi Access -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['Outfit', 'sans-serif'],
                        mono: ['Outfit', 'sans-serif'],
                    },
                    fontSize: {
                        xs: ['11px', '1.45'],
                        sm: ['13px', '1.5'],
                        base: ['14px', '1.55'],
                        lg: ['16px', '1.5'],
                        xl: ['18px', '1.4'],
                        '2xl': ['21px', '1.3'],
                        '3xl': ['26px', '1.25'],
                        '4xl': ['32px', '1.2'],
                        '5xl': ['40px', '1.15'],
                        '6xl': ['48px', '1.1'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: 'rgb(var(--color-primary-rgb) / <alpha-value>)',
                            container: 'rgb(var(--color-primary-container-rgb) / <alpha-value>)',
                            soft: 'rgb(var(--color-primary-soft-rgb) / <alpha-value>)',
                        },
                        secondary: {
                            DEFAULT: 'rgb(var(--color-secondary-rgb) / <alpha-value>)',
                            container: 'rgb(var(--color-secondary-container-rgb) / <alpha-value>)',
                            soft: 'rgb(var(--color-secondary-soft-rgb) / <alpha-value>)',
                        },
                        accent: 'rgb(var(--color-accent-rgb) / <alpha-value>)',
                        canvas: 'rgb(var(--color-canvas-rgb) / <alpha-value>)',
                        surface: 'rgb(var(--color-surface-rgb) / <alpha-value>)',
                        body: 'rgb(var(--color-body-rgb) / <alpha-value>)',
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
            --font-primary: 'Outfit', sans-serif;
            --font-mono: 'Outfit', sans-serif;
            --color-primary: #0D3024;
            --color-primary-rgb: 13 48 36;
            --color-primary-container: #0a2219;
            --color-primary-container-rgb: 10 34 25;
            --color-primary-soft: #E8F0EC;
            --color-primary-soft-rgb: 232 240 236;
            --color-secondary: #B8860B;
            --color-secondary-rgb: 184 134 11;
            --color-secondary-container: #d4a843;
            --color-secondary-container-rgb: 212 168 67;
            --color-secondary-soft: #F5F1E6;
            --color-secondary-soft-rgb: 245 241 230;
            --color-accent: #D4A843;
            --color-accent-rgb: 212 168 67;
            --color-canvas: #FAFAF7;
            --color-canvas-rgb: 250 250 247;
            --color-surface: #FFFFFF;
            --color-surface-rgb: 255 255 255;
            --color-body: #111827;
            --color-body-rgb: 17 24 39;
            --color-text: #111827;
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 12px;
        }

        html.dark {
            --color-primary: #d4a843;
            --color-primary-rgb: 212 168 67;
            --color-primary-container: #2a2e3c;
            --color-primary-container-rgb: 42 46 60;
            --color-primary-soft: rgba(212,168,67,0.12);
            --color-primary-soft-rgb: 42 46 60;
            --color-secondary: #f0c45e;
            --color-secondary-rgb: 240 196 94;
            --color-secondary-container: #B8860B;
            --color-secondary-container-rgb: 184 134 11;
            --color-secondary-soft: rgba(212,168,67,0.1);
            --color-secondary-soft-rgb: 34 38 50;
            --color-accent: #f0c45e;
            --color-accent-rgb: 240 196 94;
            --color-canvas: #0f1117;
            --color-canvas-rgb: 15 17 23;
            --color-surface: #1a1d27;
            --color-surface-rgb: 26 29 39;
            --color-body: #cbd5e1;
            --color-body-rgb: 203 213 225;
            --color-text: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            color: var(--color-text);
            background-color: var(--color-canvas);
        }

        /* Admin headings use brand color (auto flips to gold in dark) */
        .admin-shell h1,
        .admin-shell h2,
        .admin-shell h3,
        .admin-shell h4,
        .admin-shell h5,
        .admin-shell h6 {
            color: var(--color-primary) !important;
            font-family: 'Outfit', sans-serif !important;
        }
        /* Preserve explicitly-colored headings */
        .admin-shell h2.text-white,
        .admin-shell h3.text-white {
            color: #ffffff !important;
        }
        .admin-shell h2.text-amber-400,
        .admin-shell h3.text-amber-400 {
            color: #fbbf24 !important;
        }
        .admin-shell h3.text-red-800,
        .admin-shell h4.text-red-800,
        .admin-shell h4.text-red-900 {
            color: #991b1b !important;
        }
        html.dark .admin-shell h3.text-red-800,
        html.dark .admin-shell h4.text-red-800,
        html.dark .admin-shell h4.text-red-900 {
            color: var(--dark-danger) !important;
        }
        .admin-shell h4.text-yellow-800 {
            color: #854d0e !important;
        }
        html.dark .admin-shell h4.text-yellow-800 {
            color: #fbbf24 !important;
        }

        .font-mono-caps {
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.875rem; /* 14px */
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ── Universal Table Typography (Outfit Font & Consistent Scale) ── */
        table,
        .data-table,
        .admin-table {
            font-family: 'Outfit', sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        table th,
        thead th,
        .table-header th {
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.8125rem !important; /* 13px */
            font-weight: 700 !important;
            line-height: 1.25rem !important;
            letter-spacing: 0.015em;
        }

        table td,
        tbody td,
        .table-cell td {
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.875rem !important; /* 14px */
            line-height: 1.35rem !important;
        }

        table code,
        table .font-mono,
        table [class*="font-mono"] {
            font-family: 'Outfit', monospace, sans-serif !important;
            font-size: 0.8125rem !important; /* 13px */
            font-weight: 700 !important;
            letter-spacing: 0.02em;
        }

        table .badge,
        table [class*="rounded-full"],
        table [class*="rounded-lg"],
        table [class*="rounded-md"] {
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.75rem !important; /* 12px */
            font-weight: 600 !important;
        }

        table button,
        table a.button,
        table .btn {
            font-family: 'Outfit', sans-serif !important;
            font-size: 0.75rem !important; /* 12px */
            font-weight: 700 !important;
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
            background: var(--color-primary) !important;
            height: 3px !important;
            z-index: 99999 !important;
        }
        #nprogress .peg {
            box-shadow: 0 0 10px var(--color-primary), 0 0 5px var(--color-primary) !important;
        }
        #nprogress .spinner-icon {
            border-top-color: var(--color-primary) !important;
            border-left-color: var(--color-primary) !important;
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
<body class="admin-shell antialiased bg-canvas text-body h-screen overflow-hidden flex" 
      x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== null ? localStorage.getItem('sidebarOpen') === 'true' : window.innerWidth > 1024 }" 
      x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))"
      @resize.window="if(window.innerWidth <= 1024) sidebarOpen = false">
    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 relative z-10 transition-all duration-300 bg-canvas overflow-y-auto page-fade-in">
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

            const msg = form.getAttribute('data-confirm');
            if (msg) {
                e.preventDefault();
                const name = msg.replace(/\?$/, '').replace(/^Hapus\s+/i, '');

                if (window.confirmDialog) {
                    window.confirmDialog({
                        title: 'Konfirmasi Hapus',
                        name: name,
                        message: msg,
                        form: form,
                        confirmText: 'Hapus',
                        cancelText: 'Batal',
                    });
                } else {
                    form.setAttribute('data-confirm-modal', '');
                    form.submit();
                }
        });
    });
    </script>

    <!-- Flatpickr JS & Indonesian Locale -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        function initAppDatepickers() {
            if (typeof flatpickr !== 'undefined') {
                flatpickr.localize(flatpickr.l10ns.id);
                document.querySelectorAll('input[type="date"], input.datepicker').forEach(function(el) {
                    if (!el._flatpickr) {
                        const currentVal = el.value;
                        flatpickr(el, {
                            locale: 'id',
                            altInput: true,
                            altFormat: 'd/m/Y',
                            dateFormat: 'Y-m-d',
                            defaultDate: currentVal || null,
                            allowInput: true,
                            disableMobile: true
                        });
                    }
                });
            }
        }
        document.addEventListener('DOMContentLoaded', initAppDatepickers);
        // Also re-check when dynamically loaded / Alpine updates
        window.initAppDatepickers = initAppDatepickers;
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
