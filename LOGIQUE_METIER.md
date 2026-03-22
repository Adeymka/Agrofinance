# AgroFinance+ — Logique métier

Document de référence sur les **règles métier**, les **entités**, les **indicateurs financiers agricoles** et les **droits par abonnement**. Il reflète l’état du code au moment de la rédaction.

---

## 1. Vision métier

**AgroFinance+** est une application de **gestion financière agricole** : suivre les **recettes** et **dépenses** par **exploitation** et par **campagne / activité**, calculer des **indicateurs** (marge, rentabilité, statut feu tricolore), générer des **rapports PDF** et gérer des **abonnements** payants (FedaPay).

Public cible : exploitants, structures de conseil, coopératives (selon le plan).

---

## 2. Entités et relations

### 2.1 Utilisateur (`users`)

- Identité : **nom**, **prénom**, **téléphone** (unique, identifiant de connexion principal), **email** optionnel.
- Sécurité : **PIN** stocké en **hash** (`pin_hash`), jamais en clair.
- Profil agricole : **type d’exploitation** par défaut (`cultures_vivrieres`, `elevage`, `maraichage`, `transformation`, `mixte`), **département**, **commune**.
- Un utilisateur possède **plusieurs exploitations** (sous réserve des limites d’abonnement).
- **Abonnements** : historique de lignes dans `abonnements` ; un seul « actif » pertinent à la fois pour les règles (voir §5).

### 2.2 Exploitation (`exploitations`)

Unité de production rattachée à un utilisateur.

- **nom**, **type** (même enum que le type utilisateur : cultures, élevage, maraîchage, transformation, mixte).
- **localisation** (adresse / zone), **superficie** (optionnel), **description**.
- Contient des **activités** (campagnes) et des **rapports**.

### 2.3 Activité (`activites`)

Une **campagne** ou période de production au sein d’une exploitation.

- **nom**, **type** métier (`culture`, `elevage`, `transformation`) — distinct du type d’exploitation.
- **date_debut**, **date_fin** (optionnelle).
- **statut** : en production, les campagnes **en cours** (`en_cours`) sont celles qui entrent dans les **consolidations** et indicateurs ; les campagnes **terminées** (`termine`) ou **abandonnées** (`abandonne`) sont exclues des agrégations « actives ».
- **budget prévisionnel** (optionnel) : sert aux **alertes** (70 %, 90 %, 100 % des dépenses par rapport au budget).
- **description** (optionnel).

### 2.4 Transaction (`transactions`)

Mouvement financier rattaché à **une activité**.

- **type** : `recette` ou `depense`.
- **nature** (surtout pour les dépenses) : `fixe` ou `variable` — indispensable pour le calcul de **coûts totaux** et de **marge** (voir §4).
- **categorie** : clé métier (ex. `semences`, `vente_marche`) ; le référentiel des libellés et regroupements est porté par `App\Helpers\TransactionCategories` (communes + spécifiques par type d’exploitation + mixte).
- **montant**, **date_transaction**, **note**, **est_imprevue** (booléen).
- **synced** : indicateur de synchronisation (ex. mode hors ligne / mobile).
- **photo_justificatif** (optionnel).

### 2.5 Abonnement (`abonnements`)

Droit d’usage de l’application sur une période.

- **plan** : valeur stockée en base ; le **plan métier** utilisé pour les règles peut être **normalisé** (ex. facturation `mensuel` → `essentielle`, `annuel` → `pro`) via `AbonnementService`.
- **statut** : `actif`, `essai`, `expire`, `suspendu` (selon migrations / SGBD).
- **date_debut**, **date_fin** : période de validité.
- **montant** payé, **ref_fedapay** (référence paiement / mock).

### 2.6 Rapport (`rapports`)

PDF généré pour une **exploitation**, sur une **période**.

- **type** : `campagne`, `mensuel`, `annuel`, `dossier_credit` — les **droits d’accès** diffèrent : **campagne** (et types assimilés « standard ») vs **dossier crédit** (réservé aux plans supérieurs).
- **periode_debut**, **periode_fin**.
- **chemin_pdf** (stockage applicatif), **lien_token** + **lien_expire_le** pour **partage public** (lien sans authentification, durée limitée — ex. 72 h en conception).

---

## 3. Indicateurs financiers (service `FinancialIndicatorsService`)

Les calculs s’appliquent à une **activité** sur une période (par défaut : toutes les transactions de l’activité), puis se **consolident** au niveau **exploitation** en ne prenant que les activités **en cours**.

### 3.1 Notation

| Code | Signification métier |
|------|----------------------|
| **PB** | Produit brut (somme des **recettes**) |
| **CV** | Coûts variables (somme des **dépenses** de nature **variable**) |
| **CF** | Coûts fixes (somme des **dépenses** de nature **fixe**) |
| **CT** | Coût total = CV + CF |
| **CI** | Coûts d’intrants (sous-ensemble des dépenses dont la **catégorie** appartient à une liste prédéfinie : semences, engrais, vaccins, énergie transformation, etc.) |
| **VAB** | Valeur ajoutée brute = PB − CI |
| **MB** | Marge brute = PB − CV |
| **RNE** | Résultat net d’exploitation = PB − CT |
| **RF** | Rentabilité des frais = (RNE / CT) × 100 si CT > 0, sinon 0 |
| **SR** | Seuil de rentabilité (approche coûts semi-variables : lien entre CF, marge sur coûts variables et PB) |

### 3.2 Statut indicateurs (feu tricolore)

