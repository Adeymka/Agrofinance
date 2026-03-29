<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\TransactionJustificatifService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TransactionJustificatifController extends Controller
{
    public function __construct(
        private TransactionJustificatifService $justificatifService
    ) {}

    /**
     * POST multipart : champ fichier attendu « justificatif ».
     */
    public function store(Request $request, int $id)
    {
        $request->validate([
            'justificatif' => 'required|file|max:'.TransactionJustificatifService::MAX_KB.'|mimes:jpeg,jpg,png,webp,pdf',
        ]);

        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        if ($transaction->activite->statut !== \App\Models\Activite::STATUT_EN_COURS) {
            return response()->json([
                'succes' => false,
                'message' => 'Cette transaction n’accepte plus de justificatif (campagne terminée ou abandonnée).',
            ], 422);
        }

        $path = $this->justificatifService->storeUploadedFile($transaction, $request->file('justificatif'));
        $transaction->update(['photo_justificatif' => $path]);

        return response()->json([
            'succes' => true,
            'message' => 'Justificatif enregistré.',
            'data' => [
                'has_justificatif' => true,
            ],
        ], 201);
    }

    /**
     * Téléchargement du fichier (propriétaire uniquement).
     */
    public function show(int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        if (empty($transaction->photo_justificatif)) {
            return response()->json([
                'succes' => false,
                'message' => 'Aucun justificatif pour cette transaction.',
            ], 404);
        }

        $path = $transaction->photo_justificatif;
        if (! Storage::disk('local')->exists($path)) {
            return response()->json([
                'succes' => false,
                'message' => 'Fichier introuvable.',
            ], 404);
        }

        return Storage::disk('local')->download($path, 'justificatif_'.$transaction->id);
    }

    public function destroy(int $id)
    {
        $transaction = Transaction::whereHas('activite.exploitation', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })->findOrFail($id);

        if ($transaction->activite->statut !== \App\Models\Activite::STATUT_EN_COURS) {
            return response()->json([
                'succes' => false,
                'message' => 'Modification impossible sur cette campagne.',
            ], 422);
        }

        $this->justificatifService->deleteStoredIfAny($transaction);
        $transaction->update(['photo_justificatif' => null]);

        return response()->json([
            'succes' => true,
            'message' => 'Justificatif supprimé.',
        ]);
    }
}
