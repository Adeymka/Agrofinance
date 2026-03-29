<?php

/**
 * Montants facturés (FCFA / mois) pour les clés de paiement FedaPay.
 * Une seule source de vérité : {@see \App\Support\TarifsAbonnement} et {@see \App\Services\AbonnementService::montantFacturation}.
 */
return [
    'fcfa' => [
        'mensuel' => 5_000,
        'annuel' => 10_000,
        'cooperative' => 16_000,
    ],
];
