<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'telephone'        => 'required|string|exists:users,telephone',
            'pin'              => 'required|digits_between:4,6|confirmed',
            'pin_confirmation' => 'required|digits_between:4,6',
            'otp_token'        => 'required|string',
        ]);

        /** @var OtpService $otp */
        $otp = app(OtpService::class);
        if (! $otp->consommerTokenCreationPin($request->telephone, (string) $request->otp_token)) {
            return response()->json([
                'succes' => false,
                'message' => 'Token OTP invalide ou expiré. Vérifiez le code OTP puis réessayez.',
            ], 422);
        }

        $user = User::where('telephone', $request->telephone)->firstOrFail();
        $user->update(['pin_hash' => Hash::make($request->pin)]);

        return response()->json([
            'succes'  => true,
            'message' => 'PIN créé avec succès. Vous pouvez maintenant vous connecter.',
        ]);
    }
}

