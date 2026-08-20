<!DOCTYPE html>
<html lang="id">
<head>
    <script>
        // Prevent FOUC: apply dark class before render
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Saung Babakan Cinta' }}</title>
    <meta name="description" content="{{ $description ?? 'Saung Babakan Cinta — Rumah Makan Sunda. Dine-in, Katering & Nasi Box.' }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Anonymous+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN Fallback for Wi-Fi LAN Access -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'Google_Sans': ['Outfit', 'sans-serif'],
                        'Anonymous_Pro': ['Anonymous Pro', 'monospace'],
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
                        success: 'var(--color-success)',
                        warning: 'var(--color-warning)',
                        danger: 'var(--color-danger)',
                    }
                }
            }
        }
    </script>
    <style>
        :root {
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
            --color-success: #16A34A;
            --color-warning: #D97706;
            --color-danger: #DC2626;
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
            --color-success: #34d399;
            --color-warning: #fbbf24;
            --color-danger: #f87171;
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: var(--color-canvas); color: var(--color-body); }
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: 'Outfit', sans-serif; color: var(--color-primary); }
        .bg-batik {
            background-image: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M20 0l20 20-20 20L0 20z" fill="%230D3024" fill-opacity="0.03" fill-rule="evenodd"/></svg>');
        }
        html { scroll-behavior: smooth; }
        .mobile-nav { display: none; }
        .mobile-nav.active { display: flex; }
        /* Alpine.js x-cloak: hide elements until Alpine.js is initialized */
        [x-cloak] { display: none !important; }
        /* Mobile menu smooth transition */
        #mobile-nav-menu {
            display: none;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
            max-height: 0;
            opacity: 0;
            background: #fff;
            border-top: 1px solid #f3f4f6;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        #mobile-nav-menu.is-open {
            display: block;
            max-height: 90vh;
            opacity: 1;
            overflow-y: auto;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative">

    <x-landing.navbar />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-landing.footer />

    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const isLoggedIn = {{ Auth::guard('pelanggan')->check() ? 'true' : 'false' }};
        function handleOrderClick(event, url) {
            if (url) {
                window.location.href = url;
            }
        }
    </script>
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
