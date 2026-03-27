<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActiviteController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    public function index()
    {
        $activites = Activite::pourUtilisateur(auth()->user()->id)
            ->with('exploitation:id,nom')
            ->latest()
            ->get();

        return response()->json([
            'succes' => true,
            'data'   => $activites,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'exploitation_id'    => 'required|integer|exists:exploitations,id',
            'nom'                => 'required|string|max:255',
            'type'               => 'required|string|max:100',
            'date_debut'         => 'required|date',
            'date_fin'           => 'nullable|date|after:date_debut',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'description'        => 'nullable|string',
        ]);

        // Securite : verifier que l'exploitation appartient a l'utilisateur
        $exploitation = Exploitation::where('user_id', auth()->user()->id)
            ->findOrFail($request->exploitation_id);

        $activite = Activite::create([
            'exploitation_id'    => $exploitation->id,
            'nom'                => $request->nom,
            'type'               => $request->type,
            'date_debut'         => $request->date_debut,
            'date_fin'           => $request->date_fin,
            'budget_previsionnel' => $request->budget_previsionnel,
            'description'        => $request->description,
            'statut'             => 'en_cours',
        ]);

        return response()->json([
            'succes'  => true,
            'message' => 'Activite creee avec succes.',
            'data'    => $activite,
        ], 201);
    }

    public function show(int $id)
    {
        $activite = Activite::pourUtilisateur(auth()->user()->id)
            ->with('transactions')
            ->findOrFail($id);

        $dateMin = $this->abonnementService->dateDebutHistorique(auth()->user())?->toDateString();

        $payload = $activite->toArray();
        if ($dateMin) {
            $payload['transactions'] = $activite->transactions
                ->filter(fn ($t) => (string) $t->date_transaction >= $dateMin)
                ->values()
                ->all();
        }

        return response()->json([
            'succes' => true,
            'data'   => array_merge($payload, [
                'alerte_budget' => $activite->alerteBudget($dateMin),
            ]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $activite = Activite::pourUtilisateur(auth()->user()->id)->findOrFail($id);

        $request->validate([
            'nom'                => 'sometimes|string|max:255',
            'type'               => 'sometimes|string|max:100',
            'date_debut'         => 'sometimes|date',
            'date_fin'           => 'nullable|date',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'description'        => 'nullable|string',
        ]);

        $activite->update($request->only([
            'nom', 'type', 'date_debut', 'date_fin',
            'budget_previsionnel', 'description',
        ]));

        return response()->json([
            'succes'  => true,
            'message' => 'Activite mise a jour.',
            'data'    => $activite,
        ]);
    }

    /**
     * #13 — lockForUpdate() pour empecher la double cloture en concurrence.
     */
    public function cloturer(int $id)
    {
        // Verification ownership avant le lock (evite un SELECT inutile dans la transaction)
        Activite::pourUtilisateur(auth()->user()->id)->findOrFail($id);

        $resultat = DB::transaction(function () use ($id) {
            $activite = Activite::lockForUpdate()->findOrFail($id);

            if ($activite->statut !== Activite::STATUT_EN_COURS) {
                return ['deja_cloturee' => true, 'activite' => $activite];
            }

            $activite->update([
                'statut'   => Activite::STATUT_TERMINE,
                'date_fin' => now()->toDateString(),
            ]);

            return ['deja_cloturee' => false, 'activite' => $activite];
        });

        if ($resultat['deja_cloturee']) {
            return response()->json([
                'succes'  => false,
                'message' => 'Seules les campagnes en cours peuvent etre cloturees.',
            ], 422);
        }

        return response()->json([
            'succes'  => true,
            'message' => 'Activite cloturee.',
            'data'    => $resultat['activite'],
        ]);
    }

    /**
     * #13 — lockForUpdate() applique aussi sur abandonner() pour coherence.
     */
    public function abandonner(int $id)
    {
        Activite::pourUtilisateur(auth()->user()->id)->findOrFail($id);

        $resultat = DB::transaction(function () use ($id) {
            $activite = Activite::lockForUpdate()->findOrFail($id);

            if ($activite->statut !== Activite::STATUT_EN_COURS) {
                return ['deja_terminee' => true, 'activite' => $activite];
            }

            $activite->update([
                'statut'   => Activite::STATUT_ABANDONNE,
                'date_fin' => $activite->date_fin ?? now()->toDateString(),
            ]);

            return ['deja_terminee' => false, 'activite' => $activite];
        });

        if ($resultat['deja_terminee']) {
            return response()->json([
                'succes'  => false,
                'message' => 'Seules les campagnes en cours peuvent etre marquees comme abandonnees.',
            ], 422);
        }

        return response()->json([
            'succes'  => true,
            'message' => 'Campagne marquee comme abandonnee.',
            'data'    => $resultat['activite'],
        ]);
    }
}
