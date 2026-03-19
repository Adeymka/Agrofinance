<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private const EXPIRY = 10;
    private const MAX_TRIES = 5;
    private const LOCKOUT = 15;

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

    private function envoyerSMS(string $telephone, string $code): bool
    {
        if (app()->environment(['local', 'testing'])) {
            Log::info("[OTP LOCAL] Tel: {$telephone} | Code: {$code}");
            return true;
        }

        try {
            $client = new \Vonage\Client(
                new \Vonage\Client\Credentials\Basic(
                    config('services.vonage.api_key'),
                    config('services.vonage.api_secret')
                )
            );
            $client->sms()->send(
                new \Vonage\SMS\Message\SMS(
                    $telephone,
                    config('services.vonage.sms_from'),
                    "AgroFinance+ : votre code est {$code}. Valable 10 minutes."
                )
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Vonage SMS : ' . $e->getMessage());
            return false;
        }
    }
}

