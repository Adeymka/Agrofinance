<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public const STATUT_VALIDATION_EN_ATTENTE = 'en_attente';

    public const STATUT_VALIDATION_VALIDEE = 'validee';

    protected $fillable = [
        'client_uuid', 'activite_id', 'type', 'nature', 'categorie',
        'intrant_production',
        'montant', 'date_transaction', 'note',
        'est_imprevue', 'synced', 'photo_justificatif',
        'statut_validation', 'validee_par_user_id', 'validee_le',
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
        'est_imprevue'         => 'boolean',
        'intrant_production'   => 'boolean',
        'synced'               => 'boolean',
        'validee_le'           => 'datetime',
    ];

    public function getHasJustificatifAttribute(): bool
    {
        return ! empty($this->attributes['photo_justificatif'] ?? null);
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validee_par_user_id');
    }
}

