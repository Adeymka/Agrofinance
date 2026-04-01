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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $triExploitations = (string) $request->query('tri_exploitations', 'rne_desc');
        $periode = (string) $request->query('periode', 'all');
        $isCooperative = $this->abonnementService->estPlanCooperatif($user);
        $seuilAlerte = $isCooperative ? (float) $request->query('seuil_alerte', 85) : 85.0;
        $seuilCritique = $isCooperative ? (float) $request->query('seuil_critique', 100) : 100.0;
        $seuilAlerte = max(1.0, min(100.0, $seuilAlerte));
        $seuilCritique = max($seuilAlerte, min(200.0, $seuilCritique));

        $exploitations = Exploitation::where('user_id', $uid)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
            ->get();

        if ($exploitations->isEmpty()) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation pour accéder au tableau de bord.');
        }

        $exploitation = $exploitationId > 0
            ? $exploitations->firstWhere('id', $exploitationId)
            : null;

        if (! $exploitation) {
            $exploitation = $exploitations->first();
        }

        $dateDebutHistorique = $this->abonnementService->dateDebutHistorique($user)?->toDateString();
        $finPeriode = now()->toDateString();
        $debutPeriode = match ($periode) {
            '30j' => now()->subDays(30)->toDateString(),
            '90j' => now()->subDays(90)->toDateString(),
            '12m' => now()->subMonths(12)->toDateString(),
            default => null,
        };
        $libellePeriodeSelection = match ($periode) {
            '30j' => '30 derniers jours',
            '90j' => '90 derniers jours',
            '12m' => '12 derniers mois',
            default => 'Toute la période disponible',
        };

        $indicateursParExploitation = [];
        foreach ($exploitations as $exp) {
            $calcul = $this->service->calculerExploitation($exp->id, $dateDebutHistorique, $debutPeriode, $finPeriode);
            $indicateursParExploitation[$exp->id] = [
                'exploitation_id' => $exp->id,
                'nom' => $exp->nom,
                'consolide' => $calcul['consolide'] ?? [],
                'nb_campagnes_actives' => (int) ($calcul['consolide']['nb_campagnes_actives'] ?? 0),
            ];
        }

        $pbEntreprise = collect($indicateursParExploitation)->sum(fn ($bloc) => (float) ($bloc['consolide']['PB'] ?? 0));
        $ctEntreprise = collect($indicateursParExploitation)->sum(fn ($bloc) => (float) ($bloc['consolide']['CT'] ?? 0));
        $mbEntreprise = collect($indicateursParExploitation)->sum(fn ($bloc) => (float) ($bloc['consolide']['MB'] ?? 0));
        $rneEntreprise = collect($indicateursParExploitation)->sum(fn ($bloc) => (float) ($bloc['consolide']['RNE'] ?? 0));
        $rfEntreprise = $ctEntreprise > 0 ? round(($rneEntreprise / $ctEntreprise) * 100, 2) : 0;
        $statutEntreprise = $rneEntreprise > 0 ? 'vert' : ($mbEntreprise > 0 ? 'orange' : 'rouge');

        $entrepriseConsolide = [
            'PB' => round($pbEntreprise, 2),
            'CT' => round($ctEntreprise, 2),
            'MB' => round($mbEntreprise, 2),
            'RNE' => round($rneEntreprise, 2),
            'RF' => round($rfEntreprise, 2),
            'statut' => $statutEntreprise,
            'nb_exploitations' => $exploitations->count(),
            'nb_campagnes_actives' => (int) collect($indicateursParExploitation)->sum('nb_campagnes_actives'),
        ];

        $exploitationsResume = collect($indicateursParExploitation)
            ->map(function (array $bloc) use ($exploitation) {
                $consolide = $bloc['consolide'];

                return [
                    'id' => (int) $bloc['exploitation_id'],
                    'nom' => (string) $bloc['nom'],
                    'RNE' => (float) ($consolide['RNE'] ?? 0),
                    'MB' => (float) ($consolide['MB'] ?? 0),
                    'RF' => (float) ($consolide['RF'] ?? 0),
                    'statut' => (string) ($consolide['statut'] ?? 'rouge'),
                    'nb_campagnes_actives' => (int) ($bloc['nb_campagnes_actives'] ?? 0),
                    'active' => (int) $bloc['exploitation_id'] === (int) $exploitation->id,
                ];
            });

        $exploitationsResume = match ($triExploitations) {
            'rf_desc' => $exploitationsResume->sortByDesc('RF'),
            'mb_desc' => $exploitationsResume->sortByDesc('MB'),
            'nom_asc' => $exploitationsResume->sortBy('nom', SORT_NATURAL | SORT_FLAG_CASE),
            default => $exploitationsResume->sortByDesc('RNE'),
        };

        $exploitationsResume = $exploitationsResume->values()->all();

        $periodeTableauBord = $this->service->resumerPeriodeExploitation($exploitation, $dateDebutHistorique);
        $periodeTableauBord['libelle_periode'] = 'Filtre actif : '.$libellePeriodeSelection.'. '.$periodeTableauBord['libelle_periode'];
        $messagePlancherAbonnement = $dateDebutHistorique
            ? 'Votre formule limite l’historique : aucune donnée avant le '.\Carbon\Carbon::parse($dateDebutHistorique)->format('d/m/Y').'.'
            : null;

        $resultats = $this->service->calculerExploitation($exploitation->id, $dateDebutHistorique, $debutPeriode, $finPeriode);
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

        $alertes = $this->dashboardService->alertesDepuisCartes($activitesCards, $seuilAlerte, $seuilCritique);
        $alertesBudget = $alertes['alertesBudget'];
        $bannerBudgetCritique = $alertes['bannerBudgetCritique'];

        $dernieresTransactions = Transaction::query()
            ->when($activiteIds->isNotEmpty(), fn ($q) => $q->whereIn('activite_id', $activiteIds))
            ->when($activiteIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->when($dateDebutHistorique, fn ($q) => $q->where('date_transaction', '>=', $dateDebutHistorique))
            ->when($debutPeriode, fn ($q) => $q->where('date_transaction', '>=', $debutPeriode))
            ->where('date_transaction', '<=', $finPeriode)
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
            'periodeTableauBord' => $periodeTableauBord,
            'messagePlancherAbonnement' => $messagePlancherAbonnement,
            'entrepriseConsolide' => $entrepriseConsolide,
            'exploitationsResume' => $exploitationsResume,
            'triExploitations' => $triExploitations,
            'periodeSelection' => $periode,
            'seuilAlerte' => $seuilAlerte,
            'seuilCritique' => $seuilCritique,
            'isCooperative' => $isCooperative,
        ]);
    }

    public function exporterConsolideEntrepriseCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        if (! $this->abonnementService->estPlanCooperatif($user)) {
            abort(403, "Fonction réservée au plan Coopérative.");
        }
        $lignes = $this->buildConsolideEntrepriseLignes($user, (string) $request->query('periode', 'all'));

        $nomFichier = 'consolide_entreprise_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($lignes): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Excel-friendly UTF-8 CSV: BOM + explicit separator.
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=;\r\n");

            fputcsv($handle, ['Exploitation', 'Campagnes actives', 'PB', 'CT', 'MB', 'RNE', 'RF (%)', 'Statut'], ';');

            foreach ($lignes as $ligne) {
                fputcsv($handle, [
                    $ligne['exploitation'],
                    $ligne['campagnes_actives'],
                    number_format((float) $ligne['PB'], 2, '.', ''),
                    number_format((float) $ligne['CT'], 2, '.', ''),
                    number_format((float) $ligne['MB'], 2, '.', ''),
                    number_format((float) $ligne['RNE'], 2, '.', ''),
                    number_format((float) $ligne['RF'], 2, '.', ''),
                    $ligne['statut'],
                ], ';');
            }

            fclose($handle);
        }, $nomFichier, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildConsolideEntrepriseLignes($user, string $periode): array
    {
        $uid = (int) $user->id;
        $dateDebutHistorique = $this->abonnementService->dateDebutHistorique($user)?->toDateString();
        $finPeriode = now()->toDateString();
        $debutPeriode = match ($periode) {
            '30j' => now()->subDays(30)->toDateString(),
            '90j' => now()->subDays(90)->toDateString(),
            '12m' => now()->subMonths(12)->toDateString(),
            default => null,
        };

        $exploitations = Exploitation::where('user_id', $uid)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
            ->get();

        $lignes = [];
        foreach ($exploitations as $exp) {
            $calcul = $this->service->calculerExploitation($exp->id, $dateDebutHistorique, $debutPeriode, $finPeriode);
            $consolide = $calcul['consolide'] ?? [];
            $lignes[] = [
                'exploitation' => $exp->nom,
                'campagnes_actives' => (int) ($consolide['nb_campagnes_actives'] ?? 0),
                'PB' => (float) ($consolide['PB'] ?? 0),
                'CT' => (float) ($consolide['CT'] ?? 0),
                'MB' => (float) ($consolide['MB'] ?? 0),
                'RNE' => (float) ($consolide['RNE'] ?? 0),
                'RF' => (float) ($consolide['RF'] ?? 0),
                'statut' => (string) ($consolide['statut'] ?? 'rouge'),
            ];
        }

        return $lignes;
    }
}
