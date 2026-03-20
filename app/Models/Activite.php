<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
    /** Campagne en cours (équivalent « actif » métier). */
    public const STATUT_EN_COURS = 'en_cours';

    public const STATUT_TERMINE = 'termine';

    protected $fillable = [
        'exploitation_id', 'nom', 'type',
        'date_debut', 'date_fin', 'statut',
        'budget_previsionnel', 'description',
    ];

    protected $casts = [
        'date_debut'          => 'date',
        'date_fin'            => 'date',
        'budget_previsionnel' => 'decimal:2',
    ];

    public function exploitation()
    {
        return $this->belongsTo(Exploitation::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Calcul alerte budget (70%, 90%, 100%)
    public function alerteBudget(): ?array
    {
        if (!$this->budget_previsionnel || $this->budget_previsionnel <= 0) {
            return null;
        }

        $totalDepenses = $this->transactions()
            ->where('type', 'depense')
            ->sum('montant');

        $pourcentage = ($totalDepenses / $this->budget_previsionnel) * 100;

        if ($pourcentage >= 100) {
            return ['niveau' => 'danger', 'pourcentage' => round($pourcentage, 1),
                    'message' => 'Budget dépassé !'];
        }
        if ($pourcentage >= 90) {
            return ['niveau' => 'warning', 'pourcentage' => round($pourcentage, 1),
                    'message' => '90% du budget consommé.'];
        }
        if ($pourcentage >= 70) {
            return ['niveau' => 'info', 'pourcentage' => round($pourcentage, 1),
                    'message' => '70% du budget consommé.'];
        }

        return null;
    }
}