Pour une activité ou un consolidé, un **statut** couleur est calculé :

- **vert** : RNE > 0 et (pas de SR ou PB ≥ SR) ;
- **orange** : MB > 0 (mais pas les conditions du vert) ;
- **rouge** : sinon.

Le **dashboard API** peut aussi calculer un **statut global** à partir des totaux (MB, RNE).

### 3.3 Consolidation par exploitation

`calculerExploitation` :

- Charge les **activités actives** (`en_cours`) avec leurs transactions.
- Calcule les indicateurs **par activité**.
- **Consolidé** : somme des PB, CT, MB, RNE des activités actives ; **RF** global = f(RNE total, CT total) ; **statut** recalculé sur les totaux.

**Important** : les activités **terminées** ne sont **pas** incluses dans cette consolidation — seules les campagnes **en cours** alimentent le tableau de bord et les agrégats « métier live ».

### 3.4 Historique et plans gratuits

Pour les plans **gratuit** / sans abonnement valide, une **limite d’historique** peut s’appliquer (ex. **6 mois** en amont pour la consultation) — voir `AbonnementService::dateDebutHistorique`.

---

## 4. Catégories de transactions

- **Communes** à tous les types : charges (location terrain, main-d’œuvre, transport, carburant, etc.) et recettes (subventions, crédits agricoles).
- **Spécifiques** par type d’exploitation : cultures vivrières, élevage, maraîchage, transformation.
- **Mixte** : combinaison des listes pour faciliter la saisie multi-filières.

Les clés (`categorie`) sont utilisées pour les filtres, l’affichage et le calcul de **CI** (intrants).

---

## 5. Abonnements et droits métier (`AbonnementService`)

### 5.1 Plans métier normalisés

Après normalisation, les plans « logiques » sont notamment :

| Plan métier | Rôle |
|-------------|------|
| **gratuit** / **aucun** | Accès limité, historique restreint, pas de PDF avancé selon règles |
| **essentielle** | PDF « campagne » / rapports standard, une exploitation typiquement |
| **pro** | Comme Essentielle + **multi-exploitations** (plafond, ex. 5), **dossier crédit** |
| **cooperative** | Multi-exploitations **illimitées**, **dossier crédit** |

Les valeurs **brutes** en base peuvent encore refléter la **facturation** (`mensuel`, `annuel`) ; la **normalisation** aligne sur ces plans métier pour les contrôles.

### 5.2 Règles principales

- **Abonnement valide** : ligne avec statut `actif` ou `essai`, **date_fin ≥ aujourd’hui** (début de journée).
- **PDF** : génération / téléchargement des rapports type **campagne** (hors dossier crédit) → Essentielle **ou** supérieur ; **dossier crédit** → Pro **ou** Coopérative.
- **Nombre d’exploitations** : gratuit / essentielle souvent **1** ; pro **plafonné** ; coopérative **illimité** (en pratique, entier max).
- **Paiement** : montants (Essentielle 1 500, Pro 5 000, Coopérative 8 000 FCFA/mois) et durée de période **30 jours** par renouvellement pour les plans payants, définis dans `AbonnementService` et les contrôleurs d’abonnement.

### 5.3 Parcours sans abonnement actif

L’utilisateur connecté peut accéder à **abonnement**, **profil**, **déconnexion**, **callback / mock FedaPay** ; le reste des fonctionnalités « métier » (dashboard, exploitations, etc.) exige un **abonnement actif** (middleware `subscribed`).

---

## 6. Authentification métier

- **Inscription** : création du compte ; **OTP** par SMS (ou log en local).
- **Vérification OTP** : code à durée limitée, nombre de tentatives, blocage temporaire après échecs répétés.
- **PIN** : 4 chiffres, hashé ; connexion **téléphone + PIN**.
- **API** : jetons **Sanctum** ; la clé d’authentification côté guard peut être le **téléphone** — pour les **clés étrangères** (`user_id`), utiliser **`auth()->user()->id`** (identifiant numérique utilisateur).

---

## 7. OTP (règles)

- Durée de validité du code (ex. 10 minutes).
- Tentatives max puis blocage (ex. 15 minutes).
- En **local**, le code peut être **journalisé** pour les tests (`storage/logs/laravel.log`).

---

## 8. Rapports et partage

- Les PDF sont stockés **hors dossier public direct** (ex. `storage/app/rapports/`), servis par **contrôleur** (autorisation + abonnement).
- Un **lien de partage** permet l’accès **sans compte** avec **token** et **expiration**.

---

## 9. Fonds d’écran (règle produit)

- **Espace connecté desktop** : rotation d’**images locales** (`public/images/`) classées par thème (Agro, élevage, transformation), **jeu de 4 images par semaine** calendaire (ordre Agro, Agro, élevage, transformation).
- **Pages d’authentification** (inscription, connexion) : **image fixe** (Unsplash) dédiée, indépendante de la rotation hebdomadaire.

---

## 10. Glossaire rapide

| Terme | Sens dans le projet |
|-------|---------------------|
| **Campagne** | Activité agricole sur une période (`activites`) |
| **Feu tricolore** | Logique de statut vert / orange / rouge sur indicateurs |
| **MB** | Marge brute |
| **RNE** | Résultat net d’exploitation |
| **Mixte** | Exploitation ou utilisateur couvrant plusieurs filières |

---

*Ce document décrit la logique métier telle qu’implémentée ; toute évolution du code doit mettre à jour ce fichier.*
