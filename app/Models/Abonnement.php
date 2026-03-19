<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'user_id', 'plan', 'statut',
        'date_debut', 'date_fin', 'montant',
        'ref_fedapay',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
        'montant'    => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

