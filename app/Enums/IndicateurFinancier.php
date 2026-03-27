<?php

namespace App\Enums;

/**
 * Indicateurs Financiers Agricoles (IFA) utilises dans FinancialIndicatorsService.
 *
 * Chaque cas enum correspond a la cle de tableau retournee par calculer() / calculerExploitation().
 * Utiliser ces constantes permet d'eviter les chaines magiques dispersees dans les vues et services.
 *
 * Ref. FAO / methode FSA-UAC (Farming System Analysis — Unite d'Analyse des Couts).
 */
enum IndicateurFinancier: string
{
    /** Produit Brut : total des recettes de vente de la periode. */
    case ProduitBrut = 'PB';

    /** Charges Variables : intrants consommes proportionnellement a la production. */
    case ChargesVariables = 'CV';

    /** Charges Fixes : couts independants du volume produit (amortissements, etc.). */
    case ChargesFixes = 'CF';

    /** Charges Totales : CV + CF. */
    case ChargesTotales = 'CT';

    /** Charges Imprevisibles : depenses non budgetees. */
    case ChargesImprevisibles = 'CI';

    /** Valeur Ajoutee Brute : PB − CV. */
    case ValeurAjouteeBrute = 'VAB';

    /** Marge Brute : PB − CV − CF = VAB − CF. */
    case MargeBrute = 'MB';

    /** Resultat Net d'Exploitation : MB − CI. */
    case ResultatNetExploitation = 'RNE';

    /** Rentabilite Financiere : (RNE / CT) × 100, en %. Null si CT = 0. */
    case RentabiliteFinanciere = 'RF';

    /** Seuil de Rentabilite : CT / marge sur cout variable. */
    case SeuilRentabilite = 'SR';

    /** Statut synthetique derive : 'vert' | 'orange' | 'rouge'. */
    case Statut = 'statut';

    /**
     * Libelle lisible pour l'affichage dans les vues et rapports PDF.
     */
    public function libelle(): string
    {
        return match ($this) {
            self::ProduitBrut           => 'Produit Brut',
            self::ChargesVariables      => 'Charges Variables',
            self::ChargesFixes          => 'Charges Fixes',
            self::ChargesTotales        => 'Charges Totales',
            self::ChargesImprevisibles  => 'Charges Imprevisibles',
            self::ValeurAjouteeBrute    => 'Valeur Ajoutee Brute',
            self::MargeBrute            => 'Marge Brute',
            self::ResultatNetExploitation => 'Resultat Net Exploitation',
            self::RentabiliteFinanciere => 'Rentabilite Financiere',
            self::SeuilRentabilite      => 'Seuil de Rentabilite',
            self::Statut                => 'Statut',
        };
    }
}
