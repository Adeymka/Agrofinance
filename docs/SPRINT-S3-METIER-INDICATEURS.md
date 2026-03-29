# Sprint S3 — Métier & indicateurs financiers agricoles

**Statut : terminé** (P0–P2 couverts dans le périmètre produit décrit ci-dessous ; pas de refonte parcours S5 ni a11y systématique S6).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`

Ce document est la **source de vérité** pour les changements **métier / indicateurs** livrés dans le dépôt et la **recette** associée.

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| Période des chiffres visible (D1) | **Oui** — `FinancialIndicatorsService::resumerPeriodeExploitation` ; bandeau dashboard web + mobile ; API `periode` + `message_plancher_abonnement` ; PDF avec plancher abonnement si applicable. |
| CI / VAB et saisie libre (D2) | **Oui** — colonne `intrant_production` ; question Oui/Non web + mobile + API ; `TransactionCategories::slugsChargesIntermediaires()` comme référence unique ; CI = intrants listés **ou** `intrant_production === true`. |
| Fixe / variable pédagogie (D3) | **Oui** — phrase courte sous le choix nature (web + mobile création). |
| Plusieurs campagnes (D4) | **Oui** — textes sous les totaux consolidés (nombre de campagnes en cours, renvoi au détail). |
| Seuil / statut consolidé (D5) | **Oui** — phrases explicatives dashboard (web + mobile) sur la synthèse vs détail campagne. |
| Données insuffisantes (D6) | **Oui** — booléen `donnees_indicatives` dans les calculs ; bandeaux dashboard ; mention PDF. |

---

## 2. Thèmes D1–D6 — état

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** | **Fait** | Libellé de période basé sur min/max des transactions (campagnes **en cours**, plancher abonnement appliqué dans les calculs). |
| **D2** | **Fait** | Migration + formulaires + API ; liste des slugs CI dans `TransactionCategories`. |
| **D3** | **Fait** | Aide fixe/variable sur création transaction. |
| **D4** | **Fait** | Rappel du nombre de campagnes actives et du détail par campagne. |
| **D5** | **Fait** | Notes sur le statut « global » vs campagne (pas de seuil fusionné au consolidé). |
| **D6** | **Fait** | Règles : &lt; 5 transactions, ou recettes sans dépenses, ou dépenses sans recettes → `donnees_indicatives`. |

---

## 3. Fichiers principaux

| Zone | Fichiers |
|------|----------|
| Moteur | `app/Services/FinancialIndicatorsService.php`, `app/Helpers/TransactionCategories.php` |
| Modèle / migration | `app/Models/Transaction.php`, `database/migrations/2026_03_28_120000_add_intrant_production_to_transactions_table.php` |
| Web | `app/Http/Controllers/Web/DashboardController.php`, `Web/TransactionController.php` |
| API | `app/Http/Controllers/Api/DashboardController.php`, `Api/TransactionController.php` |
| PDF | `app/Services/RapportService.php`, `resources/views/rapports/pdf/campagne.blade.php`, `dossier-credit.blade.php` |
| Vues | `resources/views/dashboard/index.blade.php`, `resources/views/transactions/create.blade.php`, `edit.blade.php` |
| Doc | `docs/API_CLIENT.md`, ce fichier |
| Tests | `tests/Feature/FinancialIndicatorsIntrantsTest.php` |

---

## 4. Recette manuelle (numérotée)

1. **Dashboard** — Vérifier la ligne de **période** (dates cohérentes avec vos saisies). Si abonnement limite l’historique, le message associé apparaît.
2. **Consolidé** — Avec **2+ campagnes en cours**, vérifier le texte sur le nombre de campagnes et le **détail par campagne** (cartes ou liste).
3. **Statut global** — Lire la note indiquant que la couleur du total est une **synthèse** (pas le même critère que le seuil par campagne).
4. **Nouvelle dépense** — Choisir une catégorie **non intrant** (ex. location de terrain) : le bloc **« sert la production »** apparaît ; enregistrer ; vérifier les indicateurs (CI) côté campagne.
5. **API** — `POST /api/v1/transactions` avec dépense `location_terrain` **sans** `intrant_production` → **422**. Avec `"intrant_production": true` → **201**.
6. **API Dashboard** — `GET /api/v1/dashboard` : présence de `periode`, `message_plancher_abonnement`, `consolide_global.donnees_indicatives`.
7. **PDF** — Générer un rapport : période en tête, mention plancher si applicable, encart « peu de données » si critères D6.

**Résultats attendus** : pas d’erreur serveur ; textes en français simple ; cohérence des montants **CI** entre saisie (intrant) et tableau des indicateurs.

---

## 5. Hors périmètre (volontaire)

- Correspondance floue des libellés, file admin de validation des catégories, API d’autocomplétion référentiel avancée.
- Recalcul d’un seuil de rentabilité « fusionné » multi-campagnes.
- Refonte centre d’aide ou parcours S5 / UX S6 au-delà des libellés métier strictement nécessaires.

---

*Document figé pour clôture sprint S3 — à actualiser si le moteur d’indicateurs ou les slugs CI évoluent.*
