<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Transaction;
use App\Services\AbonnementService;
use App\Services\DashboardService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

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
        $uid = (int) $user->id;

        $exploitationId = (int) $request->query('exploitation_id', 0);

        $exploitationsQuery = Exploitation::where('user_id', $uid)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')]);

        $exploitation = $exploitationId > 0
            ? (clone $exploitationsQuery)->where('id', $exploitationId)->first()
            : null;

        if (! $exploitation) {
            $exploitation = $exploitationsQuery->first();
        }

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

        $heroGraph = $this->dashboardService->resoudreHeroEtGraphique(
            $request->query('campagne'),
            $activiteIds,
            $parActivite,
            $exploitation
        );

        $activitesCards = $this->dashboardService->construireCartesActivites(
            $exploitation,
            $parActivite,
            $dateDebutHistorique
        );

        $alertes = $this->dashboardService->alertesDepuisCartes($activitesCards);
        $alertesBudget = $alertes['alertesBudget'];
        $bannerBudgetCritique = $alertes['bannerBudgetCritique'];

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
            'premierActiviteId' => $heroGraph['premierActiviteId'],
            'parActivite' => $parActivite,
            'apiToken' => $apiToken,
            'infoAbonnement' => $infoAbonnement,
            'nav' => 'dashboard',
            'heroActiviteId' => $heroGraph['heroActiviteId'],
            'heroInd' => $heroGraph['heroInd'],
            'chartActiviteId' => $heroGraph['chartActiviteId'],
            'alertesBudget' => $alertesBudget,
            'bannerBudgetCritique' => $bannerBudgetCritique,
        ]);
    }
}
