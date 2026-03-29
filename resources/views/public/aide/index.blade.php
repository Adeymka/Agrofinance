@extends('layouts.app-public')
@section('title', 'Centre d\'aide — AgroFinance+')
@section('meta-description', 'Recherchez des réponses : compte, campagnes, transactions, indicateurs financiers agricoles, rapports PDF, abonnements.')

@section('content')

{{-- ══ HERO HELP CENTER ══════════════════════════════════ --}}
<section class="aide-page-hero" style="background:linear-gradient(180deg, #0A1500 0%, #0D1F0D 100%);
                text-align:center;">
  <div style="max-width:700px; margin:0 auto;">

    <div style="width:64px; height:64px; border-radius:18px; margin:0 auto 24px;
                background:rgba(74,222,128,0.12); border:1px solid rgba(74,222,128,0.25);
                display:flex; align-items:center; justify-content:center; font-size:28px;">
      💬
    </div>

    <h1 style="font-family:'Space Grotesk',sans-serif; font-size:44px; font-weight:700;
               color:white; letter-spacing:-0.03em; margin:0 0 16px; line-height:1.1;">
      Comment pouvons-nous<br>
      vous <span style="color:#4ade80;">aider</span> ?
    </h1>

    <p style="font-family:'Inter',sans-serif; font-size:17px;
              color:rgba(255,255,255,0.45); margin:0 0 40px; line-height:1.6;">
      Trouvez rapidement une réponse à votre question.
    </p>

    <div id="searchWrap" style="position:relative; max-width:560px; margin:0 auto;">

      <div style="position:absolute; left:18px; top:50%; transform:translateY(-50%);
                  pointer-events:none; z-index:2;">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:20px;height:20px;"
             fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.35)" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
      </div>

      <input id="searchInput"
             type="text"
             placeholder="Rechercher... ex: gain ou perte, OTP, rapport PDF"
             autocomplete="off"
             style="width:100%; padding:16px 48px 16px 52px;
                    background:rgba(255,255,255,0.07);
                    backdrop-filter:blur(12px);
                    border:1px solid rgba(255,255,255,0.15);
                    border-radius:14px; color:white;
                    font-family:'Inter',sans-serif; font-size:15px;
                    outline:none; box-sizing:border-box;
                    transition:all 0.2s;"
             onfocus="this.style.borderColor='rgba(74,222,128,0.50)';
                      this.style.background='rgba(255,255,255,0.10)'"
             onblur="this.style.borderColor='rgba(255,255,255,0.15)';
                     this.style.background='rgba(255,255,255,0.07)'">

      <button type="button" id="clearSearch"
              style="position:absolute; right:16px; top:50%; transform:translateY(-50%);
                     background:none; border:none; cursor:pointer; display:none;
                     color:rgba(255,255,255,0.40); padding:4px;">
        ✕
      </button>

      <div id="searchResults"
           style="position:absolute; top:calc(100% + 8px); left:0; right:0; z-index:50;
                  background:rgba(10,21,0,0.96); backdrop-filter:blur(20px);
                  border:1px solid rgba(255,255,255,0.12); border-radius:14px;
                  overflow:hidden; display:none;
                  box-shadow:0 24px 48px rgba(0,0,0,0.50);">
      </div>

    </div>

    <div style="margin-top:24px; display:flex; flex-wrap:wrap;
                justify-content:center; gap:8px;">
      <span style="font-family:'Inter',sans-serif; font-size:12px;
                   color:rgba(255,255,255,0.28);">Populaires :</span>
      @foreach($populaires->take(4) as $art)
      <a href="{{ route('aide.article', [$art->categorie->slug, $art->slug]) }}"
         style="font-family:'Inter',sans-serif; font-size:12px; font-weight:500;
                color:rgba(74,222,128,0.70); text-decoration:none;
                padding:4px 12px; border-radius:999px;
                border:1px solid rgba(74,222,128,0.20);
                background:rgba(74,222,128,0.06);
                transition:all 0.2s;"
         onmouseover="this.style.color='#4ade80';this.style.borderColor='rgba(74,222,128,0.40)'"
         onmouseout="this.style.color='rgba(74,222,128,0.70)';this.style.borderColor='rgba(74,222,128,0.20)'">
        {{ $art->titre }}
      </a>
      @endforeach
    </div>

  </div>
