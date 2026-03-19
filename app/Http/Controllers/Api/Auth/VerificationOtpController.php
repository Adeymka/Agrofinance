<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;

class VerificationOtpController extends Controller
{
    public function __invoke(Request $request, OtpService $otp)
    {
        $request->validate([
            'telephone' => 'required|string',
            'code'      => 'required|string|size:6',
        ]);

        $resultat = $otp->verifier($request->telephone, $request->code);

        return response()->json([
            'succes'  => $resultat['succes'],
            'message' => $resultat['message'],
        ], $resultat['succes'] ? 200 : 422);
    }

    public function renvoyer(Request $request, OtpService $otp)
    {
        $request->validate([
            'telephone' => 'required|string|exists:users,telephone',
        ]);

        $otp->genererEtEnvoyer($request->telephone);

        return response()->json([
            'succes'  => true,
            'message' => 'Nouveau code OTP envoyé.',
        ]);
    }
}

