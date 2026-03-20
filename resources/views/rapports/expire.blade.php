<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lien expiré — AgroFinance+</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-agro-fond flex flex-col items-center justify-center p-6">
    <div class="max-w-md text-center space-y-4">
        <div class="flex justify-center text-agro-vert">
            <x-icon name="link" class="w-12 h-12" />
        </div>
        <h1 class="text-xl font-bold text-gray-800">Lien expiré</h1>
        <p class="text-sm text-gray-600">Ce rapport n’est plus accessible. Le lien a expiré après 72 heures.</p>
        <a href="{{ url('/connexion') }}" class="inline-block mt-4 text-sm font-semibold text-agro-vert underline">Se connecter à AgroFinance+</a>
    </div>
</body>
</html>
