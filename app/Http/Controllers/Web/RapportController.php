<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\HandlesPdfAbonnement;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\Exploitation;
use App\Models\Rapport;
use App\Services\AbonnementService;
use App\Services\RapportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    use HandlesPdfAbonnement;

    public function __construct(
        private AbonnementService $abonnementService,
        private RapportService $rapportService
    ) {}

    public function index(Request $request)
    {
        $uid = (int) auth()->user()->id;
        $exploitations = Exploitation::where('user_id', $uid)
            ->orderBy('nom')
            ->get();

        if ($exploitations->isEmpty()) {
            return redirect()->route('exploitations.create')
                ->with('info', "Créez d'abord votre exploitation.");
        }

        // Récupère l'exploitation sélectionnée ou la première par défaut
        $exploitation = null;
        $exploitationId = (int) $request->query('exploitation_id', 0);
        
        if ($exploitationId > 0) {
            $exploitation = $exploitations->firstWhere('id', $exploitationId);
        }
        
        if (! $exploitation) {
            $exploitation = $exploitations->first();
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

        $infoAbonnement = $this->abonnementService->infos(auth()->user());

        return view('rapports.index', compact('exploitation', 'exploitations', 'rapports', 'activites', 'activitePreselect', 'infoAbonnement'));
    }

    public function generer(Request $request)
    {
        $request->validate([
            'exploitation_id' => 'required|integer|exists:exploitations,id',
            'type' => 'required|in:standard,dossier_credit',
            'periode_scope' => 'nullable|in:all,custom',
            'periode_debut' => 'nullable|date',
            'periode_fin' => 'nullable|date',
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
        $user = auth()->user();

        // Vérifier que l'exploitation appartient à l'utilisateur
        $exploitation = Exploitation::where('user_id', $uid)
            ->findOrFail($request->exploitation_id);

        // LOG: Données du formulaire
        \Log::info('=== GENERER RAPPORT ===', [
            'user_id' => $uid,
            'exploitation_id' => $exploitation->id,
            'exploitation_nom' => $exploitation->nom,
            'type' => $request->type,
            'periode_scope' => $request->input('periode_scope'),
            'periode_debut_form' => $request->input('periode_debut'),
            'periode_fin_form' => $request->input('periode_fin'),
        ]);

        // Déterminer les dates : si scope='all', ignorer les dates personnalisées
        $periodeDebut = null;
        $periodeFin = null;
        if ($request->input('periode_scope') === 'custom') {
            $periodeDebut = $request->input('periode_debut');
            $periodeFin = $request->input('periode_fin');
        }
        // Sinon scope='all' ou null → passer null (illimité)

        // LOG: Dates finales après traitement
        \Log::info('Dates finales au service:', [
            'periode_debut_passed' => $periodeDebut,
            'periode_fin_passed' => $periodeFin,
        ]);

        $this->rapportService->creerEtDispatcherExploitation(
            $user,
            $exploitation,
            (string) $request->type,
            $periodeDebut,
            $periodeFin
        );

        return redirect()->route('rapports.index', ['exploitation_id' => $exploitation->id])
            ->with('success', 'Rapport PDF généré !');
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

        if (! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            return back()->withErrors(['pdf' => 'Fichier introuvable.']);
        }

        return Storage::disk('local')->download($rapport->chemin_pdf, "rapport_{$rapport->id}.pdf");
    }

    public function partager(string $token)
    {
        $rapport = Rapport::where('lien_token', $token)->firstOrFail();

        if ($rapport->lien_expire_le && now()->isAfter($rapport->lien_expire_le)) {
            return response()->view('rapports.expire', [], 410);
        }

        if ($rapport->chemin_pdf === '' || ! Storage::disk('local')->exists($rapport->chemin_pdf)) {
            abort(404);
        }

        $contenu = Storage::disk('local')->get($rapport->chemin_pdf);

        return response($contenu, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport.pdf"',
        ]);
    }
}
