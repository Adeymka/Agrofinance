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

    /**
     * Exploitation ciblée : ?exploitation_id= (GET) ou défaut = plus petite id (comportement historique).
     */
    private function exploitationPourRequete(Request $request): Exploitation
    {
        $userId = (int) auth()->user()->id;
        $exploitationId = (int) ($request->input('exploitation_id') ?? $request->query('exploitation_id') ?? 0);

        if ($exploitationId > 0) {
            return Exploitation::query()
                ->where('user_id', $userId)
                ->findOrFail($exploitationId);
        }

        return Exploitation::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $userId = (int) auth()->user()->id;

        if (! Exploitation::query()->where('user_id', $userId)->exists()) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation.');
        }

        // Récupérer TOUTES les exploitations de l'utilisateur
        $exploitations = Exploitation::query()
            ->where('user_id', $userId)
            ->orderBy('nom')
            ->get();

        $exploitation = $this->exploitationPourRequete($request);

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
            'exploitations',
            'actives',
            'terminees',
            'abandonnees',
            'indicateursParActivite',
            'indicateursTerminees',
            'indicateursAbandonnees'
        ));
    }

    public function create(Request $request)
    {
        $userId = (int) auth()->user()->id;

        if (! Exploitation::query()->where('user_id', $userId)->exists()) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation.');
        }

        // Récupérer TOUTES les exploitations pour le sélecteur
        $exploitations = Exploitation::query()
            ->where('user_id', $userId)
            ->orderBy('nom')
            ->get();

        $exploitation = $this->exploitationPourRequete($request);

        return view('activites.create', compact('exploitation', 'exploitations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'exploitation_id' => 'required|integer',
            'nom' => 'required|string|max:150',
            'type' => 'required|in:culture,elevage,transformation',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'budget_previsionnel' => 'nullable|numeric|min:0',
        ]);

        $exploitation = Exploitation::query()
            ->where('user_id', (int) auth()->user()->id)
            ->findOrFail((int) $request->input('exploitation_id'));

        Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => $request->nom,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'budget_previsionnel' => $request->budget_previsionnel,
            'statut' => Activite::STATUT_EN_COURS,
        ]);

        return redirect()->route('activites.index', ['exploitation_id' => $exploitation->id])
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
            $expId = Activite::query()->pourUtilisateur((int) auth()->user()->id)->whereKey($id)->value('exploitation_id');

            return redirect()->route('activites.index', array_filter(['exploitation_id' => $expId]))
                ->with('error', $resultat['message']);
        }

        $nom = $resultat['activite']->nom;

        return redirect()->route('activites.index', ['exploitation_id' => $resultat['activite']->exploitation_id])
            ->with('success', "Campagne « {$nom} » clôturée.");
    }

    public function abandonner(int $id)
    {
        $resultat = $this->activiteStatutService->abandonner($id, (int) auth()->user()->id);

        if (! $resultat['ok']) {
            if ($resultat['reason'] === 'not_found') {
                abort(404);
            }
            $expId = Activite::query()->pourUtilisateur((int) auth()->user()->id)->whereKey($id)->value('exploitation_id');

            return redirect()->route('activites.index', array_filter(['exploitation_id' => $expId]))
                ->with('error', $resultat['message']);
        }

        $nom = $resultat['activite']->nom;

        return redirect()->route('activites.index', ['exploitation_id' => $resultat['activite']->exploitation_id])
            ->with('success', "Campagne « {$nom} » marquée comme abandonnée.");
    }
}
