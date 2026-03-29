<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1F0D">
    <title>Hors ligne — AgroFinance+</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            background: #0D1F0D;
            color: white;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 24px;
        }
        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(74,222,128,0.20);
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 420px;
            width: 100%;
            backdrop-filter: blur(16px);
        }
        .icon { font-size: 56px; margin-bottom: 20px; }
        h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: 12px;
        }
        p {
            font-size: 14px;
            color: rgba(255,255,255,0.50);
            line-height: 1.65;
            margin-bottom: 28px;
        }
        .btn {
            display: inline-block;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            background: #16a34a;
            padding: 12px 28px;
            border-radius: 12px;
            border: 1px solid rgba(74,222,128,0.30);
            cursor: pointer;
        }
        .status {
            margin-top: 20px;
            font-size: 12px;
            color: rgba(255,255,255,0.28);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📡</div>
        <h1>Pas de connexion internet</h1>
        <p>
            AgroFinance+ est hors ligne.<br>
            Reconnectez-vous pour accéder à vos données et continuer la gestion de votre exploitation.
        </p>
        <p style="font-size:13px;color:rgba(255,255,255,0.42);margin-bottom:20px;line-height:1.55;">
            Si vous aviez enregistré des dépenses ou des recettes sans réseau, elles restent sur cet appareil jusqu’à la prochaine connexion ; elles seront alors envoyées vers votre compte.
        </p>
        <button class="btn" onclick="window.location.reload()">
            Réessayer
        </button>
        <p class="status" id="statusMsg">Vérification de la connexion…</p>
    </div>
    <script>
    (function () {
        var msg = document.getElementById('statusMsg');
        function check() {
            msg.textContent = navigator.onLine ? 'Connexion détectée — rechargement…' : 'Toujours hors ligne.';
            if (navigator.onLine) window.location.href = '{{ url('/') }}';
        }
        window.addEventListener('online', check);
        setInterval(check, 5000);
        check();
    })();
    </script>
</body>
</html>
