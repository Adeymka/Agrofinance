<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use Illuminate\Support\Facades\Storage;

class PartageController extends Controller
{
    /**
     * GET /partage/{token}
     * Accès public — sans authentification.
     */
    public function __invoke(string $token)
    {
        $rapport = Rapport::where('lien_token', $token)->first();

        if (! $rapport) {
            return response()->json([
                'succes'  => false,
                'message' => 'Lien invalide.',
            ], 404);
        }

        if ($rapport->lien_expire_le && now()->isAfter($rapport->lien_expire_le)) {
            return response()->json([
                'succes'    => false,
                'message'   => "Ce lien a expiré. Demandez un nouveau rapport à l'exploitant.",
                'expire_le' => $rapport->lien_expire_le->format('d/m/Y à H:i'),
            ], 410);
        }

        if ($rapport->chemin_pdf === '' || ! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return response()->json([
                'succes'  => false,
                'message' => 'Fichier introuvable.',
            ], 404);
        }

        $contenu = Storage::disk('local')->get($rapport->chemin_pdf);

        return response($contenu, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport_agrofinance.pdf"',
        ]);
    }
}
