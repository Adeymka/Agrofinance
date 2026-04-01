<?php

namespace App\Services;

use App\Models\CooperativeMember;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CooperativeInvitationService
{
    /**
     * @return array{sent:bool,channel:string,message:string}
     */
    public function sendInvitation(CooperativeMember $member): array
    {
        $token = (string) ($member->invitation_token ?? '');
        if ($token === '') {
            return ['sent' => false, 'channel' => 'none', 'message' => 'Token d’invitation manquant.'];
        }

        $acceptUrl = route('cooperative.invitation.show', ['token' => $token]);
        $ownerName = trim((string) ($member->cooperative?->owner?->prenom.' '.$member->cooperative?->owner?->nom));
        $message = "Invitation AgroFinance+ Coop. Rôle: {$member->role}. Acceptez ici: {$acceptUrl}";

        // En local/test, on journalise le lien complet pour recette rapide.
        if (app()->isLocal() || app()->environment('testing')) {
            Log::info('[COOP INVITATION LOCAL] '.$message);

            return ['sent' => true, 'channel' => 'local-log', 'message' => $acceptUrl];
        }

        $targetEmail = (string) ($member->user?->email ?? '');
        if ($targetEmail !== '') {
            try {
                Mail::raw($message, function ($mail) use ($targetEmail): void {
                    $mail->to($targetEmail)->subject('Invitation coopérative AgroFinance+');
                });

                return ['sent' => true, 'channel' => 'email', 'message' => 'Invitation envoyée par email.'];
            } catch (\Throwable $e) {
                Log::warning('Invitation coop email failed: '.$e->getMessage());
            }
        }

        $targetPhone = (string) ($member->invited_phone ?? $member->user?->telephone ?? '');
        if ($targetPhone !== '' && class_exists(\Vonage\Client::class)) {
            try {
                $apiKey = (string) config('services.vonage.api_key');
                $apiSecret = (string) config('services.vonage.api_secret');
                $smsFrom = (string) config('services.vonage.sms_from', 'AgroFinance+');

                if ($apiKey !== '' && $apiSecret !== '') {
                    $client = new \Vonage\Client(
                        new \Vonage\Client\Credentials\Basic($apiKey, $apiSecret)
                    );
                    $client->sms()->send(
                        new \Vonage\SMS\Message\SMS($targetPhone, $smsFrom, $message)
                    );

                    return ['sent' => true, 'channel' => 'sms', 'message' => 'Invitation envoyée par SMS.'];
                }
            } catch (\Throwable $e) {
                Log::warning('Invitation coop SMS failed: '.$e->getMessage());
            }
        }

        Log::warning('[COOP INVITATION FALLBACK] Aucun canal effectif.', [
            'cooperative_member_id' => $member->id,
            'cooperative_id' => $member->cooperative_id,
            'invitation_token_suffix' => $this->maskTokenSuffix($token),
            'owner' => $ownerName !== '' ? $ownerName : null,
        ]);

        return [
            'sent' => false,
            'channel' => 'fallback-log',
            'message' => 'Aucun envoi automatique (email/SMS non configuré).',
        ];
    }

    /**
     * Suffixe court pour corrélation support, sans exposer le secret dans les logs.
     */
    private function maskTokenSuffix(string $token): string
    {
        if ($token === '') {
            return '(vide)';
        }

        if (strlen($token) <= 8) {
            return '***';
        }

        return '…'.substr($token, -6);
    }
}
