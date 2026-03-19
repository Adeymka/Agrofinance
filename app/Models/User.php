<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'telephone', 'email', 'pin_hash',
        'type_exploitation', 'departement', 'commune'
    ];

    protected $hidden = ['pin_hash'];

    /**
     * Connexion par téléphone (Sanctum / guard).
     * Attention : Auth::id() renvoie donc le téléphone, pas users.id.
     * Pour les clés étrangères (user_id), utiliser auth()->user()->id.
     */
    public function getAuthIdentifierName(): string
    {
        return 'telephone';
    }

    public function exploitations()
    {
        return $this->hasMany(Exploitation::class);
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function abonnementActif()
    {
        return $this->hasOne(Abonnement::class)
            ->whereIn('statut', ['actif', 'essai'])
            ->where('date_fin', '>=', now())
            ->latest();
    }

    public function aUnAbonnementActif(): bool
    {
        return $this->abonnementActif()->exists();
    }

    public function verifierPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }
}

