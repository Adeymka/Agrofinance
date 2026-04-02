@extends($layout)
@section('title', 'Rapports PDF — AgroFinance+')
@section('page-title', 'Rapports PDF')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* Tokens : --af-* (app.css) */
.rpt-page-head { padding: 20px 0 4px; }
.rpt-page-title {
    font-family: var(--font-display), sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--af-text-high);
    letter-spacing: -0.03em;
    margin-bottom: 4px;
}
.rpt-page-sub {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.3);
    margin-bottom: 20px;
}
.rpt-block {
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: var(--af-radius-lg);
    padding: 18px;
    margin-bottom: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.rpt-block-title {
    font-family: var(--font-display), sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.75);
    letter-spacing: -0.02em;
    margin-bottom: 14px;
}
.rpt-label {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--af-text-muted);
    margin-bottom: 6px;
}
.rpt-select, .rpt-input {
    width: 100%;
    background: var(--af-glass-05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    color: var(--af-text-body-strong);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    box-sizing: border-box;
}
.rpt-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.3)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 40px;
}
.rpt-select:focus, .rpt-input:focus { border-color: var(--af-chip-active-border); }
.rpt-field { margin-bottom: 12px; }
.rpt-hint {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-dim);
    margin-top: 6px;
}
.rpt-date-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}
.rpt-upsell {
    background: rgba(74, 222, 128, 0.05);
    border: 1px solid rgba(74, 222, 128, 0.15);
    border-radius: var(--af-radius-lg);
    padding: 24px;
    text-align: center;
    margin-bottom: 16px;
}
.rpt-upsell-icon { font-size: 36px; margin-bottom: 12px; }
.rpt-upsell-title {
    font-family: var(--font-display), sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.88);
    letter-spacing: -0.02em;
    margin-bottom: 8px;
}
.rpt-upsell-sub {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-muted);
    line-height: 1.55;
    margin-bottom: 16px;
}
.rpt-upsell-sub strong { color: rgba(255, 255, 255, 0.75); font-weight: 600; }
.rpt-upsell-btn {
    display: inline-block;
    background: var(--af-color-accent-dark);
    color: #fff;
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    border: 1px solid var(--af-tx-type-rec-border);
}
.rpt-submit {
    width: 100%;
    background: var(--af-color-accent-dark);
    color: #fff;
    font-family: var(--font-ui), sans-serif;
    font-size: 14px;
    font-weight: 700;
    padding: 15px;
    border-radius: 14px;
    border: 1px solid var(--af-tx-type-rec-border);
    cursor: pointer;
    margin-top: 4px;
    transition: opacity 0.15s;
}
.rpt-submit:active { opacity: 0.8; }
.rpt-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}
.rpt-item:last-child { border-bottom: none; }
.rpt-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--af-green-tint-bg);
    border: 1px solid var(--af-green-tint-border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}
.rpt-item-body { flex: 1; min-width: 0; }
.rpt-item-type {
    font-family: var(--font-display), sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.85);
    letter-spacing: -0.01em;
}
.rpt-item-meta {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.3);
    margin-top: 1px;
}
.rpt-download-btn {
    font-family: var(--font-ui), sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--af-color-accent);
    text-decoration: none;
    background: var(--af-green-tint-bg);
    border: 1px solid var(--af-green-icon-border);
    border-radius: 10px;
    padding: 7px 12px;
    white-space: nowrap;
    flex-shrink: 0;
}
.rpt-copy-btn {
    font-size: 11px;
    color: var(--af-color-accent);
    opacity: 0.72;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: 4px;
    text-decoration: underline;
    font-family: var(--font-ui), sans-serif;
}
.rpt-badge-vert {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    background: var(--af-filter-active-bg);
    border: 1px solid var(--af-filter-active-border);
    color: var(--af-color-accent);
    white-space: nowrap;
}
.rpt-badge-rouge {
    font-family: var(--font-ui), sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    background: var(--af-red-tint-bg);
    border: 1px solid var(--af-red-tint-border);
    color: var(--af-color-danger);
    white-space: nowrap;
}
.rpt-empty {
    text-align: center;
    padding: 24px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: var(--af-radius-lg);
}
.rpt-empty p {
    font-family: var(--font-ui), sans-serif;
    font-size: 13px;
    color: var(--af-text-muted);
    margin: 0;
}
.rpt-share-note {
    font-family: var(--font-ui), sans-serif;
    font-size: 11px;
    color: var(--af-text-muted);
    line-height: 1.5;
    margin: 0 0 18px 0;
    max-width: 100%;
}
</style>
@endpush

