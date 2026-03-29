<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\AbonnementService;
use App\Services\ActiviteStatutService;
use App\Services\FinancialIndicatorsService;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $service,
        private AbonnementService $abonnementService,
        private ActiviteStatutService $activiteStatutService
    ) {}

    public function index()
    {
        $exploitation = Exploitation::where('user_id', (int) auth()->user()->id)->first();

        if (! $exploitation) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation.');
        }

        $actives = $exploitation->activites()
            ->where('statut', Activite::STATUT_EN_COURS)
            ->orderByDesc('date_debut')
            ->get();

        $terminees = $exploitation->activites()
            ->where('statut', Activite::STATUT_TERMINE)
            ->orderByDesc('date_fin')
            ->get();

        $abandonnees = $exploitation->activites()
            ->where('statut', Activite::STATUT_ABANDONNE)
            ->orderByDesc('date_fin')
            ->get();

        $dateMin = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();

        $indicateursParActivite = [];
        foreach ($actives as $a) {
            $indicateursParActivite[$a->id] = $this->service->calculer($a->id, null, null, $dateMin);
        }

        $indicateursTerminees = [];
        foreach ($terminees as $a) {
            $indicateursTerminees[$a->id] = $this->service->calculer($a->id, null, null, $dateMin);
        }

        $indicateursAbandonnees = [];
        foreach ($abandonnees as $a) {
            $indicateursAbandonnees[$a->id] = $this->service->calculer($a->id, null, null, $dateMin);
        }

        return view('activites.index', compact(
            'exploitation',
            'actives',
            'terminees',
            'abandonnees',
            'indicateursParActivite',
            'indicateursTerminees',
            'indicateursAbandonnees'
        ));
    }

    public function create()
    {
        $exploitation = Exploitation::where('user_id', (int) auth()->user()->id)->first();

        if (! $exploitation) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation.');
        }

        return view('activites.create', compact('exploitation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:150',
            'type' => 'required|in:culture,elevage,transformation',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'budget_previsionnel' => 'nullable|numeric|min:0',
        ]);

        $exploitation = Exploitation::where('user_id', (int) auth()->user()->id)->firstOrFail();

        Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => $request->nom,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'budget_previsionnel' => $request->budget_previsionnel,
            'statut' => Activite::STATUT_EN_COURS,
        ]);

        return redirect()->route('activites.index')
            ->with('success', "Campagne « {$request->nom} » créée !");
    }

    public function show(int $id)
    {
        $activite = Activite::pourUtilisateur((int) auth()->user()->id)
            ->with('transactions')
            ->findOrFail($id);

        $dateMin = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();

        $indicateurs = $this->service->calculer($id, null, null, $dateMin);

        $txQuery = $activite->transactions()->orderByDesc('date_transaction');
        if ($dateMin) {
            $txQuery->where('date_transaction', '>=', $dateMin);
        }
        $transactions = $txQuery->paginate(20);

        $alerteBudget = null;
        if ($activite->budget_previsionnel && (float) $activite->budget_previsionnel > 0) {
            $ct = (float) ($indicateurs['CT'] ?? 0);
            $pourcent = ($ct / (float) $activite->budget_previsionnel) * 100;
            if ($pourcent >= 100) {
                $alerteBudget = ['niveau' => 'rouge', 'pourcent' => round($pourcent, 1)];
            } elseif ($pourcent >= 90) {
                $alerteBudget = ['niveau' => 'orange', 'pourcent' => round($pourcent, 1)];
            } elseif ($pourcent >= 70) {
                $alerteBudget = ['niveau' => 'jaune', 'pourcent' => round($pourcent, 1)];
            }
        }

        $sr = $indicateurs['SR'] ?? null;
        $pb = $indicateurs['PB'] ?? 0;
        $srAtteint = $sr !== null && $pb > 0 && $pb >= $sr;

        $infoAbonnement = $this->abonnementService->infos(auth()->user());

        return view('activites.show', compact(
            'activite',
            'indicateurs',
            'transactions',
            'alerteBudget',
            'srAtteint',
            'infoAbonnement'
        ));
    }

    public function cloturer(int $id)
    {
        $resultat = $this->activiteStatutService->cloturer($id, (int) auth()->user()->id);

        if (! $resultat['ok']) {
            if ($resultat['reason'] === 'not_found') {
                abort(404);
            }

            return redirect()->route('activites.index')
                ->with('error', $resultat['message']);
        }

        $nom = $resultat['activite']->nom;

        return redirect()->route('activites.index')
            ->with('success', "Campagne « {$nom} » clôturée.");
    }

    public function abandonner(int $id)
    {
        $resultat = $this->activiteStatutService->abandonner($id, (int) auth()->user()->id);

        if (! $resultat['ok']) {
            if ($resultat['reason'] === 'not_found') {
                abort(404);
            }

            return redirect()->route('activites.index')
                ->with('error', $resultat['message']);
        }

        $nom = $resultat['activite']->nom;

        return redirect()->route('activites.index')
            ->with('success', "Campagne « {$nom} » marquée comme abandonnée.");
    }
}