</section>

<div style="height:1px; background:linear-gradient(to right,
            transparent, rgba(74,222,128,0.20) 30%,
            rgba(74,222,128,0.20) 70%, transparent);">
</div>

<section class="aide-section" style="background:#0D1F0D; padding:80px 48px;">
  <div style="max-width:1100px; margin:0 auto;">

    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:22px; font-weight:600;
               color:rgba(255,255,255,0.85); letter-spacing:-0.02em;
               margin:0 0 32px; text-align:center;">
      Parcourir par rubrique
    </h2>

    <div class="aide-cat-grid" style="display:grid; gap:20px;">
      @foreach($categories as $cat)
      <a href="{{ route('aide.categorie', $cat->slug) }}"
         style="display:block; text-decoration:none;
                background:rgba(255,255,255,0.05);
                border:1px solid rgba(255,255,255,0.09);
                border-radius:18px; padding:28px 24px;
                transition:all 0.25s ease;"
         onmouseover="this.style.background='rgba(255,255,255,0.09)';
                      this.style.borderColor='rgba(74,222,128,0.25)';
                      this.style.transform='translateY(-3px)'"
         onmouseout="this.style.background='rgba(255,255,255,0.05)';
                     this.style.borderColor='rgba(255,255,255,0.09)';
                     this.style.transform='translateY(0)'">

        <div style="width:50px; height:50px; border-radius:14px; font-size:22px;
                    background:rgba(74,222,128,0.10);
                    border:1px solid rgba(74,222,128,0.20);
                    display:flex; align-items:center; justify-content:center;
                    margin-bottom:16px;">
          {{ $cat->icone }}
        </div>

        <h3 style="font-family:'Space Grotesk',sans-serif; font-size:16px; font-weight:600;
                   color:rgba(255,255,255,0.90); letter-spacing:-0.015em;
                   margin:0 0 8px;">
          {{ $cat->nom }}
        </h3>

        <p style="font-family:'Inter',sans-serif; font-size:13px;
                  color:rgba(255,255,255,0.40); line-height:1.55;
                  margin:0 0 16px;">
          {{ $cat->description }}
        </p>

        <div style="display:flex; align-items:center; justify-content:space-between;">
          <span style="font-family:'Inter',sans-serif; font-size:12px;
                       color:rgba(255,255,255,0.30);">
            {{ $cat->articles_count }} article{{ $cat->articles_count > 1 ? 's' : '' }}
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;
               color:rgba(74,222,128,0.50);"
               fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </div>

      </a>
      @endforeach
    </div>

  </div>
</section>

<div style="max-width:800px; margin:0 auto; height:1px;
            background:linear-gradient(to right, transparent,
            rgba(255,255,255,0.08) 30%, rgba(255,255,255,0.08) 70%, transparent);">
</div>

@if($populaires->count() > 0)
<section class="aide-section" style="background:#0D1F0D; padding:60px 48px 80px;">
  <div style="max-width:1100px; margin:0 auto;">

    <h2 style="font-family:'Space Grotesk',sans-serif; font-size:20px; font-weight:600;
               color:rgba(255,255,255,0.80); letter-spacing:-0.02em; margin:0 0 24px;">
      Articles les plus consultés
    </h2>

    <div class="aide-pop-grid" style="display:grid; gap:12px;">
      @foreach($populaires as $art)
      <a href="{{ route('aide.article', [$art->categorie->slug, $art->slug]) }}"
         style="display:flex; align-items:center; gap:14px; text-decoration:none;
                padding:16px 20px; border-radius:12px;
                background:rgba(255,255,255,0.04);
                border:1px solid rgba(255,255,255,0.07);
                transition:all 0.2s;"
         onmouseover="this.style.background='rgba(255,255,255,0.08)';
                      this.style.borderColor='rgba(255,255,255,0.14)'"
         onmouseout="this.style.background='rgba(255,255,255,0.04)';
                     this.style.borderColor='rgba(255,255,255,0.07)'">

        <div style="width:36px; height:36px; border-radius:10px; flex-shrink:0;
                    background:rgba(74,222,128,0.08);
                    border:1px solid rgba(74,222,128,0.15);
                    display:flex; align-items:center; justify-content:center;
                    font-size:16px;">
          {{ $art->categorie->icone }}
        </div>

        <div style="flex:1; min-width:0;">
          <div style="font-family:'Inter',sans-serif; font-size:14px; font-weight:500;
                      color:rgba(255,255,255,0.82); white-space:nowrap;
                      overflow:hidden; text-overflow:ellipsis;">
            {{ $art->titre }}
          </div>
          <div style="font-family:'Inter',sans-serif; font-size:11px;
                      color:rgba(255,255,255,0.30); margin-top:2px;">
            {{ $art->categorie->nom }}
          </div>
        </div>

        <svg xmlns="http://www.w3.org/2000/svg"
             style="width:14px;height:14px;flex-shrink:0;color:rgba(255,255,255,0.25);"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>

      </a>
      @endforeach
    </div>

  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
