<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AgroFinance+') — AgroFinance+</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page-body" style="margin:0; padding:0; font-family:'Inter',sans-serif;
             background:#050f05; min-height:100vh; overflow-x:hidden; overflow-y:auto;">

    <!-- BACKGROUND PLEIN ÉCRAN -->
    <div style="position:fixed; inset:0; z-index:0;
                background-image: url('https://images.unsplash.com/photo-1508193638397-1c4234db14d8?w=1920&q=80');
                background-size:cover; background-position:center;">
        <div style="position:absolute; inset:0;
                    background: linear-gradient(135deg,
                      rgba(3,15,3,0.88) 0%,
                      rgba(8,30,8,0.75) 50%,
                      rgba(3,12,3,0.85) 100%);">
        </div>
    </div>

    <!-- CONTENEUR PRINCIPAL 2 COLONNES -->
    <div class="auth-split" style="position:relative; z-index:1; display:flex;
                min-height:100vh; width:100%;">

        <!-- ══ COLONNE GAUCHE : Branding ══ -->
        <div class="auth-split-brand" style="width:42%; display:flex; flex-direction:column;
                    justify-content:center; padding:60px 50px;
                    border-right:1px solid rgba(255,255,255,0.08); box-sizing:border-box;">
            @yield('branding')
        </div>

        <!-- ══ COLONNE DROITE : Formulaire ══ -->
        <div class="auth-split-form" style="width:58%; display:flex; align-items:center;
                    justify-content:center; padding:60px 70px; box-sizing:border-box;">
            <div style="width:100%; max-width:440px;">
                @yield('form')
            </div>
        </div>

    </div>
</body>
</html>
