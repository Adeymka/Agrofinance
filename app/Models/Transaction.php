<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'activite_id', 'type', 'nature', 'categorie',
        'montant', 'date_transaction', 'note',
        'est_imprevue', 'synced',
    ];

    protected $casts = [
        'montant'          => 'decimal:2',
        'date_transaction' => 'date',
        'est_imprevue'     => 'boolean',
        'synced'           => 'boolean',
    ];

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }
}

