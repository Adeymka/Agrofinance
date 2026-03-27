<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class OtpService
{
    private const EXPIRY = 10;
    private const MAX_TRIES = 5;
    private const LOCKOUT = 15;
    private const SMS_RETRY_ATTEMPTS = 3;

    /**
     * Genere un code OTP a 6 chiffres, le stocke en cache (10 min) et l'envoie par SMS.
     *
     * @param  string  $telephone  Numero au format +229XXXXXXXX
     * @return bool                true si le SMS a ete envoye (ou simule en local), false sinon
     */
    public function genererEtEnvoyer(string $telephone): bool
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = 'otp_' . preg_replace('/[^0-9]/', '', $telephone);

        Cache::put($key, [
            'code' => $code,
            'tentatives' => 0,
        ], now()->addMinutes(self::EXPIRY));

        return $this->envoyerSMS($telephone, $code);
    }

    /**
     * Verifie un code OTP fourni par l'utilisateur.
     *
     * Compte les tentatives echouees et bloque le numero apres MAX_TRIES (5).
     *
     * @param  string  $telephone  Numero au format +229XXXXXXXX
     * @param  string  $code       Code saisi par l'utilisateur
     * @return array{succes:bool, message:string}
     */
    public function verifier(string $telephone, string $code): array
    {
        $key = 'otp_' . preg_replace('/[^0-9]/', '', $telephone);
        $keyLock = 'otp_block_' . preg_replace('/[^0-9]/', '', $telephone);

        if (Cache::has($keyLock)) {
            return ['succes' => false, 'message' => 'Compte bloqué 15 minutes.'];
        }

        $data = Cache::get($key);
        if (!$data) {
            return ['succes' => false, 'message' => 'Code expiré. Demandez un nouveau code.'];
        }

        $data['tentatives']++;

        if ($data['tentatives'] >= self::MAX_TRIES) {
            Cache::forget($key);
            Cache::put($keyLock, true, now()->addMinutes(self::LOCKOUT));
            return ['succes' => false, 'message' => 'Trop de tentatives. Compte bloqué 15 min.'];
        }

        Cache::put($key, $data, now()->addMinutes(self::EXPIRY));

        if ($data['code'] !== $code) {
            $restantes = self::MAX_TRIES - $data['tentatives'];
            return ['succes' => false, 'message' => "{$restantes} tentative(s) restante(s)."];
        }

        Cache::forget($key);
        return ['succes' => true, 'message' => 'Code vérifié avec succès.'];
    }

    /**
     * Genere un token de creation de PIN a usage unique (valable 15 min).
     * Ce token doit etre transmis a POST /auth/creer-pin pour prouver que l'OTP a ete verifie.
     *
     * @param  string  $telephone  Numero normalise
     * @return string              Token hexadecimal 32 caracteres
     */
    public function creerTokenCreationPin(string $telephone): string
    {
        $telephoneNettoye = preg_replace('/[^0-9]/', '', $telephone);
        $token = bin2hex(random_bytes(16));
        $key = "pin_creation_token_{$telephoneNettoye}";
        Cache::put($key, $token, now()->addMinutes(15));

        return $token;
    }

    /**
     * Consomme (invalide) le token de creation de PIN.
     *
     * @param  string  $telephone
     * @param  string  $token      Token recu dans la reponse de verification OTP
     * @return bool                false si le token est invalide ou expire
     */
    public function consommerTokenCreationPin(string $telephone, string $token): bool
    {
        $telephoneNettoye = preg_replace('/[^0-9]/', '', $telephone);
        $key = "pin_creation_token_{$telephoneNettoye}";
        $storedToken = Cache::get($key);
        if (! $storedToken || ! hash_equals((string) $storedToken, $token)) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function envoyerSMS(string $telephone, string $code): bool
    {
        if (app()->environment('local') && env('OTP_DEBUG_LOG', false)) {
            Log::info("[OTP DEBUG] Tel: {$telephone} | Code: {$code}");
            return true;
        }

        $derniereErreur = null;
        for ($tentative = 0; $tentative < self::SMS_RETRY_ATTEMPTS; $tentative++) {
            try {
                $httpClient = new GuzzleClient([
                    'timeout' => (float) config('services.vonage.timeout_seconds', 15),
                    'connect_timeout' => (float) config('services.vonage.connect_timeout_seconds', 5),
                ]);

                $client = new Client(
                    new Basic(
                        (string) config('services.vonage.api_key'),
                        (string) config('services.vonage.api_secret')
                    ),
                    [],
                    $httpClient
                );

                $client->sms()->send(
                    new SMS(
                        $telephone,
                        (string) config('services.vonage.sms_from'),
                        "AgroFinance+ : votre code est {$code}. Valable 10 minutes."
                    )
                );

                return true;
            } catch (\Throwable $e) {
                $derniereErreur = $e;
                Log::warning('Vonage SMS: échec envoi', [
                    'tentative' => $tentative + 1,
                    'error_class' => $e::class,
                    'error_message' => $e->getMessage(),
                ]);

                if ($tentative < self::SMS_RETRY_ATTEMPTS - 1) {
                    $backoffSeconds = (int) pow(2, $tentative); // 1,2,4...
                    usleep($backoffSeconds * 1000000);
                }
            }
        }

        Log::error('Vonage SMS: abandon après tentatives', [
            'error_class' => $derniereErreur ? $derniereErreur::class : null,
            'error_message' => $derniereErreur?->getMessage(),
        ]);

        return false;
    }
}

