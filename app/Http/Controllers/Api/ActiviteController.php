<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Services\AbonnementService;
use Illuminate\Http\Request;

class ActiviteController extends Controller
{
    public function __construct(
        private AbonnementService $abonnementService
    ) {}

    public function index()
    {
        $activites = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->with('exploitation:id,nom')->latest()->get();

        return response()->json([
            'succes' => true,
            'data' => $activites,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'exploitation_id' => 'required|integer|exists:exploitations,id',
            'nom' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // Sécurité : vérifier que l'exploitation appartient à l'utilisateur
        $exploitation = Exploitation::where('user_id', auth()->user()->id)
            ->findOrFail($request->exploitation_id);

        $activite = Activite::create([
            'exploitation_id' => $exploitation->id,
            'nom' => $request->nom,
            'type' => $request->type,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'budget_previsionnel' => $request->budget_previsionnel,
            'description' => $request->description,
            'statut' => 'en_cours',
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Activité créée avec succès.',
            'data' => $activite,
        ], 201);
    }

    public function show(int $id)
    {
        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->with('transactions')->findOrFail($id);

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
            'data' => array_merge($payload, [
                'alerte_budget' => $activite->alerteBudget($dateMin),
            ]),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $request->validate([
            'nom' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:100',
            'date_debut' => 'sometimes|date',
            'date_fin' => 'nullable|date',
            'budget_previsionnel' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $activite->update($request->only([
            'nom', 'type', 'date_debut', 'date_fin',
            'budget_previsionnel', 'description',
        ]));

        return response()->json([
            'succes' => true,
            'message' => 'Activité mise à jour.',
            'data' => $activite,
        ]);
    }

    public function cloturer(int $id)
    {
        $activite = Activite::whereHas('exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        $activite->update([
            'statut' => 'termine',
            'date_fin' => now()->toDateString(),
        ]);

        return response()->json([
            'succes' => true,
            'message' => 'Activité clôturée.',
            'data' => $activite,
        ]);
    }
}
