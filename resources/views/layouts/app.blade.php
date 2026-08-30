<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Gizi-Logic' }}</title>
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4338ca">
    <link rel="icon" href="/favicon.ico">
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4f46e5', // Indigo 600
                        secondary: '#10b981', // Emerald 500
                    }
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <nav class="bg-primary text-white p-4 shadow-md sticky top-0 z-50 flex justify-between items-center">
        <div class="text-xl font-bold tracking-wider">Gizi-Logic 🥦</div>
        @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="text-sm font-semibold hover:text-gray-200 transition">Logout</button>
        </form>
        @endauth
    </nav>
    <main class="max-w-md mx-auto min-h-screen">
        {{ $slot }}
    </main>
    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/serviceworker.js');
            });
        }
    </script>
</body>
</html>
