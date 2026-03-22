<?php

namespace App\Helpers;

/**
 * Catégories transaction : suggestions par type d'exploitation + charges communes.
 */
final class TransactionCategories
{
    /**
     * Catégories communes à tous les types.
     *
     * @return array{depenses: array<string, array<string, string>>, recettes: array<string, array<string, string>>}
     */
    public static function communes(): array
    {
        return [
            'depenses' => [
                'Charges communes' => [
                    'location_terrain' => '🏞️ Location terrain',
                    'main_oeuvre' => '👷 Main-d\'oeuvre',
                    'transport' => '🚛 Transport',
                    'carburant' => '⛽ Carburant',
                    'amortissement' => '📉 Amortissement matériel',
                    'connexion' => '💧 Eau / Électricité',
                    'frais_financiers' => '🏦 Frais financiers',
                ],
            ],
            'recettes' => [
                'Autres recettes' => [
                    'subvention' => '🏛️ Subvention / Aide',
                    'credit_agricole' => '🏦 Crédit agricole reçu',
                ],
            ],
        ];
    }

    /**
     * Catégories spécifiques par type d'exploitation (sans les communes).
     *
     * @return array{depenses: array<string, array<string, string>>, recettes: array<string, array<string, string>>}
     */
    public static function parType(string $type): array
    {
        $specifiques = [
            'cultures_vivrieres' => [
                'depenses' => [
                    'Intrants cultures' => [
                        'semences' => '🌱 Semences / Plants',
                        'engrais_mineraux' => '🧪 Engrais minéraux',
                        'engrais_organiques' => '🌿 Engrais organiques',
                        'pesticides' => '💊 Pesticides',
                        'herbicides' => '🌾 Herbicides',
                    ],
                ],
                'recettes' => [
                    'Ventes cultures' => [
                        'vente_marche' => '🛒 Vente au marché',
                        'vente_bord_champ' => '🌾 Vente bord champ',
                        'vente_cooperative' => '🤝 Vente coopérative',
                    ],
                ],
            ],
            'elevage' => [
                'depenses' => [
                    'Intrants élevage' => [
                        'aliments_animaux' => '🌾 Aliments animaux',
                        'vaccins' => '💉 Vaccins',
                        'medicaments_veterinaires' => '🏥 Médicaments vétérinaires',
                        'eau_abreuvement' => '💧 Eau abreuvement',
                    ],
                ],
                'recettes' => [
                    'Ventes élevage' => [
                        'vente_animaux' => '🐄 Vente d\'animaux',
                        'vente_lait' => '🥛 Vente lait',
                        'vente_oeufs' => '🥚 Vente œufs',
                        'vente_fumier' => '♻️ Vente fumier / compost',
                    ],
                ],
            ],
            'maraichage' => [
                'depenses' => [
                    'Intrants maraîchage' => [
                        'semences' => '🌱 Semences légumes',
                        'engrais_organiques' => '🌿 Compost / Engrais organiques',
                        'pesticides' => '💊 Pesticides / Fongicides',
                        'eau_abreuvement' => '💧 Eau d\'irrigation',
                        'emballages' => '📦 Emballages / Sacs',
                    ],
                ],
                'recettes' => [
                    'Ventes maraîchage' => [
                        'vente_marche' => '🛒 Vente au marché',
                        'vente_restaurant' => '🍽️ Vente restaurateurs',
                        'vente_cooperative' => '🤝 Vente coopérative',
                        'vente_bord_champ' => '🌾 Vente bord champ',
                    ],
                ],
            ],
            'transformation' => [
                'depenses' => [
                    'Intrants transformation' => [
                        'matieres_premieres' => '🪴 Matières premières',
                        'energie_transformation' => '🔥 Énergie / Gaz / Électricité',
                        'emballages' => '📦 Emballages / Bouteilles',
                        'produits_chimiques' => '🧴 Produits de transformation',
                    ],
                ],
                'recettes' => [
                    'Ventes transformation' => [
                        'vente_produits_transformes' => '🪴 Vente produits transformés',
                        'vente_cooperative' => '🤝 Vente coopérative',
                        'vente_export' => '✈️ Vente export',
                    ],
                ],
            ],
        ];

        if ($type === 'mixte') {
            $toutes = ['depenses' => [], 'recettes' => []];
            foreach ($specifiques as $cats) {
                foreach ($cats['depenses'] as $groupe => $items) {
                    $toutes['depenses'][$groupe] = $items;
                }
                foreach ($cats['recettes'] as $groupe => $items) {
                    $toutes['recettes'][$groupe] = $items;
                }
            }

            return $toutes;
        }

        return $specifiques[$type] ?? $specifiques['cultures_vivrieres'];
    }

    /**
     * Suggestions + communes fusionnées pour l’UI et la validation.
     *
     * @return array{depenses: array<string, array<string, string>>, recettes: array<string, array<string, string>>}
     */
    public static function getByType(string $type): array
    {
        $specifiques = self::parType($type);
        $communes = self::communes();

        return [
            'depenses' => array_merge(
                $specifiques['depenses'] ?? [],
                $communes['depenses']
            ),
            'recettes' => array_merge(
                $specifiques['recettes'] ?? [],
                $communes['recettes']
            ),
        ];
    }

    /**
     * Liste des slugs autorisés pour un type d’exploitation et un type de transaction.
     *
     * @param  'depense'|'recette'  $transactionType
     * @return list<string>
     */
    public static function flatSlugsForTransactionType(string $exploitationType, string $transactionType): array
    {
        $tree = self::getByType($exploitationType);
        $key = $transactionType === 'depense' ? 'depenses' : 'recettes';
        $slugs = [];
        foreach ($tree[$key] ?? [] as $items) {
            foreach ($items as $slug => $_label) {
                $slugs[] = $slug;
            }
        }

        return array_values(array_unique($slugs));
    }
}
