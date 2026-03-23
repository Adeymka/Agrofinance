{{--
    Bannière d'installation PWA.
    Apparaît automatiquement sur mobile quand le navigateur
    déclenche l'événement `beforeinstallprompt`.

    Intégration : inclure une fois dans app-mobile.blade.php (juste avant </body>)
    avec <x-pwa-install-prompt />.
--}}
<div id="pwaInstallBanner"
     class="hidden fixed bottom-[72px] inset-x-0 z-50 px-4"
     role="status"
     aria-live="polite">
    <div style="
        background: linear-gradient(135deg, rgba(22,163,74,0.18), rgba(13,31,13,0.92));
        border: 1px solid rgba(74,222,128,0.35);
        border-radius: 16px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        backdrop-filter: blur(16px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.40);
    ">
        <span style="font-size:28px; flex-shrink:0;">🌱</span>

        <div style="flex:1; min-width:0;">
            <div style="font-family:'Space Grotesk',sans-serif; font-size:13px; font-weight:600; color:white; line-height:1.3;">
                Installer AgroFinance+
            </div>
            <div style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.55); margin-top:2px;">
                Accès rapide depuis votre écran d'accueil
            </div>
        </div>

        <button id="pwaInstallBtn"
                style="
                    flex-shrink:0;
                    font-family:'Inter',sans-serif;
                    font-size:12px; font-weight:600;
                    color:white;
                    background:#16a34a;
                    border:none; cursor:pointer;
                    border-radius:10px;
                    padding:8px 14px;
                    white-space:nowrap;
                ">
            Installer
        </button>

        <button id="pwaInstallDismiss"
                aria-label="Fermer"
                style="
                    flex-shrink:0;
                    background:none; border:none; cursor:pointer;
                    color:rgba(255,255,255,0.40);
                    font-size:18px; line-height:1;
                    padding:4px;
                ">
            ✕
        </button>
    </div>
</div>

<script>
(function () {
    var banner  = document.getElementById('pwaInstallBanner');
    var btnInstall  = document.getElementById('pwaInstallBtn');
    var btnDismiss  = document.getElementById('pwaInstallDismiss');
    var deferredPrompt = null;

    // Ne plus afficher si l'utilisateur a déjà refusé récemment (7 jours)
    var dismissed = localStorage.getItem('pwa_install_dismissed');
    if (dismissed && (Date.now() - parseInt(dismissed, 10)) < 7 * 24 * 3600 * 1000) {
        return;
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        // Afficher la bannière après 3 secondes (pas intrusif au 1er chargement)
        setTimeout(function () {
            if (banner) banner.classList.remove('hidden');
        }, 3000);
    });

    if (btnInstall) {
        btnInstall.addEventListener('click', function () {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function (result) {
                if (result.outcome === 'accepted') {
                    if (banner) banner.classList.add('hidden');
                }
                deferredPrompt = null;
            });
        });
    }

    if (btnDismiss) {
        btnDismiss.addEventListener('click', function () {
            if (banner) banner.classList.add('hidden');
            localStorage.setItem('pwa_install_dismissed', Date.now().toString());
        });
    }

    // Cacher si déjà installé
    window.addEventListener('appinstalled', function () {
        if (banner) banner.classList.add('hidden');
    });
})();
</script>
