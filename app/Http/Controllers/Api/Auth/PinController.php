<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'telephone'        => 'required|string|exists:users,telephone',
            'pin'              => 'required|string|size:4|confirmed',
            'pin_confirmation' => 'required|string|size:4',
        ]);

        $user = User::where('telephone', $request->telephone)->firstOrFail();
        $user->update(['pin_hash' => Hash::make($request->pin)]);

        return response()->json([
            'succes'  => true,
            'message' => 'PIN créé avec succès. Vous pouvez maintenant vous connecter.',
        ]);
    }
}

