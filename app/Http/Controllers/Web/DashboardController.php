<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Activite, Exploitation, Transaction};
use App\Services\FinancialIndicatorsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $uid  = (int) $user->id;

        $exploitation = Exploitation::where('user_id', $uid)
            ->with(['activitesActives' => fn ($q) => $q->with('transactions')])
            ->first();

        if (! $exploitation) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation pour accéder au tableau de bord.');
        }

        $resultats = $this->service->calculerExploitation($exploitation->id);
        $consolide = $resultats['consolide'];

        $recettes = $consolide['PB'] ?? 0;
        $depenses = $consolide['CT'] ?? 0;
        $marge    = $consolide['MB'] ?? 0;
        $rf       = $consolide['RF'] ?? 0;
        $statut   = $consolide['statut'] ?? 'rouge';

        $activiteIds = $exploitation->activites()
            ->where('statut', Activite::STATUT_EN_COURS)
            ->pluck('id');

        $dernieresTransactions = Transaction::query()
            ->when($activiteIds->isNotEmpty(), fn ($q) => $q->whereIn('activite_id', $activiteIds))
            ->when($activiteIds->isEmpty(), fn ($q) => $q->whereRaw('1 = 0'))
            ->with('activite:id,nom')
            ->orderByDesc('date_transaction')
            ->limit(5)
            ->get();

        $parActivite = $resultats['par_activite'] ?? [];
        $activitesCards = [];

        foreach ($exploitation->activitesActives as $activite) {
            $ind = $parActivite[$activite->id] ?? null;
            if (! $ind) {
                continue;
            }

            $lastTx = $activite->transactions->max('date_transaction');
            $daysSince = $lastTx
                ? now()->diffInDays(Carbon::parse($lastTx))
                : 999;

            $totalDep = $activite->transactions->where('type', 'depense')->sum('montant');
            $budget   = $activite->budget_previsionnel;
            $pctBudget = ($budget && $budget > 0)
                ? min(100, round(($totalDep / $budget) * 100, 1))
                : null;

            $activitesCards[] = [
                'id'          => $activite->id,
                'nom'         => $activite->nom,
                'type'        => $activite->type,
                'statut'      => $activite->statut,
                'recettes'    => $ind['PB'] ?? 0,
                'depenses'    => $ind['CT'] ?? 0,
                'marge'       => $ind['MB'] ?? 0,
                'statut_fsa'  => $ind['statut'] ?? 'rouge',
                'budget_pct'  => $pctBudget,
                'budget_prev' => $budget,
                'days_since'  => $daysSince,
            ];
        }

        $premierActiviteId = $parActivite !== []
            ? array_key_first($parActivite)
            : $exploitation->activitesActives->first()?->id;

        $apiToken = session('api_token');

        return view('dashboard.index', [
            'user'                  => $user,
            'exploitation'          => $exploitation,
            'resultats'               => $resultats,
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
            'nav'                   => 'dashboard',
        ]);
    }
}
