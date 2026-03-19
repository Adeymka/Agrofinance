<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .entete {
            border-bottom: 2px solid #1B5E20;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .titre-app {
            font-size: 20px;
            font-weight: bold;
            color: #1B5E20;
        }
        .bandeau-dossier {
            background: #E8F5E9;
            border: 1px solid #1B5E20;
            padding: 10px;
            margin-bottom: 16px;
            font-weight: bold;
            color: #1B5E20;
        }
        .sous-titre { font-size: 13px; color: #555; }
        h2 {
            color: #1B5E20;
            border-bottom: 1px solid #1B5E20;
            padding-bottom: 4px;
            margin-bottom: 12px;
            margin-top: 20px;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th {
            background-color: #1B5E20;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .kpi-cell {
            width: 25%;
            padding: 8px;
            background: #E8F5E9;
            border: 1px solid #C8E6C9;
            text-align: center;
        }
        .kpi-label  { font-size: 10px; color: #555; display: block; }
        .kpi-value  { font-size: 14px; font-weight: bold; color: #1B5E20; }
        .statut-vert   { color: #1B5E20; font-weight: bold; }
        .statut-orange { color: #F59E0B; font-weight: bold; }
        .statut-rouge  { color: #DC2626; font-weight: bold; }
        .pied-page {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 8px;
            font-size: 10px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="entete">
        <div class="titre-app">AgroFinance+</div>
        <div class="sous-titre">Dossier de crédit / synthèse financière — généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    <div class="bandeau-dossier">Document à destination des institutions de microfinance — données sur la période sélectionnée</div>

    <h2>Identité de l'exploitant</h2>
    <table>
        <tr><td><strong>Nom</strong></td><td>{{ $user->nom }} {{ $user->prenom }}</td></tr>
        <tr><td><strong>Téléphone</strong></td><td>{{ $user->telephone }}</td></tr>
        <tr><td><strong>Département</strong></td><td>{{ $user->departement ?? 'Non renseigné' }}</td></tr>
        <tr><td><strong>Commune</strong></td><td>{{ $user->commune ?? 'Non renseignée' }}</td></tr>
        <tr><td><strong>Type d'exploitation</strong></td><td>{{ $exploitation->type }}</td></tr>
    </table>

    <h2>Activité : {{ $activite->nom }}</h2>
    <table>
        <tr><td><strong>Exploitation</strong></td><td>{{ $exploitation->nom }}</td></tr>
        <tr><td><strong>Période analysée</strong></td>
            <td>{{ $rapport->periode_debut->format('d/m/Y') }} → {{ $rapport->periode_fin->format('d/m/Y') }}</td></tr>
        <tr><td><strong>Statut campagne</strong></td><td>{{ $activite->statut }}</td></tr>
    </table>

    <h2>Synthèse FSA-UAC</h2>
    <table>
        <tr>
            <td class="kpi-cell">
                <span class="kpi-label">Produit Brut (PB)</span>
                <span class="kpi-value">{{ number_format($indicateurs['PB'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Marge Brute (MB)</span>
                <span class="kpi-value">{{ number_format($indicateurs['MB'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Revenu Net (RNE)</span>
                <span class="kpi-value">{{ number_format($indicateurs['RNE'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Rendement Financier</span>
                <span class="kpi-value">{{ $indicateurs['RF'] }}%</span>
            </td>
        </tr>
        <tr>
            <td class="kpi-cell">
                <span class="kpi-label">Coûts Variables (CV)</span>
                <span class="kpi-value">{{ number_format($indicateurs['CV'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Coûts Fixes (CF)</span>
                <span class="kpi-value">{{ number_format($indicateurs['CF'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Coût Total (CT)</span>
                <span class="kpi-value">{{ number_format($indicateurs['CT'], 0, ',', ' ') }} FCFA</span>
            </td>
            <td class="kpi-cell">
                <span class="kpi-label">Seuil Rentabilité (SR)</span>
                <span class="kpi-value">
                    {{ $indicateurs['SR'] !== null && $indicateurs['SR'] !== '' ? number_format($indicateurs['SR'], 0, ',', ' ') . ' FCFA' : 'N/A' }}
                </span>
            </td>
        </tr>
    </table>

    <p>
        Appréciation :
        <span class="statut-{{ $indicateurs['statut'] }}">
            @if($indicateurs['statut'] === 'vert') RENTABLE
            @elseif($indicateurs['statut'] === 'orange') ATTENTION
            @else DÉFICITAIRE
            @endif
        </span>
    </p>

    <h2>Mouvements (transactions)</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th><th>Type</th><th>Nature</th>
                <th>Catégorie</th><th>Montant (FCFA)</th><th>Note</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $t)
            <tr>
                <td>{{ $t->date_transaction->format('d/m/Y') }}</td>
                <td>{{ strtoupper($t->type) }}</td>
                <td>{{ $t->nature ?? '-' }}</td>
                <td>{{ $t->categorie ?? '-' }}</td>
                <td style="text-align:right">{{ number_format((float) $t->montant, 0, ',', ' ') }}</td>
                <td>{{ $t->note ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pied-page">
        AgroFinance+ — Document généré automatiquement — {{ rtrim(config('app.url'), '/') }}/partage/{{ $rapport->lien_token }} (lien valable 72h)
    </div>

</body>
</html>
