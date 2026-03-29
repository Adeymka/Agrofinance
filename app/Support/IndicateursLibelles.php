<?php

namespace App\Support;

/**
 * Libellés français accessibles pour l'affichage (les clés API JSON restent PB, MB, RNE, etc.).
 */
final class IndicateursLibelles
{
    /** Libellés complets : cartes, PDF, tableaux détaillés. */
    public const LIBELLES = [
        'PB' => 'Total des ventes',
        'CV' => "Dépenses liées au volume d'activité",
        'CF' => 'Dépenses fixes',
        'CT' => 'Total des dépenses',
        'CI' => 'Achats pour produire (intrants)',
        'VAB' => 'Richesse après achats pour produire',
        'MB' => 'Reste avant charges fixes',
        'RNE' => 'Gain ou perte finale',
        'RF' => 'Rentabilité',
        'SR' => 'Ventes nécessaires pour équilibre',
    ];

    /** Libellés courts : grilles denses, mini-cartes (peuvent tenir sur 2 lignes). */
    public const LIBELLES_COURTS = [
        'PB' => 'Ventes',
        'CV' => 'Dép. volume',
        'CF' => 'Frais fixes',
        'CT' => 'Dépenses',
        'CI' => 'Pour produire',
        'VAB' => 'Après intrants',
        'MB' => 'Reste',
        'RNE' => 'Solde',
        'RF' => 'Rentabilité',
        'SR' => 'Équilibre',
    ];

    public static function label(string $code): string
    {
        return self::LIBELLES[$code] ?? $code;
    }

    public static function labelCourt(string $code): string
    {
        return self::LIBELLES_COURTS[$code] ?? self::label($code);
    }
}
