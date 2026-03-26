<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class VerificationOtpController extends Controller
{
    public function __invoke(Request $request, OtpService $otp)
    {
        // Rate-limit brute-force OTP avant vérification.
        $telephoneNettoye = preg_replace('/[^0-9]/', '', (string) $request->telephone);
        $rateKey = 'api_otp_verify:' . $telephoneNettoye . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            $secondes = RateLimiter::availableIn($rateKey);

            return response()->json([
                'succes' => false,
                'message' => "Trop de tentatives OTP. Réessayez dans {$secondes} secondes.",
            ], 429);
        }

        $request->validate([
            'telephone' => 'required|string',
            'code'      => 'required|string|size:6',
        ]);

        RateLimiter::hit($rateKey, 10 * 60);

        $resultat = $otp->verifier($request->telephone, $request->code);

        $response = [
            'succes'  => $resultat['succes'],
            'message' => $resultat['message'],
        ];

        if ($resultat['succes']) {
            RateLimiter::clear($rateKey);
            $response['pin_creation_token'] = $otp->creerTokenCreationPin($request->telephone);
        }

        return response()->json($response, $resultat['succes'] ? 200 : 422);
    }

    public function renvoyer(Request $request, OtpService $otp)
    {
        $request->validate([
            'telephone' => 'required|string|exists:users,telephone',
        ]);

        $telephoneNettoye = preg_replace('/[^0-9]/', '', (string) $request->telephone);
        $rateKey = 'api_otp_resend:'.$telephoneNettoye.':'.$request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $secondes = RateLimiter::availableIn($rateKey);

            return response()->json([
                'succes' => false,
                'message' => "Trop de renvois OTP. Réessayez dans {$secondes} secondes.",
            ], 429);
        }

        $otp->genererEtEnvoyer($request->telephone);
        RateLimiter::hit($rateKey, 15 * 60);

        return response()->json([
            'succes'  => true,
            'message' => 'Nouveau code OTP envoyé.',
        ]);
    }
}

