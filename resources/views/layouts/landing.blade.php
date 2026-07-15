<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Saung Babakan Cinta' }}</title>
    <meta name="description" content="{{ $description ?? 'Saung Babakan Cinta — Rumah Makan Nusantara. Dine-in, Catering & Nasi Box.' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #FAFAF7; color: #3D3D3D; }
        h1, h2, h3, h4, h5, h6, .font-serif { font-family: 'Outfit', sans-serif; color: #0D3024; }
        .bg-batik {
            background-image: url('data:image/svg+xml;utf8,<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"><path d="M20 0l20 20-20 20L0 20z" fill="%230D3024" fill-opacity="0.03" fill-rule="evenodd"/></svg>');
        }
        html { scroll-behavior: smooth; }
        .mobile-nav { display: none; }
        .mobile-nav.active { display: flex; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col relative">

    <x-landing.navbar />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-landing.footer />

    @stack('scripts')
</body>
</html>
