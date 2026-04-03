<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'AgroFinance+') — Gestion financière agricole</title>
  <meta name="description" content="@yield('meta-description', 'AgroFinance+ calcule automatiquement vos indicateurs financiers agricoles et génère vos rapports PDF pour la microfinance. Essai gratuit 75 jours.')">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="manifest" href="{{ route('pwa.manifest') }}">
  <meta name="theme-color" content="#0D1F0D">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
</head>
<body class="public-page-body" style="margin:0; padding:0; background:#0D1F0D;
             font-family:'Inter',sans-serif; overflow-x:hidden;">

  <!-- ════ NAVBAR ════════════════════════════════════════════════════ -->
  <nav id="publicNav"
       style="position:fixed; top:0; left:0; right:0; z-index:100;
              padding:0 48px;
              background:rgba(13,31,13,0.00);
              backdrop-filter:blur(0px);
              border-bottom:1px solid transparent;
              transition:all 0.4s ease;">

    <div class="public-nav-inner" style="max-width:1280px; margin:0 auto; height:68px;
                display:flex; align-items:center; justify-content:space-between;">

      <!-- Logo -->
      <a href="{{ route('accueil') }}"
         style="display:flex; align-items:center; gap:10px; text-decoration:none;">
        <div style="width:36px; height:36px; border-radius:10px;
                    background:rgba(74,222,128,0.20);
                    border:1px solid rgba(74,222,128,0.35);
                    display:flex; align-items:center; justify-content:center; overflow:hidden;">
          <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" style="width:28px; height:28px; object-fit:contain; border-radius:6px;">
        </div>
        <span style="font-family:'Space Grotesk',sans-serif; font-size:16px;
                     font-weight:700; color:white; letter-spacing:-0.02em;">
          AgroFinance<span style="color:#4ade80;">+</span>
        </span>
      </a>

      <!-- Liens navigation -->
      <div class="public-nav-links" style="display:flex; align-items:center; gap:32px;">
        <a href="{{ route('aide.index') }}"
           style="font-family:'Inter',sans-serif; font-size:14px; font-weight:400;
                  color:rgba(255,255,255,0.60); text-decoration:none;
                  transition:color 0.2s;"
           onmouseover="this.style.color='white'"
           onmouseout="this.style.color='rgba(255,255,255,0.60)'">
          Comment ça marche
        </a>
        <a href="{{ route('a-propos') }}"
           style="font-family:'Inter',sans-serif; font-size:14px; font-weight:400;
                  color:rgba(255,255,255,0.60); text-decoration:none;
                  transition:color 0.2s;"
           onmouseover="this.style.color='white'"
           onmouseout="this.style.color='rgba(255,255,255,0.60)'">
          À propos
        </a>
        <a href="{{ route('contact') }}"
           style="font-family:'Inter',sans-serif; font-size:14px; font-weight:400;
                  color:rgba(255,255,255,0.60); text-decoration:none;
                  transition:color 0.2s;"
           onmouseover="this.style.color='white'"
           onmouseout="this.style.color='rgba(255,255,255,0.60)'">
          Contact
        </a>
      </div>

      <!-- CTA navbar -->
      <div class="public-nav-cta" style="display:flex; align-items:center; gap:12px;">
        <a href="{{ route('connexion') }}"
           style="font-family:'Inter',sans-serif; font-size:14px; font-weight:500;
                  color:rgba(255,255,255,0.70); text-decoration:none;
                  padding:8px 16px; transition:color 0.2s;"
           onmouseover="this.style.color='white'"
           onmouseout="this.style.color='rgba(255,255,255,0.70)'">
          Connexion
        </a>
        <a href="{{ route('inscription') }}"
           style="font-family:'Inter',sans-serif; font-size:14px; font-weight:600;
                  color:white; text-decoration:none;
                  background:#16a34a; padding:9px 20px;
                  border-radius:10px; transition:all 0.2s;
                  border:1px solid rgba(74,222,128,0.30);"
           onmouseover="this.style.background='#15803d';this.style.boxShadow='0 8px 24px rgba(22,163,74,0.35)'"
           onmouseout="this.style.background='#16a34a';this.style.boxShadow='none'">
          Essai gratuit →
        </a>
      </div>

    </div>
  </nav>
  <!-- FIN NAVBAR -->

  <!-- CONTENU DE LA PAGE -->
  @yield('content')

  <!-- ════ FOOTER ═══════════════════════════════════════════════════ -->
  <footer style="background:#060F06; border-top:1px solid rgba(255,255,255,0.06);
                 padding:48px 48px 32px;">
    <div style="max-width:1280px; margin:0 auto;">
      <div class="public-footer-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:48px; margin-bottom:48px;">

        <!-- Colonne 1 : Brand -->
        <div>
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
            <div style="width:32px; height:32px; border-radius:8px;
                        background:rgba(74,222,128,0.15);
                        border:1px solid rgba(74,222,128,0.25);
                        display:flex; align-items:center; justify-content:center;
                        overflow:hidden;">
              <img src="{{ asset('images/logo-agrofinanceplus.png') }}" alt="Logo AgroFinance+" style="width:24px; height:24px; object-fit:contain; border-radius:5px;">
            </div>
            <span style="font-family:'Space Grotesk',sans-serif; font-size:15px;
                         font-weight:700; color:white;">
              AgroFinance<span style="color:#4ade80;">+</span>
            </span>
          </div>
          <p style="font-family:'Inter',sans-serif; font-size:13px; line-height:1.7;
                    color:rgba(255,255,255,0.38); max-width:260px; margin:0 0 20px;">
            Gérez vos finances agricoles. Sachez si votre exploitation est rentable.
            Conçu au Bénin, pour le Bénin.
          </p>
          <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-family:'Inter',sans-serif; font-size:11px;
                         color:rgba(255,255,255,0.25);">Paiement via</span>
            <span style="font-family:'Inter',sans-serif; font-size:12px;
                         font-weight:600; color:rgba(255,255,255,0.50);">
              MTN MoMo · Moov Money
            </span>
          </div>
        </div>

        <!-- Colonne 2 : Produit -->
        <div>
          <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                    color:rgba(255,255,255,0.28); text-transform:uppercase;
                    letter-spacing:0.12em; margin:0 0 16px;">Produit</p>
          @foreach([
            ['Comment ça marche', 'aide.index', null],
            ['Tarifs', 'accueil', 'tarifs'],
            ['Se connecter', 'connexion', null],
            ['Créer un compte', 'inscription', null],
          ] as [$label, $route, $anchor])
          <a href="{{ route($route) }}{{ $anchor ? '#' . $anchor : '' }}"
             style="display:block; font-family:'Inter',sans-serif; font-size:13px;
                    color:rgba(255,255,255,0.45); text-decoration:none; margin-bottom:10px;
                    transition:color 0.2s;"
             onmouseover="this.style.color='rgba(255,255,255,0.80)'"
             onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            {{ $label }}
          </a>
          @endforeach
        </div>

        <!-- Colonne 3 : Entreprise -->
        <div>
          <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                    color:rgba(255,255,255,0.28); text-transform:uppercase;
                    letter-spacing:0.12em; margin:0 0 16px;">Entreprise</p>
          @foreach([
            ['À propos', 'a-propos'],
            ['Contact', 'contact'],
          ] as [$label, $route])
          <a href="{{ route($route) }}"
             style="display:block; font-family:'Inter',sans-serif; font-size:13px;
                    color:rgba(255,255,255,0.45); text-decoration:none; margin-bottom:10px;
                    transition:color 0.2s;"
             onmouseover="this.style.color='rgba(255,255,255,0.80)'"
             onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            {{ $label }}
          </a>
          @endforeach
        </div>

        <!-- Colonne 4 : Contact rapide -->
        <div>
          <p style="font-family:'Inter',sans-serif; font-size:11px; font-weight:600;
                    color:rgba(255,255,255,0.28); text-transform:uppercase;
                    letter-spacing:0.12em; margin:0 0 16px;">Nous contacter</p>
          <a href="https://wa.me/22900000000"
             style="display:flex; align-items:center; gap:8px; text-decoration:none;
                    font-family:'Inter',sans-serif; font-size:13px;
                    color:rgba(255,255,255,0.45); margin-bottom:10px; transition:color 0.2s;"
             onmouseover="this.style.color='#4ade80'"
             onmouseout="this.style.color='rgba(255,255,255,0.45)'">
            <span>📱</span> WhatsApp
          </a>
          <p style="font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(255,255,255,0.30); margin:0;">
            📍 Cotonou, Bénin
          </p>
        </div>

      </div>

      <!-- Barre de séparation + copyright -->
      <div style="height:1px; background:linear-gradient(to right,
                  transparent, rgba(255,255,255,0.08), transparent);
                  margin-bottom:24px;"></div>
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
        <p style="font-family:'Inter',sans-serif; font-size:12px;
                  color:rgba(255,255,255,0.25); margin:0;">
          © 2025 AgroFinance+ — Tous droits réservés
        </p>
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
          <a href="{{ route('conditions-utilisation') }}" style="font-family:'Inter',sans-serif; font-size:12px;
                              color:rgba(255,255,255,0.25); text-decoration:none;">
            Conditions d’utilisation
          </a>
          <a href="{{ route('confidentialite') }}" style="font-family:'Inter',sans-serif; font-size:12px;
                              color:rgba(255,255,255,0.25); text-decoration:none;">
            Confidentialité
          </a>
        </div>
      </div>
    </div>
  </footer>
  <!-- FIN FOOTER -->

  <!-- Script navbar scroll effect -->
  <script>
  window.addEventListener('scroll', () => {
    const nav = document.getElementById('publicNav');
    if (window.scrollY > 50) {
      nav.style.background    = 'rgba(13,31,13,0.92)';
      nav.style.backdropFilter= 'blur(20px)';
      nav.style.borderBottom  = '1px solid rgba(255,255,255,0.08)';
    } else {
      nav.style.background    = 'rgba(13,31,13,0.00)';
      nav.style.backdropFilter= 'blur(0px)';
      nav.style.borderBottom  = '1px solid transparent';
    }
  });

  // Reveal au scroll
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity   = '1';
        e.target.style.transform = 'translateY(0)';
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
  </script>

  @stack('scripts')
  <script>
  if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
          navigator.serviceWorker.register('{{ route('pwa.sw') }}').catch(function () {});
      });
  }
  </script>
</body>
</html>