(function () {
  const aideBaseUrl = @json(url('/aide'));
  const input = document.getElementById('searchInput');
  const results = document.getElementById('searchResults');
  const clearBtn = document.getElementById('clearSearch');
  const searchWrap = document.getElementById('searchWrap');
  let timer = null;

  function clearSearch() {
    input.value = '';
    clearBtn.style.display = 'none';
    hideResults();
    input.focus();
  }

  clearBtn.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    clearSearch();
  });

  input.addEventListener('input', function () {
    const q = this.value.trim();
    clearBtn.style.display = q.length > 0 ? 'block' : 'none';

    clearTimeout(timer);
    if (q.length < 2) { hideResults(); return; }

    timer = setTimeout(function () { fetchResults(q); }, 300);
  });

  async function fetchResults(q) {
    try {
      const res = await fetch(`{{ route('aide.recherche') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await res.json();
      renderResults(data.resultats || [], q);
    } catch (e) {
      hideResults();
    }
  }

  function renderResults(items, q) {
    if (items.length === 0) {
      results.style.display = 'block';
      results.innerHTML = `
      <div style="padding:20px 20px; text-align:center;
                  font-family:'Inter',sans-serif; font-size:14px;
                  color:rgba(255,255,255,0.35);">
        Aucun résultat pour « ${escapeHtml(q)} »
      </div>`;
      return;
    }

    results.style.display = 'block';
    results.innerHTML = items.map(function (item) {
      return `
    <a href="${item.url}"
       style="display:flex; align-items:flex-start; gap:12px; padding:14px 18px;
              text-decoration:none; transition:background 0.15s;
              border-bottom:1px solid rgba(255,255,255,0.06);"
       onmouseover="this.style.background='rgba(74,222,128,0.08)'"
       onmouseout="this.style.background='transparent'">
      <span style="font-size:18px; flex-shrink:0; margin-top:1px;">${item.icone}</span>
      <div style="flex:1; min-width:0;">
        <div style="font-family:'Inter',sans-serif; font-size:14px; font-weight:500;
                    color:rgba(255,255,255,0.88); margin-bottom:2px;">
          ${escapeHtml(item.titre)}
        </div>
        <div style="font-family:'Inter',sans-serif; font-size:12px;
                    color:rgba(255,255,255,0.35);">
          ${escapeHtml(item.categorie)}
          ${item.resume ? ' · ' + escapeHtml(item.resume.substring(0, 80)) + '...' : ''}
        </div>
      </div>
    </a>`;
    }).join('') + `
    <div style="padding:10px 18px; text-align:center;">
      <a href="${aideBaseUrl}?q=${encodeURIComponent(input.value.trim())}"
         style="font-family:'Inter',sans-serif; font-size:12px;
                color:rgba(74,222,128,0.60); text-decoration:none;">
        Voir tous les résultats →
      </a>
    </div>`;
  }

  function hideResults() {
    results.style.display = 'none';
    results.innerHTML = '';
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  document.addEventListener('click', function (e) {
    if (searchWrap && !searchWrap.contains(e.target)) {
      hideResults();
    }
  });

  input.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { hideResults(); input.blur(); }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    var q0 = params.get('q');
    if (q0 && q0.trim().length >= 2) {
      input.value = q0.trim();
      clearBtn.style.display = 'block';
      fetchResults(q0.trim());
    }
  });
})();
</script>
@endpush
