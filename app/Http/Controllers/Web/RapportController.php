<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\HandlesPdfAbonnement;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Rapport;
use App\Services\AbonnementService;
use App\Services\FinancialIndicatorsService;
use App\Jobs\GenerateRapportPdfJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    use HandlesPdfAbonnement;

    public function __construct(
        private FinancialIndicatorsService $fsa,
        private AbonnementService $abonnementService
    ) {}

    public function index(Request $request)
    {
        $exploitation = Exploitation::where('user_id', (int) auth()->user()->id)
            ->with('activites')
            ->first();

        if (! $exploitation) {
            return redirect()->route('exploitations.create')
                ->with('info', 'Créez d’abord votre exploitation.');
        }

        $rapports = Rapport::where('exploitation_id', $exploitation->id)
            ->with('exploitation:id,nom')
            ->latest()
            ->get();

        $activites = $exploitation->activites()->where('statut', Activite::STATUT_EN_COURS)->get();

        $pre = $request->query('activite_id');
        $activitePreselect = null;
        if ($pre !== null && $pre !== '' && $activites->contains('id', (int) $pre)) {
            $activitePreselect = (int) $pre;
        }

        return view('rapports.index', compact('exploitation', 'rapports', 'activites', 'activitePreselect'));
    }

    public function generer(Request $request)
    {
        $request->validate([
            'activite_id' => 'required|integer|exists:activites,id',
            'type' => 'required|in:campagne,dossier_credit',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date|after_or_equal:periode_debut',
        ]);

        $refus = $this->refuseSiPasGenerationRapport(
            $this->abonnementService,
            $request,
            $request->type
        );
        if ($refus !== null) {
            return $refus;
        }

        $uid = (int) auth()->user()->id;

        $activite = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('exploitation')->findOrFail($request->activite_id);

        $debut = $request->periode_debut
            ?? ($activite->date_debut?->toDateString() ?? now()->startOfMonth()->toDateString());
        $fin = $request->periode_fin
            ?? ($activite->date_fin?->toDateString() ?? now()->toDateString());

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $activite->exploitation->id,
            'type' => $request->type,
            'periode_debut' => $debut,
            'periode_fin' => $fin,
            'chemin_pdf' => '',
            'lien_token' => $token,
            'lien_expire_le' => now()->addHours(72),
        ]);
        $job = new GenerateRapportPdfJob(
            $rapport->id,
            $activite->id,
            $request->type,
            $debut,
            $fin
        );

        if (app()->environment(['local', 'testing'])) {
            $job->handle($this->fsa);
        } else {
            dispatch($job->onQueue('rapports'));
        }

        return redirect()->route('rapports.index')
            ->with('success', 'Rapport PDF en cours de génération !');
    }

    public function telecharger(Request $request, int $id)
    {
        $uid = (int) auth()->user()->id;

        $rapport = Rapport::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->findOrFail($id);

        $refus = $this->refuseSiPasTelechargementRapport(
            $this->abonnementService,
            $request,
            (string) $rapport->type
        );
        if ($refus !== null) {
            return $refus;
        }

        if ($rapport->chemin_pdf === '') {
            return back()->withErrors(['pdf' => 'Rapport en cours de génération.']);
        }

        if (! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return back()->withErrors(['pdf' => 'Fichier introuvable.']);
        }

        $contenuStocke = Storage::disk('local')->get($rapport->chemin_pdf);

        try {
            $pdfBytes = Crypt::decryptString($contenuStocke);
        } catch (\Throwable) {
            // Compatibilité avec d'anciens rapports encore non chiffrés.
            $pdfBytes = $contenuStocke;
        }

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapport_'.$rapport->id.'.pdf"',
        ]);
    }

    public function partager(string $token)
    {
        $rapport = Rapport::where('lien_token', $token)->firstOrFail();

        // Expiration obligatoire : absence de date = lien invalide (pas d’accès illimité).
        if (! $rapport->lien_expire_le || now()->isAfter($rapport->lien_expire_le)) {
            return response()->view('rapports.expire', [], 410);
        }

        if ($rapport->chemin_pdf === '' || ! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            abort(404);
        }

        $contenuStocke = Storage::disk('local')->get($rapport->chemin_pdf);

        try {
            $contenu = Crypt::decryptString($contenuStocke);
        } catch (\Throwable) {
            // Compatibilité avec d'anciens rapports encore non chiffrés.
            $contenu = $contenuStocke;
        }

        return response($contenu, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport.pdf"',
        ]);
    }
}
