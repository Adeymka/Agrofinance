<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Activite, Exploitation, Rapport};
use App\Services\FinancialIndicatorsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RapportController extends Controller
{
    public function __construct(
        private FinancialIndicatorsService $fsa
    ) {}

    public function index()
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

        $activites = $exploitation->activites()->where('statut', \App\Models\Activite::STATUT_EN_COURS)->get();

        return view('rapports.index', compact('exploitation', 'rapports', 'activites'));
    }

    public function generer(Request $request)
    {
        $request->validate([
            'activite_id'   => 'required|integer|exists:activites,id',
            'type'          => 'required|in:campagne,dossier_credit',
            'periode_debut' => 'nullable|date',
            'periode_fin'   => 'nullable|date|after_or_equal:periode_debut',
        ]);

        $uid = (int) auth()->user()->id;

        $activite = Activite::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->with('exploitation')->findOrFail($request->activite_id);

        $exploitation = $activite->exploitation;
        $user         = auth()->user();

        $debut = $request->periode_debut
            ?? ($activite->date_debut?->toDateString() ?? now()->startOfMonth()->toDateString());
        $fin = $request->periode_fin
            ?? ($activite->date_fin?->toDateString() ?? now()->toDateString());

        $indicateurs = $this->fsa->calculer($activite->id, $debut, $fin);

        $transactions = $activite->transactions()
            ->whereBetween('date_transaction', [$debut, $fin])
            ->orderBy('date_transaction')
            ->get();

        $token = Str::random(40);

        $rapport = Rapport::create([
            'exploitation_id' => $exploitation->id,
            'type'            => $request->type,
            'periode_debut'   => $debut,
            'periode_fin'     => $fin,
            'chemin_pdf'      => '',
            'lien_token'      => $token,
            'lien_expire_le'  => now()->addHours(72),
        ]);

        $template = $request->type === 'dossier_credit'
            ? 'rapports.pdf.dossier-credit'
            : 'rapports.pdf.campagne';

        $pdf = Pdf::loadView($template, compact(
            'user', 'exploitation', 'activite',
            'rapport', 'indicateurs', 'transactions'
        ));

        $nomFichier = "rapport_{$rapport->id}_{$token}.pdf";
        $chemin     = 'rapports/'.$nomFichier;

        Storage::disk('local')->makeDirectory('rapports');
        Storage::disk('local')->put($chemin, $pdf->output());

        $rapport->update(['chemin_pdf' => $chemin]);

        return redirect()->route('rapports.index')
            ->with('success', 'Rapport PDF généré !');
    }

    public function telecharger(int $id)
    {
        $uid = (int) auth()->user()->id;

        $rapport = Rapport::whereHas('exploitation', function ($q) use ($uid) {
            $q->where('user_id', $uid);
        })->findOrFail($id);

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
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rapport.pdf"',
        ]);
    }
}
