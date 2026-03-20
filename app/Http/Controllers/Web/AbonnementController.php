<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Cache, Log, Session};
use Illuminate\Support\Str;

class AbonnementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $abonnement = $user->abonnementActif()->first();

        return view('abonnement.index', compact('user', 'abonnement'));
    }

    public function initier(Request $request)
    {
        $request->validate([
            'plan'      => 'required|in:mensuel,annuel',
            'telephone' => 'required|string',
        ]);

        $montants = ['mensuel' => 1000, 'annuel' => 10000];
        $user     = Auth::user();
        $montant  = $montants[$request->plan];

        if (config('services.fedapay.mock')) {
            $ref = 'mock_'.Str::uuid()->toString();

            Cache::put(
                "fedapay_pending_mock:{$user->id}",
                [
                    'user_id'   => $user->id,
                    'plan'      => $request->plan,
                    'montant'   => $montant,
                    'telephone' => $request->telephone,
                    'ref'       => $ref,
                ],
                now()->addHours(24)
            );

            return redirect()->route('abonnement')
                ->with('info', 'Mode simulation : cliquez sur « Confirmer la simulation » pour activer l’abonnement.');
        }

        if (empty(config('services.fedapay.secret_key'))) {
            return back()->withErrors([
                'paiement' => 'Paiement non configuré : ajoutez FEDAPAY_SECRET_KEY dans .env, ou FEDAPAY_MOCK=true.',
            ]);
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transaction = Transaction::create([
                'description'  => "AgroFinance+ — Abonnement {$request->plan}",
                'amount'       => $montant,
                'currency'     => ['iso' => 'XOF'],
                'callback_url' => route('abonnement.callback'),
                'customer'     => [
                    'firstname'    => $user->prenom,
                    'lastname'     => $user->nom,
                    'phone_number' => ['number' => $request->telephone, 'country' => 'bj'],
                ],
                'include' => 'customer,currency',
            ]);

            $txId = (string) $transaction->id;

            Cache::put(
                "fedapay_pending:{$txId}",
                [
                    'user_id' => $user->id,
                    'plan'    => $request->plan,
                ],
                now()->addHours(48)
            );

            Session::put([
                'fedapay_transaction_id' => $txId,
                'fedapay_plan'           => $request->plan,
                'fedapay_user_id'        => $user->id,
            ]);

            $token = $transaction->generateToken();

            return redirect($token->url);
        } catch (\Throwable $e) {
            Log::error('FedaPay Web : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->withErrors(['paiement' => 'Erreur paiement : '.$e->getMessage()]);
        }
    }

    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return back()->withErrors(['paiement' => 'Mode mock désactivé.']);
        }

        $userId  = (int) Auth::user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== $userId) {
            return back()->withErrors(['paiement' => 'Aucune initiation mock en attente. Relancez « Souscrire ».']);
        }

        $planChoisi = $pending['plan'];
        $montant    = $pending['montant'];
        $ref        = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        if (Abonnement::where('ref_fedapay', $ref)->exists()) {
            return redirect()->route('abonnement')
                ->with('success', 'Abonnement déjà enregistré pour cette session mock.');
        }

        $duree = $planChoisi === 'annuel' ? 365 : 30;

        Abonnement::where('user_id', $userId)
            ->whereIn('statut', ['actif', 'essai'])
            ->update(['statut' => 'expire']);

        Abonnement::create([
            'user_id'     => $userId,
            'plan'        => $planChoisi,
            'statut'      => 'actif',
            'date_debut'  => now()->toDateString(),
            'date_fin'    => now()->addDays($duree)->toDateString(),
            'montant'     => $montant,
            'ref_fedapay' => $ref,
        ]);

        Log::info("Abonnement mock activé (web) — user {$userId} — plan {$planChoisi}");

        return redirect()->route('abonnement')
            ->with('success', "Abonnement {$planChoisi} activé (simulation) !");
    }

    public function callback(Request $request)
    {
        if (empty(config('services.fedapay.secret_key'))) {
            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'FedaPay non configuré.']);
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transactionId = $request->query('id')
                ?? $request->input('id')
                ?? session('fedapay_transaction_id');

            if (! $transactionId) {
                Log::warning('FedaPay callback Web sans id transaction.');

                return redirect()->route('abonnement')
                    ->withErrors(['paiement' => 'Transaction inconnue.']);
            }

            $transaction = Transaction::retrieve($transactionId);

            $pending = Cache::pull("fedapay_pending:{$transactionId}");
            if ($pending) {
                $planChoisi = $pending['plan'];
                $userId     = $pending['user_id'];
            } else {
                $planChoisi = session('fedapay_plan', 'mensuel');
                $userId     = session('fedapay_user_id');
            }

            if (! $userId) {
                Log::error("FedaPay callback Web : impossible de résoudre user pour TX {$transactionId}");

                return redirect()->route('abonnement')
                    ->withErrors(['paiement' => 'Contexte de paiement perdu.']);
            }

            if (in_array($transaction->status, ['approved', 'transferred'], true)) {

                if (Abonnement::where('ref_fedapay', (string) $transaction->id)->exists()) {
                    session()->forget([
                        'fedapay_transaction_id',
                        'fedapay_plan',
                        'fedapay_user_id',
                    ]);

                    return redirect()->route('abonnement')
                        ->with('success', 'Paiement déjà enregistré.');
                }

                $duree = $planChoisi === 'annuel' ? 365 : 30;

                Abonnement::where('user_id', $userId)
                    ->whereIn('statut', ['actif', 'essai'])
                    ->update(['statut' => 'expire']);

                Abonnement::create([
                    'user_id'     => $userId,
                    'plan'        => $planChoisi,
                    'statut'      => 'actif',
                    'date_debut'  => now()->toDateString(),
                    'date_fin'    => now()->addDays($duree)->toDateString(),
                    'montant'     => $transaction->amount ?? 0,
                    'ref_fedapay' => (string) $transaction->id,
                ]);

                session()->forget([
                    'fedapay_transaction_id',
                    'fedapay_plan',
                    'fedapay_user_id',
                ]);

                Log::info("Abonnement activé (web) — user {$userId} — plan {$planChoisi}");

                return redirect()->route('abonnement')
                    ->with('success', "Abonnement {$planChoisi} activé avec succès !");
            }

            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'Paiement non confirmé.']);
        } catch (\Throwable $e) {
            Log::error('FedaPay callback Web : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->route('abonnement')
                ->withErrors(['paiement' => 'Erreur lors de la confirmation.']);
        }
    }
}
