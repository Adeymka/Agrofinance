<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

/**
 * DashboardController — Tableau de bord Web.
 *
 * Orchestre la requete HTTP et delogue la logique metier dans DashboardService (#15).
 * Supporte le multi-exploitation via ?exploitation_id=X (#1).
 */
class DashboardController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service,
        private AbonnementService $abonnementService,
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $uid  = (int) $user->id;

        // Multi-exploitation : charge toutes les exploitations (#1)
        $exploitations = Exploitation::where('user_id', $uid)
            ->orderBy('id')
            ->get(['id', 'nom']);

        if ($exploitations->isEmpty()) {
            return redirect()->route('exploitations.create')
                ->with('info', "Créez d'abord votre exploitation pour accéder au tableau de bord.");
        }

        $exploitationIdQuery = (int) $request->query('exploitation_id', 0);
        $exploitation = $exploitations->firstWhere('id', $exploitationIdQuery)
            ?? $exploitations->first();

        $exploitation->load(['activitesActives' => fn ($q) => $q->with('transactions')]);

        $dateDebutHistorique = $this->abonnementService->dateDebutHistorique($user)?->toDateString();

        $resultats = $this->service->calculerExploitation($exploitation->id, $dateDebutHistorique);
        $consolide = $resultats['consolide'];

        $recettes = $consolide['PB'] ?? 0;
        $depenses = $consolide['CT'] ?? 0;
        $marge    = $consolide['MB'] ?? 0;
        $rf       = $consolide['RF'] ?? 0;
        $statut   = $consolide['statut'] ?? 'rouge';

        $activiteIds = $exploitation->activites()
            ->where('statut', Activite::STATUT_EN_COURS)
            ->pluck('id');

        $parActivite = $resultats['par_activite'] ?? [];

        // Logique metier deplacee dans DashboardService (#15)
        $heroActiviteId = $this->dashboardService->determinerHeroActiviteId(
            $activiteIds,
            $parActivite,
            $request->query('campagne')
        );

        $heroInd = ($heroActiviteId && isset($parActivite[$heroActiviteId]))
            ? $parActivite[$heroActiviteId]
            : null;

        $chartActiviteId = $heroActiviteId ?: ($parActivite !== [] ? (int) array_key_first($parActivite) : null);

        $activitesCards = $this->dashboardService->construireActivitesCards(
            $exploitation,
            $parActivite,
            $dateDebutHistorique
        );

        $premierActiviteId = $chartActiviteId
            ?? ($parActivite !== [] ? array_key_first($parActivite) : $exploitation->activitesActives->first()?->id);

        $alertesBudget        = $this->dashboardService->extraireAlertesBudget($activitesCards);
        $bannerBudgetCritique = collect($alertesBudget)->contains(fn ($c) => ($c['budget_pct'] ?? 0) >= 100);

        $dernieresTransactions = $this->dashboardService->dernieresTransactions($activiteIds, $dateDebutHistorique);

        $apiToken       = session('api_token');
        $infoAbonnement = $this->abonnementService->infos($user);

        return view('dashboard.index', [
            'user'                  => $user,
            'exploitation'          => $exploitation,
            'exploitations'         => $exploitations,
            'resultats'             => $resultats,
            'consolide'             => $consolide,
            'recettes'              => $recettes,
            'depenses'              => $depenses,
            'marge'                 => $marge,
            'rf'                    => $rf,
            'statut'                => $statut,
            'dernieresTransactions' => $dernieresTransactions,
            'activitesCards'        => $activitesCards,
            'premierActiviteId'     => $premierActiviteId,
            'parActivite'           => $parActivite,
            'apiToken'              => $apiToken,
            'infoAbonnement'        => $infoAbonnement,
            'nav'                   => 'dashboard',
            'heroActiviteId'        => $heroActiviteId,
            'heroInd'               => $heroInd,
            'chartActiviteId'       => $chartActiviteId,
            'alertesBudget'         => $alertesBudget,
            'bannerBudgetCritique'  => $bannerBudgetCritique,
        ]);
    }
}
