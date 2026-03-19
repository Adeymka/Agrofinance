<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Log, Session};
use Illuminate\Support\Str;

class AbonnementController extends Controller
{
    /**
     * POST /api/abonnement/initier
     */
    public function initier(Request $request)
    {
        $request->validate([
            'plan'      => 'required|in:mensuel,annuel',
            'telephone' => 'required|string',
        ]);

        $montants = [
            'mensuel' => 1000,
            'annuel'  => 10000,
        ];

        $montant = $montants[$request->plan];
        $user    = auth()->user();

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

            return response()->json([
                'succes'  => true,
                'message' => 'Mode simulation (FEDAPAY_MOCK) : aucun appel FedaPay. Appelez POST /api/abonnement/finaliser-mock pour activer l’abonnement.',
                'data'    => [
                    'mock'             => true,
                    'transaction_id'   => $ref,
                    'montant'          => $montant,
                    'plan'             => $request->plan,
                    'url_paiement'     => null,
                    'finaliser_mock'   => 'POST '.rtrim(config('app.url'), '/').'/api/abonnement/finaliser-mock',
                ],
            ]);
        }

        if (empty(config('services.fedapay.secret_key'))) {
            return response()->json([
                'succes'  => false,
                'message' => 'Paiement non configuré : ajoutez FEDAPAY_SECRET_KEY dans .env, ou FEDAPAY_MOCK=true pour le développement sans API.',
            ], 503);
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transaction = Transaction::create([
                'description'  => "AgroFinance+ — Abonnement {$request->plan}",
                'amount'       => $montant,
                'currency'     => ['iso' => 'XOF'],
                'callback_url' => rtrim(config('app.url'), '/').'/api/abonnement/callback',
                'customer'     => [
                    'firstname' => $user->prenom,
                    'lastname'  => $user->nom,
                    'phone_number' => [
                        'number'  => $request->telephone,
                        'country' => 'bj',
                    ],
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

            return response()->json([
                'succes'  => true,
                'message' => "Transaction initiée. Redirigez l'utilisateur vers l'URL de paiement.",
                'data'    => [
                    'transaction_id' => $transaction->id,
                    'montant'        => $montant,
                    'plan'           => $request->plan,
                    'url_paiement'   => $token->url,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('FedaPay initier : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'succes'  => false,
                'message' => "Erreur lors de l'initiation du paiement : ".$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/abonnement/callback
     * Appel public après redirection FedaPay (pas de Bearer).
     */
    public function callback(Request $request)
    {
        if (empty(config('services.fedapay.secret_key'))) {
            return response()->json(['succes' => false, 'message' => 'FedaPay non configuré.'], 503);
        }

        try {
            FedaPay::setApiKey(config('services.fedapay.secret_key'));
            FedaPay::setEnvironment(config('services.fedapay.environment', 'sandbox'));

            $transactionId = $request->query('id')
                ?? $request->input('id')
                ?? session('fedapay_transaction_id');

            if (! $transactionId) {
                Log::warning('FedaPay callback sans id transaction.');

                return response()->json(['succes' => false, 'message' => 'Transaction inconnue.'], 422);
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
                Log::error("FedaPay callback : impossible de résoudre user pour TX {$transactionId}");

                return response()->json(['succes' => false, 'message' => 'Contexte de paiement perdu.'], 422);
            }

            if (in_array($transaction->status, ['approved', 'transferred'], true)) {

                if (Abonnement::where('ref_fedapay', (string) $transaction->id)->exists()) {
                    session()->forget([
                        'fedapay_transaction_id',
                        'fedapay_plan',
                        'fedapay_user_id',
                    ]);

                    return response()->json(['succes' => true, 'message' => 'Déjà traité.']);
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

                Log::info("Abonnement activé — user {$userId} — plan {$planChoisi}");
            }

            return response()->json(['succes' => true]);
        } catch (\Throwable $e) {
            Log::error('FedaPay callback : '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['succes' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/abonnement/finaliser-mock
     * Uniquement lorsque FEDAPAY_MOCK=true — simule un paiement réussi.
     */
    public function finaliserMock(Request $request)
    {
        if (! config('services.fedapay.mock')) {
            return response()->json([
                'succes'  => false,
                'message' => 'Non disponible : activez FEDAPAY_MOCK=true dans .env (dev uniquement).',
            ], 403);
        }

        $userId = auth()->user()->id;
        $pending = Cache::pull("fedapay_pending_mock:{$userId}");

        if (! $pending || (int) $pending['user_id'] !== (int) $userId) {
            return response()->json([
                'succes'  => false,
                'message' => 'Aucune initiation mock en attente — appelez d’abord POST /api/abonnement/initier.',
            ], 422);
        }

        $planChoisi = $pending['plan'];
        $montant    = $pending['montant'];
        $ref        = $pending['ref'] ?? 'mock_'.Str::uuid()->toString();

        if (Abonnement::where('ref_fedapay', $ref)->exists()) {
            return response()->json([
                'succes'  => true,
                'message' => 'Abonnement déjà enregistré pour cette session mock.',
            ]);
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

        Log::info("Abonnement mock activé — user {$userId} — plan {$planChoisi}");

        return response()->json([
            'succes'  => true,
            'message' => 'Abonnement activé (simulation).',
            'data'    => [
                'plan'   => $planChoisi,
                'statut' => 'actif',
            ],
        ]);
    }
}
