<?php

namespace App\Support;

/**
 * Arborescence des catégories transaction (Sprint 6).
 */
final class TransactionCategories
{
    /**
     * @return array{depenses: array<string, array<string, string>>, recettes: array<string, array<string, string>>}
     */
    public static function tree(): array
    {
        return [
            'depenses' => [
                'Intrants agricoles' => [
                    'semences'                 => '🌱 Semences / plants',
                    'engrais_mineraux'         => '🧪 Engrais minéraux (NPK, urée)',
                    'engrais_organiques'       => '🌿 Engrais organiques / compost',
                    'pesticides'               => '💊 Pesticides / herbicides',
                    'vaccins'                  => '💉 Vaccins vétérinaires',
                    'medicaments_veterinaires' => '🏥 Médicaments animaux',
                    'aliments_animaux'         => '🌾 Aliments animaux (provende)',
                ],
                'Main-d\'oeuvre' => [
                    'main_oeuvre'            => '👷 Main-d\'oeuvre (journaliers)',
                    'main_oeuvre_familiale'  => '👨‍👩‍👧 Main-d\'oeuvre familiale',
                ],
                'Équipements' => [
                    'achat_equipement'     => '🔧 Achat d\'équipement',
                    'location_equipement'  => '🏗️ Location équipement',
                    'reparation'           => '🔩 Entretien / réparation',
                    'carburant'            => '⛽ Carburant',
                ],
                'Post-récolte' => [
                    'emballages'               => '📦 Emballages / sacs',
                    'stockage'                 => '🏪 Stockage / entrepôt',
                    'transport'                => '🚛 Transport',
                    'energie_transformation'   => '🔥 Énergie transformation',
                ],
                'Charges fixes' => [
                    'location_terrain' => '🏞️ Location terrain',
                    'amortissement'    => '📉 Amortissement matériel',
                    'connexion'        => '💧 Eau SONEB / électricité SBEE',
                    'frais_financiers' => '🏦 Frais financiers',
                ],
                'Autre' => [
                    'autre_depense' => '📝 Autre dépense',
                ],
            ],
            'recettes' => [
                'Ventes' => [
                    'vente_marche'               => '🛒 Vente au marché',
                    'vente_bord_champ'           => '🌾 Vente bord champ',
                    'vente_cooperative'          => '🤝 Vente via coopérative',
                    'vente_animaux'              => '🐄 Vente d\'animaux',
                    'vente_produits_transformes' => '🪴 Vente produits transformés',
                ],
                'Autres recettes' => [
                    'subvention'      => '🏛️ Subvention / aide',
                    'credit_agricole' => '🏦 Crédit agricole reçu',
                    'autre_recette'   => '📝 Autre recette',
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function flatSlugs(): array
    {
        $slugs = [];
        foreach (self::tree() as $type => $groupes) {
            foreach ($groupes as $cats) {
                foreach ($cats as $slug => $_label) {
                    $slugs[] = $slug;
                }
            }
        }

        return array_values(array_unique($slugs));
    }

    /**
     * Slugs autorisés pour un type de transaction.
     *
     * @return list<string>
     */
    public static function slugsForType(string $type): array
    {
        $key = $type === 'depense' ? 'depenses' : 'recettes';
        $slugs = [];
        foreach (self::tree()[$key] as $cats) {
            foreach ($cats as $slug => $_label) {
                $slugs[] = $slug;
            }
        }

        return $slugs;
    }
}
