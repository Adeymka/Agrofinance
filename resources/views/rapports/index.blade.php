@extends($layout)
@section('title', 'Rapports PDF — AgroFinance+')
@section('page-title', 'Rapports PDF')

@section('content')

@if($platform === 'mobile')

@push('styles')
<style>
/* ── Page header ── */
.rpt-page-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: rgba(255,255,255,0.94);
    letter-spacing: -0.03em;
    margin-bottom: 4px;
}
.rpt-page-sub {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: rgba(255,255,255,0.30);
    margin-bottom: 20px;
}
/* ── Block card ── */
.rpt-block {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 16px;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
.rpt-block-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: rgba(255,255,255,0.75);
    letter-spacing: -0.02em;
    margin-bottom: 14px;
}
/* ── Form fields ── */
.rpt-label {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: rgba(255,255,255,0.32);
    margin-bottom: 6px;
}
.rpt-select, .rpt-input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 14px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: rgba(255,255,255,0.82);
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
.rpt-select:focus, .rpt-input:focus { border-color: rgba(74,222,128,0.35); }
.rpt-field { margin-bottom: 12px; }
/* ── Upsell card ── */
.rpt-upsell {
    background: rgba(74,222,128,0.05);
    border: 1px solid rgba(74,222,128,0.15);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
    margin-bottom: 16px;
}
.rpt-upsell-icon {
    font-size: 36px;
    margin-bottom: 12px;
}
.rpt-upsell-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: rgba(255,255,255,0.88);
    letter-spacing: -0.02em;
    margin-bottom: 8px;
}
.rpt-upsell-sub {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: rgba(255,255,255,0.38);
    line-height: 1.55;
    margin-bottom: 16px;
}
.rpt-upsell-btn {
    display: inline-block;
    background: #16a34a;
    color: white;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    border: 1px solid rgba(74,222,128,0.30);
}
/* ── Submit button ── */
.rpt-submit {
    width: 100%;
    background: #16a34a;
    color: white;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 700;
    padding: 15px;
    border-radius: 14px;
    border: 1px solid rgba(74,222,128,0.30);
    cursor: pointer;
    margin-top: 4px;
    transition: opacity 0.15s;
}
.rpt-submit:active { opacity: 0.80; }
/* ── Reports list ── */
.rpt-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.rpt-item:last-child { border-bottom: none; }
.rpt-item-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(74,222,128,0.10);
    border: 1px solid rgba(74,222,128,0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}
.rpt-item-body { flex: 1; min-width: 0; }
.rpt-item-type {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    color: rgba(255,255,255,0.85);
    letter-spacing: -0.01em;
}
.rpt-item-meta {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    color: rgba(255,255,255,0.30);
    margin-top: 1px;
}
.rpt-download-btn {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #4ade80;
    text-decoration: none;
    background: rgba(74,222,128,0.10);
    border: 1px solid rgba(74,222,128,0.20);
    border-radius: 10px;
    padding: 7px 12px;
    white-space: nowrap;
    flex-shrink: 0;
}
.rpt-copy-btn {
    font-size: 11px;
    color: rgba(74,222,128,0.65);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    text-decoration: underline;
    font-family: 'Inter', sans-serif;
}
.rpt-badge-vert {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(74,222,128,0.12);
    border: 1px solid rgba(74,222,128,0.22);
    color: #4ade80;
    white-space: nowrap;
}
.rpt-badge-rouge {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 999px;
    background: rgba(248,113,113,0.12);
    border: 1px solid rgba(248,113,113,0.22);
    color: #f87171;
    white-space: nowrap;
}
</style>
@endpush

<div style="padding: 20px 0 4px;">
    <h1 class="rpt-page-title">Rapports PDF</h1>
    <p class="rpt-page-sub">Génération et téléchargement de rapports</p>
</div>

{{-- ── Générer un rapport ── --}}
@if ($activites->isEmpty())
    <div class="rpt-upsell">
        <div class="rpt-upsell-icon">📋</div>
        <div class="rpt-upsell-title">Aucune campagne</div>
        <p class="rpt-upsell-sub">Créez d'abord une campagne agricole pour pouvoir générer un rapport PDF.</p>
        <a href="{{ route('activites.create') }}" class="rpt-upsell-btn">Créer une campagne</a>
    </div>
