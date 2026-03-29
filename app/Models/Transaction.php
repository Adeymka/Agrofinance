<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'client_uuid', 'activite_id', 'type', 'nature', 'categorie',
        'montant', 'date_transaction', 'note',
        'est_imprevue', 'synced', 'photo_justificatif',
    ];

    protected $hidden = [
        'photo_justificatif',
    ];

    protected $appends = [
        'has_justificatif',
    ];

    protected $casts = [
        'montant'          => 'decimal:2',
        'date_transaction' => 'date',
        'est_imprevue'     => 'boolean',
        'synced'           => 'boolean',
    ];

    public function getHasJustificatifAttribute(): bool
    {
        return ! empty($this->attributes['photo_justificatif'] ?? null);
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }
}

