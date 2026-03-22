<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service,
        private AbonnementService $abonnementService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $uid = (int) $user->id;

        $exploitation = Exploitation::where('user_id', $uid)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
            ->first();

        if (! $exploitation) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation pour accéder au tableau de bord.');
        }

        $dateDebutHistorique = $this->abonnementService->dateDebutHistorique($user)?->toDateString();

        $resultats = $this->service->calculerExploitation($exploitation->id, $dateDebutHistorique);
        $consolide = $resultats['consolide'];

        $recettes = $consolide['PB'] ?? 0;
        $depenses = $consolide['CT'] ?? 0;
        $marge = $consolide['MB'] ?? 0;
        $rf = $consolide['RF'] ?? 0;
        $statut = $consolide['statut'] ?? 'rouge';

        $activiteIds = $exploitation->activites()
            ->where('statut', Activite::STATUT_EN_COURS)
            ->pluck('id');

        $parActivite = $resultats['par_activite'] ?? [];
        $firstParActiviteId = $parActivite !== []
            ? (int) array_key_first($parActivite)
            : null;

        $campagneQuery = $request->query('campagne');
        $heroActiviteId = null;
        if ($campagneQuery !== null && $campagneQuery !== ''
            && $activiteIds->contains((int) $campagneQuery)) {
            $heroActiviteId = (int) $campagneQuery;
        } elseif ($firstParActiviteId) {
            $heroActiviteId = $firstParActiviteId;
        }

        $heroInd = ($heroActiviteId && isset($parActivite[$heroActiviteId]))
            ? $parActivite[$heroActiviteId]
            : null;

        $chartActiviteId = $heroActiviteId ?: $firstParActiviteId;

        $activitesCards = [];

        foreach ($exploitation->activitesActives as $activite) {
            $ind = $parActivite[$activite->id] ?? null;
            if (! $ind) {
                continue;
            }

            $txForStats = $activite->transactions;
            if ($dateDebutHistorique) {
                $txForStats = $txForStats->filter(
                    fn ($t) => (string) $t->date_transaction >= $dateDebutHistorique
                );
            }

            $lastTx = $txForStats->max('date_transaction');
            $daysSince = $lastTx
                ? now()->diffInDays(Carbon::parse($lastTx))
                : 999;

            $totalDep = $txForStats->where('type', 'depense')->sum('montant');
            $budget = $activite->budget_previsionnel;
            $pctBudget = ($budget && $budget > 0)
                ? min(100, round(($totalDep / $budget) * 100, 1))
                : null;

            $activitesCards[] = [
                'id' => $activite->id,
                'nom' => $activite->nom,
                'type' => $activite->type,
                'statut' => $activite->statut,
                'recettes' => $ind['PB'] ?? 0,
                'depenses' => $ind['CT'] ?? 0,
                'marge' => $ind['MB'] ?? 0,
                'statut_indicateurs' => $ind['statut'] ?? 'rouge',
                'budget_pct' => $pctBudget,
                'budget_prev' => $budget,
                'days_since' => $daysSince,
            ];
        }

        $premierActiviteId = $chartActiviteId
            ?? ($parActivite !== [] ? array_key_first($parActivite) : $exploitation->activitesActives->first()?->id);

        $alertesBudget = array_values(array_filter($activitesCards, function (array $c) {
            $pct = $c['budget_pct'] ?? null;
            $prev = $c['budget_prev'] ?? 0;

            return $prev > 0 && $pct !== null && $pct >= 85;
        }));
        usort($alertesBudget, fn (array $a, array $b) => ($b['budget_pct'] ?? 0) <=> ($a['budget_pct'] ?? 0));

        $bannerBudgetCritique = collect($alertesBudget)->contains(fn ($c) => ($c['budget_pct'] ?? 0) >= 100);

        $dernieresTransactions = Transaction::query()
            ->when($activiteIds->isNotEmpty(), fn ($q) => $q->whereIn('activite_id', $activiteIds))
            ->when($activiteIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($dateDebutHistorique, fn ($q) => $q->where('date_transaction', '>=', $dateDebutHistorique))
            ->with('activite:id,nom')
            ->orderByDesc('date_transaction')
            ->limit(20)
            ->get();

        $apiToken = session('api_token');
        $infoAbonnement = $this->abonnementService->infos($user);

        return view('dashboard.index', [
            'user' => $user,
            'exploitation' => $exploitation,
            'resultats' => $resultats,
            'consolide' => $consolide,
            'recettes' => $recettes,
            'depenses' => $depenses,
            'marge' => $marge,
            'rf' => $rf,
            'statut' => $statut,
            'dernieresTransactions' => $dernieresTransactions,
            'activitesCards' => $activitesCards,
            'premierActiviteId' => $premierActiviteId,
            'parActivite' => $parActivite,
            'apiToken' => $apiToken,
            'infoAbonnement' => $infoAbonnement,
            'nav' => 'dashboard',
            'heroActiviteId' => $heroActiviteId,
            'heroInd' => $heroInd,
            'chartActiviteId' => $chartActiviteId,
            'alertesBudget' => $alertesBudget,
            'bannerBudgetCritique' => $bannerBudgetCritique,
        ]);
    }
}