@elseif (! ($infoAbonnement['peut_pdf'] ?? false))
    <div class="rpt-upsell">
        <div class="rpt-upsell-icon">🔒</div>
        <div class="rpt-upsell-title">Fonctionnalité Premium</div>
        <p class="rpt-upsell-sub">La génération de rapports PDF nécessite le plan <strong style="color:rgba(255,255,255,0.75);">Essentielle</strong> ou supérieur.</p>
        <a href="{{ route('abonnement') }}" class="rpt-upsell-btn">Voir les plans →</a>
    </div>
@else
    <div class="rpt-block">
        <div class="rpt-block-title">Générer un nouveau rapport</div>
        <form method="POST" action="{{ route('rapports.generer') }}">
            @csrf
            <div class="rpt-field">
                <div class="rpt-label">Campagne</div>
                <select name="activite_id" required class="rpt-select">
                    @foreach ($activites as $a)
                        <option value="{{ $a->id }}" @selected(($activitePreselect ?? $activites->first()?->id) == $a->id)>{{ $a->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="rpt-field">
                <div class="rpt-label">Type de rapport</div>
                <select name="type" class="rpt-select">
                    <option value="campagne">Rapport de campagne</option>
                    @if ($infoAbonnement['peut_dossier'] ?? false)
                        <option value="dossier_credit">Dossier crédit</option>
                    @endif
                </select>
                @if (! ($infoAbonnement['peut_dossier'] ?? false))
                    <p style="font-family:'Inter',sans-serif; font-size:11px; color:rgba(255,255,255,0.25); margin-top:6px;">Le dossier crédit est réservé aux plans Pro / Coopérative.</p>
                @endif
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px;">
                <div>
                    <div class="rpt-label">Période début</div>
                    <input type="date" name="periode_debut" value="{{ old('periode_debut', now()->startOfMonth()->toDateString()) }}" class="rpt-input">
                </div>
                <div>
                    <div class="rpt-label">Période fin</div>
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
                        {{ $r->type === 'dossier_credit' ? 'Dossier crédit' : 'Campagne' }}
                        @if($r->exploitation)— {{ $r->exploitation->nom }}@endif
                    </div>
                    <div class="rpt-item-meta">
                        {{ $r->created_at->format('d/m/Y H:i') }}
                        @if($valide && $r->lien_token)
                            · <span class="rpt-badge-vert">Valide ~{{ $heures }}h</span>
                            <button type="button" class="rpt-copy-btn js-copy" data-url="{{ route('rapports.partager', $r->lien_token) }}" style="margin-left:4px;">Copier lien</button>
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
    <div style="text-align:center; padding:24px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.06); border-radius:20px;">
        <p style="font-family:'Inter',sans-serif; font-size:13px; color:rgba(255,255,255,0.28);">Aucun rapport généré pour le moment.</p>
    </div>
@endif

<script>
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

@else
{{-- ════ DESKTOP (original) ════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="card">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Générer un rapport</h2>
                @if ($activites->isEmpty())
                    <p class="text-sm text-gray-600">Créez une campagne en cours pour générer un PDF.</p>
                @elseif (! ($infoAbonnement['peut_pdf'] ?? false))
                    <p class="text-sm text-gray-600">La génération de rapports PDF nécessite le plan <strong>Essentielle</strong> ou supérieur. <a href="{{ route('abonnement') }}" class="text-agro-vert font-medium underline">Voir les plans</a>.</p>
                @else
                    <form method="POST" action="{{ route('rapports.generer') }}" class="space-y-4">
                        @csrf
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Campagne</label><select name="activite_id" required class="input-field">@foreach ($activites as $a)<option value="{{ $a->id }}" @selected(($activitePreselect ?? $activites->first()?->id) == $a->id)>{{ $a->nom }}</option>@endforeach</select></div>
                        <div><label class="block text-xs font-medium text-gray-600 mb-1">Type</label><select name="type" class="input-field"><option value="campagne">Campagne</option>@if ($infoAbonnement['peut_dossier'] ?? false)<option value="dossier_credit">Dossier crédit</option>@endif</select></div>
                        @if (! ($infoAbonnement['peut_dossier'] ?? false))<p class="text-xs text-gray-500">Le rapport <strong>dossier crédit</strong> (Pro / Coopérative) n'est pas inclus dans votre plan.</p>@endif
                        <div class="grid grid-cols-2 gap-3">
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
                                <td class="py-3 pr-3">{{ $r->type === 'dossier_credit' ? 'Dossier crédit' : 'Campagne' }}</td>
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
