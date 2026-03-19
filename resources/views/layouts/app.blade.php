<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1B5E20">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen pb-20">
        @yield('content')

        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur border-t border-gray-100">
            <div class="max-w-md mx-auto px-3 py-2">
                <div class="grid grid-cols-5 gap-1 items-center">
                    <a href="{{ url('/') }}" class="text-center flex flex-col items-center gap-1 text-xs text-gray-700">
                        <span class="text-lg leading-none">🏠</span>
                        <span>Accueil</span>
                    </a>
                    <a href="#" class="text-center flex flex-col items-center gap-1 text-xs text-gray-700">
                        <span class="text-lg leading-none">🌱</span>
                        <span>Campagnes</span>
                    </a>
                    <a href="#" class="flex justify-center items-center">
                        <span class="bg-agro-vert text-white w-11 h-11 rounded-full flex items-center justify-center text-lg active:scale-95 transition-transform">
                            +
                        </span>
                    </a>
                    <a href="#" class="text-center flex flex-col items-center gap-1 text-xs text-gray-700">
                        <span class="text-lg leading-none">📊</span>
                        <span>Rapports</span>
                    </a>
                    <a href="#" class="text-center flex flex-col items-center gap-1 text-xs text-gray-700">
                        <span class="text-lg leading-none">👤</span>
                        <span>Profil</span>
                    </a>
                </div>
            </div>
        </nav>
    </body>
</html>