<div class="rpt-page-head">
    <h1 class="rpt-page-title">Rapports PDF</h1>
    <p class="rpt-page-sub">Génération et téléchargement de rapports</p>
    <p class="rpt-share-note">Partage : le lien permet de consulter le PDF sans compte ; il cesse de fonctionner après la durée affichée. Ne le transmettez qu’à des personnes de confiance.</p>
</div>

{{-- ── Générer un rapport ── --}}
@if (! ($infoAbonnement['peut_pdf'] ?? false))
    <div class="rpt-upsell">
        <div class="rpt-upsell-icon">🔒</div>
        <div class="rpt-upsell-title">Fonctionnalité Premium</div>
        <p class="rpt-upsell-sub">Plan actuel : <strong>{{ ucfirst($infoAbonnement['plan_metier'] ?? '—') }}</strong>. Les rapports PDF nécessitent le plan <strong>Essentielle</strong> ou supérieur (souscription ou simulation en local).</p>
        <a href="{{ route('abonnement') }}" class="rpt-upsell-btn">Voir les plans →</a>
    </div>
@else
    @if($exploitations->count() > 1)
        <div class="rpt-block">
            <div class="rpt-block-title">Sélectionner une exploitation</div>
            <form method="get" class="inline">
                <div class="rpt-field">
                    <div class="rpt-label">Exploitation</div>
                    <select name="exploitation_id" onchange="this.form.submit()" class="rpt-select">
                        @foreach ($exploitations as $exp)
                            <option value="{{ $exp->id }}" @selected($exploitation->id === $exp->id)>{{ $exp->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    @endif
    <div class="rpt-block">
        <div class="rpt-block-title">Générer un nouveau rapport</div>
        <form method="POST" action="{{ route('rapports.generer') }}">
            @csrf
            <div class="rpt-field">
                <div class="rpt-label">Exploitation</div>
                <select name="exploitation_id" class="rpt-select" required>
                    @foreach ($exploitations as $exp)
                        <option value="{{ $exp->id }}" @selected($exploitation->id === $exp->id)>{{ $exp->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rpt-field">
                <div class="rpt-label">Type de rapport</div>
                <select name="type" class="rpt-select">
                    <option value="standard">Rapport standard</option>
                    @if ($infoAbonnement['peut_dossier'] ?? false)
                        <option value="dossier_credit">Dossier crédit</option>
                    @endif
                </select>
                @if (! ($infoAbonnement['peut_dossier'] ?? false))
                    <p class="rpt-hint">Le dossier crédit est réservé aux plans Pro / Coopérative.</p>
                @endif
            </div>
            <div class="rpt-field">
                <div class="rpt-label">Couverture historique</div>
                <select name="periode_scope" class="rpt-select" id="periodeScope">
                    <option value="all">Toute la période (depuis le début)</option>
                    <option value="custom">Dates personnalisées</option>
                </select>
            </div>
            <div class="rpt-date-grid" id="dateFields" style="display: none;">
                <div>
                    <div class="rpt-label">Début</div>
                    <input type="date" name="periode_debut" value="{{ old('periode_debut', now()->startOfMonth()->toDateString()) }}" class="rpt-input">
                </div>
                <div>
                    <div class="rpt-label">Fin</div>
                    <input type="date" name="periode_fin" value="{{ old('periode_fin', now()->toDateString()) }}" class="rpt-input">
                </div>
            </div>
            <button type="submit" class="rpt-submit">📄 Générer le PDF</button>
        </form>
    </div>
@endif

{{-- ── Rapports générés ── --}}
@if($rapports->isNotEmpty())
    <div class="rpt-block">
        <div class="rpt-block-title">Rapports générés</div>
        @foreach ($rapports as $r)
            @php
                $expire = $r->lien_expire_le;
                $valide = $expire && $expire->isFuture();
                $heures = $valide ? max(1, (int) ceil(now()->diffInMinutes($expire) / 60)) : 0;
            @endphp
            <div class="rpt-item">
                <div class="rpt-item-icon">📄</div>
                <div class="rpt-item-body">
                    <div class="rpt-item-type">
                        {{ $r->type === 'dossier_credit' ? 'Dossier crédit' : 'Rapport standard' }}
                        @if($r->exploitation)— {{ $r->exploitation->nom }}@endif
                    </div>
                    <div class="rpt-item-meta">
                        {{ $r->created_at->format('d/m/Y H:i') }}
                        @if($valide && $r->lien_token)
                            · <span class="rpt-badge-vert">Valide ~{{ $heures }}h</span>
                            <button type="button" class="rpt-copy-btn js-copy" data-url="{{ route('rapports.partager', $r->lien_token) }}">Copier lien</button>
                        @elseif($expire && !$valide)
                            · <span class="rpt-badge-rouge">Expiré</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('rapports.telecharger', $r->id) }}" class="rpt-download-btn">↓ PDF</a>
            </div>
        @endforeach
    </div>
@else
    <div class="rpt-empty">
        <p>Aucun rapport généré pour le moment.</p>
    </div>
@endif

<script>
// Toggle date fields based on periode_scope (MOBILE)
document.getElementById('periodeScope')?.addEventListener('change', function() {
    const field = document.getElementById('dateFields');
    if (field) field.style.display = this.value === 'custom' ? 'grid' : 'none';
});
if (document.getElementById('periodeScope')?.value === 'custom') {
    const field = document.getElementById('dateFields');
    if (field) field.style.display = 'grid';
}

// Toggle date fields - Desktop version
document.getElementById('periodeScope_desk')?.addEventListener('change', function() {
    const field = document.getElementById('dateFields_desk');
    if (field) field.style.display = this.value === 'custom' ? 'grid' : 'none';
});
if (document.getElementById('periodeScope_desk')?.value === 'custom') {
    const field = document.getElementById('dateFields_desk');
    if (field) field.style.display = 'grid';
}

// Clear dates when "all" is selected before form submission
document.querySelectorAll('form').forEach(function(form) {
    form.addEventListener('submit', function() {
        // Check if Toute la période is selected (periodeScope = "all")
        const periodescopeField = form.querySelector('[name="periode_scope"]');
        if (periodescopeField && periodescopeField.value === 'all') {
            // Clear the date fields
            const debutField = form.querySelector('[name="periode_debut"]');
            const finField = form.querySelector('[name="periode_fin"]');
            if (debutField) debutField.value = '';
            if (finField) finField.value = '';
        }
    });
});

// Handle copy buttons
document.querySelectorAll('.js-copy').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var u = btn.getAttribute('data-url');
        if (navigator.clipboard && u) {
            navigator.clipboard.writeText(u).then(function () {
                btn.textContent = '✓ Copié';
                setTimeout(function () { btn.textContent = 'Copier lien'; }, 2000);
            });
        }
    });
});
</script>
@else{{-- ════ DESKTOP (original) ════ --}}
    <p class="text-xs text-gray-500 mb-4 max-w-3xl leading-relaxed">Partage : le lien permet de consulter le PDF sans compte ; il cesse de fonctionner après la durée affichée. Ne le transmettez qu’à des personnes de confiance.</p>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="card">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Générer un rapport</h2>
                @if (! ($infoAbonnement['peut_pdf'] ?? false))
                    <p class="text-sm text-gray-600">Plan actuel : <strong>{{ ucfirst($infoAbonnement['plan_metier'] ?? '—') }}</strong>. Les rapports PDF nécessitent <strong>Essentielle</strong> ou supérieur. <a href="{{ route('abonnement') }}" class="text-agro-vert font-medium underline">Voir les plans</a></p>
                @else
                    <form method="POST" action="{{ route('rapports.generer') }}" class="space-y-4">
                        @csrf
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Exploitation</label><select name="exploitation_id" required class="input-field">@foreach ($exploitations as $exp)<option value="{{ $exp->id }}" @selected($exploitation->id === $exp->id)>{{ $exp->nom }}</option>@endforeach</select></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label><select name="type" class="input-field"><option value="standard">Rapport standard</option>@if ($infoAbonnement['peut_dossier'] ?? false)<option value="dossier_credit">Dossier crédit</option>@endif</select></div>
                        @if (! ($infoAbonnement['peut_dossier'] ?? false))<p class="text-xs text-gray-500">Le rapport <strong>dossier crédit</strong> (Pro / Coopérative) n'est pas inclus dans votre plan.</p>@endif
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Couverture</label><select name="periode_scope" id="periodeScope_desk" class="input-field"><option value="all">Toute la période</option><option value="custom">Dates personnalisées</option></select></div>
                        <div class="grid grid-cols-2 gap-3" id="dateFields_desk" style="display: none;">
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Début</label><input type="date" name="periode_debut" value="{{ old('periode_debut', now()->startOfMonth()->toDateString()) }}" class="input-field"></div>
                            <div><label class="block text-xs font-medium text-gray-600 mb-1">Fin</label><input type="date" name="periode_fin" value="{{ old('periode_fin', now()->toDateString()) }}" class="input-field"></div>
                        </div>
                        <button type="submit" class="btn-primary w-full py-3">Générer le PDF</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="card overflow-x-auto">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Rapports générés</h2>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="py-2 pr-3">Date</th><th class="py-2 pr-3">Type</th><th class="py-2 pr-3">Exploitation</th><th class="py-2 pr-3">Partage</th><th class="py-2 pr-3 text-right">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($rapports as $r)
                            @php $expire=$r->lien_expire_le; $valide=$expire&&$expire->isFuture(); $heures=$valide?max(1,(int)ceil(now()->diffInMinutes($expire)/60)):0; @endphp
                            <tr class="border-b border-gray-50">
                                <td class="py-3 pr-3 whitespace-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-3">{{ $r->type === 'dossier_credit' ? 'Dossier crédit' : 'Rapport standard' }}</td>
                                <td class="py-3 pr-3">{{ $r->exploitation->nom ?? '—' }}</td>
                                <td class="py-3 pr-3">
                                    @if ($valide && $r->lien_token)<span class="badge-vert">Valide ~{{ max(1,$heures) }} h</span><button type="button" class="text-xs text-agro-vert underline ml-2 js-copy" data-url="{{ route('rapports.partager', $r->lien_token) }}">Copier le lien</button>
                                    @elseif($expire && !$valide)<span class="badge-rouge">Expiré</span>
                                    @else<span class="text-gray-400">—</span>@endif
                                </td>
                                <td class="py-3 pr-3 text-right whitespace-nowrap"><a href="{{ route('rapports.telecharger', $r->id) }}" class="text-agro-vert font-medium">Télécharger</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-500">Aucun rapport pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.js-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var u = btn.getAttribute('data-url');
                if (navigator.clipboard && u) { navigator.clipboard.writeText(u).then(function () { btn.textContent = 'Copié !'; setTimeout(function () { btn.textContent = 'Copier le lien'; }, 2000); }); }
            });
        });
    </script>
@endif

@endsection
