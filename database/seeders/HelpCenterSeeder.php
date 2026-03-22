<?php

namespace Database\Seeders;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Database\Seeders\Support\HelpArticleBody;
use Illuminate\Database\Seeder;

class HelpCenterSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nom' => 'Premiers pas',
                'slug' => 'premiers-pas',
                'icone' => '📋',
                'description' => 'Créer son compte, se connecter, configurer son exploitation',
                'ordre' => 1,
                'articles' => [
                    ['ordre' => 1, 'titre' => 'Comment créer mon compte AgroFinance+ ?', 'slug' => 'premiers-pas-creer-mon-compte', 'mots_cles' => 'compte,inscription,créer,nouveau,démarrer,téléphone'],
                    ['ordre' => 2, 'titre' => 'Comprendre le code OTP et comment le recevoir', 'slug' => 'premiers-pas-comprendre-otp', 'mots_cles' => 'otp,code,sms,vérification,téléphone,6 chiffres'],
                    ['ordre' => 3, 'titre' => 'Créer et gérer son PIN de connexion', 'slug' => 'premiers-pas-gerer-pin', 'mots_cles' => 'pin,4 chiffres,connexion,mot de passe,sécurité'],
                    ['ordre' => 4, 'titre' => 'Créer sa première exploitation agricole', 'slug' => 'premiers-pas-creer-exploitation', 'mots_cles' => 'exploitation,ferme,créer,configurer,première fois'],
                    ['ordre' => 5, 'titre' => "Choisir le bon type d'exploitation", 'slug' => 'premiers-pas-choisir-type-exploitation', 'mots_cles' => 'type,élevage,cultures,maraîchage,transformation,mixte'],
                ],
            ],
            [
                'nom' => 'Campagnes agricoles',
                'slug' => 'campagnes',
                'icone' => '🌱',
                'description' => 'Créer et gérer vos campagnes de production',
                'ordre' => 2,
                'articles' => [
                    ['ordre' => 1, 'titre' => "Qu'est-ce qu'une campagne agricole ?", 'slug' => 'campagnes-comprendre-campagne', 'mots_cles' => 'campagne,activité,période,production,saison'],
                    ['ordre' => 2, 'titre' => 'Créer une nouvelle campagne', 'slug' => 'campagnes-creer-campagne', 'mots_cles' => 'nouvelle campagne,créer,démarrer,nom,date'],
                    ['ordre' => 3, 'titre' => 'Définir un budget prévisionnel et les alertes', 'slug' => 'campagnes-budget-previsionnel', 'mots_cles' => 'budget,prévisionnel,alerte,70%,90%,100%,dépassement'],
                    ['ordre' => 4, 'titre' => 'Clôturer une campagne terminée', 'slug' => 'campagnes-cloturer-campagne', 'mots_cles' => 'clôturer,terminer,fermer,bilan,fin campagne'],
                    ['ordre' => 5, 'titre' => 'Comprendre les statuts : en cours, terminée, abandonnée', 'slug' => 'campagnes-statuts-campagne', 'mots_cles' => 'statut,en cours,terminé,abandonné,actif'],
                ],
            ],
            [
                'nom' => 'Saisie des transactions',
                'slug' => 'transactions',
                'icone' => '💰',
                'description' => 'Enregistrer vos dépenses et recettes facilement',
                'ordre' => 3,
                'articles' => [
                    ['ordre' => 1, 'titre' => 'Comment enregistrer une dépense ?', 'slug' => 'transactions-enregistrer-depense', 'mots_cles' => 'dépense,enregistrer,saisir,coût,achat,paiement'],
                    ['ordre' => 2, 'titre' => 'Comment enregistrer une recette ?', 'slug' => 'transactions-enregistrer-recette', 'mots_cles' => 'recette,vente,revenu,entrée,argent,saisir'],
                    ['ordre' => 3, 'titre' => 'Différence entre charge fixe et charge variable', 'slug' => 'transactions-fixe-vs-variable', 'mots_cles' => 'fixe,variable,charge,nature,loyer,semences,engrais,main-oeuvre'],
                    ['ordre' => 4, 'titre' => 'Choisir la bonne catégorie de transaction', 'slug' => 'transactions-categories-transactions', 'mots_cles' => 'catégorie,semences,engrais,transport,vente,marché,choisir'],
                    ['ordre' => 5, 'titre' => 'Saisir une transaction sans connexion internet', 'slug' => 'transactions-mode-hors-ligne', 'mots_cles' => 'hors ligne,offline,sans internet,réseau,synchronisation,pwa'],
                    ['ordre' => 6, 'titre' => 'Modifier ou supprimer une transaction', 'slug' => 'transactions-modifier-transaction', 'mots_cles' => 'modifier,supprimer,corriger,changer,transaction,erreur'],
                ],
            ],
            [
                'nom' => 'Indicateurs financiers',
                'slug' => 'indicateurs',
                'icone' => '📊',
                'description' => 'Comprendre et interpréter vos indicateurs financiers agricoles',
                'ordre' => 4,
                'articles' => [
                    ['ordre' => 1, 'titre' => 'Les 8 indicateurs financiers agricoles expliqués simplement', 'slug' => 'indicateurs-fsa-expliques', 'mots_cles' => 'indicateurs,financiers,agricoles,PB,CV,CF,MB,RNE,RF,SR,calcul'],
                    ['ordre' => 2, 'titre' => 'Comprendre le Produit Brut (PB)', 'slug' => 'indicateurs-produit-brut', 'mots_cles' => 'PB,produit brut,recettes,ventes,total'],
                    ['ordre' => 3, 'titre' => 'Comprendre la Marge Brute (MB)', 'slug' => 'indicateurs-marge-brute', 'mots_cles' => 'MB,marge brute,PB,CV,coûts variables,rentabilité'],
                    ['ordre' => 4, 'titre' => "Comprendre le Revenu Net d'Exploitation (RNE)", 'slug' => 'indicateurs-revenu-net', 'mots_cles' => 'RNE,revenu net,bénéfice,résultat,PB,CT'],
                    ['ordre' => 5, 'titre' => 'Lire le feu tricolore : vert, orange, rouge', 'slug' => 'indicateurs-feu-tricolore', 'mots_cles' => 'feu,vert,orange,rouge,statut,rentable,déficitaire,surveiller'],
                    ['ordre' => 6, 'titre' => "Comprendre le tableau de bord par type d'exploitation", 'slug' => 'indicateurs-tableau-de-bord-par-type', 'mots_cles' => 'tableau de bord,dashboard,élevage,cultures,maraîchage,type'],
                    ['ordre' => 7, 'titre' => 'Qu\'est-ce que le Seuil de Rentabilité (SR) ?', 'slug' => 'indicateurs-seuil-rentabilite', 'mots_cles' => 'SR,seuil,rentabilité,minimum,ventes,coûts fixes'],
                ],
            ],
            [
                'nom' => 'Rapports PDF',
                'slug' => 'rapports',
                'icone' => '📄',
                'description' => 'Générer et partager vos rapports financiers',
                'ordre' => 5,
                'articles' => [
                    ['ordre' => 1, 'titre' => 'Comment générer un rapport PDF de campagne ?', 'slug' => 'rapports-generer-rapport-pdf', 'mots_cles' => 'rapport,PDF,générer,créer,campagne,document'],
                    ['ordre' => 2, 'titre' => "Qu'est-ce qu'un dossier crédit ?", 'slug' => 'rapports-dossier-credit', 'mots_cles' => 'dossier crédit,microfinance,banque,prêt,financement,crédit'],
                    ['ordre' => 3, 'titre' => 'Partager un rapport avec son agent de microfinance', 'slug' => 'rapports-partager-rapport', 'mots_cles' => 'partager,WhatsApp,lien,microfinance,agent,envoyer'],
                    ['ordre' => 4, 'titre' => 'Pourquoi le lien de partage expire après 72h ?', 'slug' => 'rapports-lien-72h', 'mots_cles' => '72h,expiration,lien,sécurité,partage,valide'],
                ],
            ],
            [
                'nom' => 'Abonnements et paiement',
                'slug' => 'abonnements',
                'icone' => '💳',
                'description' => 'Plans, tarifs et paiement via Mobile Money',
                'ordre' => 6,
                'articles' => [
                    ['ordre' => 1, 'titre' => 'Comparer les 4 plans : Gratuit, Essentielle, Pro, Coopérative', 'slug' => 'abonnements-comparer-plans', 'mots_cles' => 'plans,gratuit,essentielle,pro,coopérative,tarif,comparer,différences'],
                    ['ordre' => 2, 'titre' => 'Payer avec MTN MoMo', 'slug' => 'abonnements-payer-mtn-momo', 'mots_cles' => 'MTN,MoMo,Mobile Money,paiement,payer,abonnement'],
                    ['ordre' => 3, 'titre' => 'Payer avec Moov Money', 'slug' => 'abonnements-payer-moov-money', 'mots_cles' => 'Moov,Money,Mobile Money,paiement,payer,abonnement'],
                    ['ordre' => 4, 'titre' => 'Que se passe-t-il après les 75 jours gratuits ?', 'slug' => 'abonnements-apres-essai-gratuit', 'mots_cles' => '75 jours,essai,gratuit,expiration,après,fin'],
                    ['ordre' => 5, 'titre' => 'Renouveler ou changer de plan', 'slug' => 'abonnements-renouveler-plan', 'mots_cles' => 'renouveler,changer,upgrade,plan,abonnement,passer'],
                ],
            ],
        ];

        $contenuDefaut = '<p>Cet article est en cours de rédaction. Revenez bientôt.</p>';

        foreach ($categories as $catData) {
            $cat = HelpCategory::updateOrCreate(
                ['slug' => $catData['slug']],
                [
                    'nom' => $catData['nom'],
                    'icone' => $catData['icone'],
                    'description' => $catData['description'],
                    'ordre' => $catData['ordre'],
                    'actif' => true,
                ]
            );

            foreach ($catData['articles'] as $artData) {
                $body = HelpArticleBody::forSlug($artData['slug'], $contenuDefaut);

                HelpArticle::updateOrCreate(
                    ['slug' => $artData['slug']],
                    [
                        'help_category_id' => $cat->id,
                        'titre' => $artData['titre'],
                        'resume' => $body['resume'],
                        'contenu' => $body['contenu'],
                        'mots_cles' => $artData['mots_cles'],
                        'ordre' => $artData['ordre'],
                        'actif' => true,
                    ]
                );
            }
        }
    }
}
