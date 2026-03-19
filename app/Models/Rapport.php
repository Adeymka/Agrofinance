<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'exploitation_id', 'type', 'periode_debut',
        'periode_fin', 'chemin_pdf', 'lien_token', 'lien_expire_le',
    ];

    protected $casts = [
        'periode_debut'  => 'date',
        'periode_fin'    => 'date',
        'lien_expire_le' => 'datetime',
    ];

    public function exploitation()
    {
        return $this->belongsTo(Exploitation::class);
    }
}

