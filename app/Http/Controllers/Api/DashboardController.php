<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service,
        private AbonnementService $abonnementService
    ) {}

    /**
     * GET /api/v1/dashboard
     */
    public function __invoke()
    {
        $user = Auth::user();

        $dateDebutHistorique = $this->abonnementService->dateDebutHistorique($user)?->toDateString();

        $exploitations = Exploitation::where('user_id', $user->id)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
            ->get();

        $indicateursParExploitation = [];
        foreach ($exploitations as $exploitation) {
            if ($exploitation->activitesActives->count() > 0) {
                $periode = $this->service->resumerPeriodeExploitation($exploitation, $dateDebutHistorique);
                $indicateursParExploitation[$exploitation->id] = array_merge(
                    ['nom' => $exploitation->nom, 'periode' => $periode],
                    $this->service->calculerExploitation($exploitation->id, $dateDebutHistorique)
                );
            }
        }

        $PBTotal = collect($indicateursParExploitation)->sum(fn ($item) => $item['consolide']['PB'] ?? 0);
        $MBTotal = collect($indicateursParExploitation)->sum(fn ($item) => $item['consolide']['MB'] ?? 0);
        $RNETotal = collect($indicateursParExploitation)->sum(fn ($item) => $item['consolide']['RNE'] ?? 0);
        $CTTotal = collect($indicateursParExploitation)->sum(fn ($item) => $item['consolide']['CT'] ?? 0);

        $nbTxGlobal = 0;
        $nbRecGlobal = 0;
        $nbDepGlobal = 0;
        foreach ($indicateursParExploitation as $bloc) {
            foreach ($bloc['par_activite'] ?? [] as $pa) {
                $nbTxGlobal += (int) ($pa['nb_transactions'] ?? 0);
                $nbRecGlobal += (int) ($pa['nb_recettes'] ?? 0);
                $nbDepGlobal += (int) ($pa['nb_depenses'] ?? 0);
            }
        }
        $donneesIndicativesGlobal = $nbTxGlobal < 5
            || ($nbRecGlobal > 0 && $nbDepGlobal === 0)
            || ($nbDepGlobal > 0 && $nbRecGlobal === 0);

        $consolideGlobal = [
            'PB' => round($PBTotal, 2),
            'MB' => round($MBTotal, 2),
            'RNE' => round($RNETotal, 2),
            'CT' => round($CTTotal, 2),
            'RF' => $CTTotal > 0 ? round(($RNETotal / $CTTotal) * 100, 2) : 0,
            'statut' => $this->determinerStatutGlobal($MBTotal, $RNETotal),
            'donnees_indicatives' => $donneesIndicativesGlobal,
            'note_statut_global' => 'Vue toutes exploitations : la couleur est une synthèse sur les totaux ; le détail par campagne peut afficher un seuil de rentabilité.',
        ];

        $dernieresTransactions = Transaction::whereHas('activite.exploitation', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->when($dateDebutHistorique, fn ($q) => $q->where('date_transaction', '>=', $dateDebutHistorique))
            ->with('activite:id,nom')
            ->latest('date_transaction')
            ->limit(10)
            ->get();

        $alertes = [];
        foreach ($exploitations as $exploitation) {
            foreach ($exploitation->activitesActives as $activite) {
                $alerte = $activite->alerteBudget($dateDebutHistorique);
                if ($alerte) {
                    $alertes[] = array_merge($alerte, [
                        'activite_id' => $activite->id,
                        'activite_nom' => $activite->nom,
                    ]);
                }
            }
        }

        $abonnement = $user->abonnementActif;

        $messagePlancher = $dateDebutHistorique
            ? 'Votre formule limite l’historique : aucune donnée avant le '.\Carbon\Carbon::parse($dateDebutHistorique)->format('d/m/Y').'.'
            : null;

        return response()->json([
            'succes' => true,
            'data' => [
                'user' => [
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                ],
                'message_plancher_abonnement' => $messagePlancher,
                'consolide_global' => $consolideGlobal,
                'indicateurs_par_exploitation' => $indicateursParExploitation,
                'dernieres_transactions' => $dernieresTransactions,
                'alertes_budget' => $alertes,
                'nb_exploitations' => $exploitations->count(),
                'abonnement' => $abonnement
                    ? ['plan' => $abonnement->plan, 'statut' => $abonnement->statut]
                    : ['plan' => 'aucun', 'statut' => 'inactif'],
            ],
        ]);
    }

    private function determinerStatutGlobal(float $MB, float $RNE): string
    {
        if ($RNE > 0) {
            return 'vert';
        }
        if ($MB > 0) {
            return 'orange';
        }

        return 'rouge';
    }
}
