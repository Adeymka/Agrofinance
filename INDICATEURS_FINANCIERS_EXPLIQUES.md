# 📊 Guide Complet des Indicateurs Financiers — AgroFinance+

**Document technique pour soutenance — Explique la logique, les formules et les algorithmes de chaque indicateur**

---

## 📑 Table des matières

1. [Introduction](#introduction)
2. [Indicateurs de Revenus et Coûts](#indicateurs-de-revenus-et-coûts)
3. [Indicateurs de Marges et Rentabilité](#indicateurs-de-marges-et-rentabilité)
4. [Seuil de Rentabilité (SR)](#seuil-de-rentabilité-sr)
5. [Statut Global (Vert/Orange/Rouge)](#statut-global)
6. [Consolidation par Exploitation](#consolidation-par-exploitation)
7. [Algorithme d'Évaluation des Données](#algorithme-dévaluation-des-données)
8. [Évolution Mensuelle](#évolution-mensuelle)
9. [Cas d'Usage Concrets](#cas-dusage-concrets)

---

## Introduction

AgroFinance+ calcule **10 indicateurs financiers** pour chaque **campagne (activité)** et une **agrégation consolidée** au niveau **exploitation (ferme)**. Ces indicateurs permettent à l'agriculteur de :

- 📈 **Comprendre sa rentabilité** réelle
- 💰 **Anticiper ses coûts** fixes et variables
- 🎯 **Piloter son budget** et détecter les alertes
- 📊 **Comparer ses activités** entre elles
- 🔮 **Prévoir ses besoins** de revenus pour équilibrer les coûts fixes

### Structure des données

**Entrée:** Transactions saisies par l'utilisateur :
- **Type:** `recette` ou `depense`
- **Nature** (pour dépenses seulement) : `variable` (consomme directement) ou `fixe` (costs structurels)
- **Catégorie:** Classification standardisée (`semences`, `engrais`, `main_d_oeuvre`, etc.)
- **Montant:** En FCFA
- **Date:** Pour filtrage par période

**Processus:** Agrégation par type et nature → calcul en cascade → déduction du statut

---

## Indicateurs de Revenus et Coûts

### 1️⃣ **PB (Produit Brut) — Chiffre d'affaires agricole**

#### Définition agricole
C'est le **total de toutes les recettes** : tout ce que l'agriculteur a vendu (récoltes, animaux, sous-produits, etc.).

#### Formule du code
```php
$PB = $recettes->sum('montant');
```

#### Décomposition
```
PB = Σ (montants des transactions où type='recette')
```

#### Exemple concret
Un agriculteur vend pendant sa campagne :
- 3 sacs de tomates à 5 000 FCFA = 15 000 FCFA
- 2 sacs de maïs à 8 000 FCFA = 16 000 FCFA
- Œufs vendus = 4 000 FCFA

**PB = 15 000 + 16 000 + 4 000 = 35 000 FCFA**

#### Fonctionnalité utilisateur
- Affiché au tableau de bord comme **« chiffre d'affaires »**
- Utilisé pour calculer les marges et le statut global
- **Clé pour évaluer si l'activité est viable**

#### Cas particuliers
- **Si PB = 0:** Pas de revenus → tous les coûts sont des déficits
- **Si PB > 0 mais très faible:** Alertera les autres indicateurs

---

### 2️⃣ **CV (Coûts Variables)**

#### Définition agricole
Les **coûts directement liés à la production** et qui changent selon la quantité produite :
- Semences, engrais, pesticides, carburant, main-d'œuvre directe, etc.
- **Si tu ne produis rien, ces coûts ne se font pas.**

#### Formule du code
```php
$CV = $depenses->where('nature', 'variable')->sum('montant');
```

#### Décomposition
```
CV = Σ (montants des transactions où type='depense' ET nature='variable')
```

#### Exemple concret
Même agriculteur, ses coûts variables :
- Semences = 8 000 FCFA
- Engrais = 5 000 FCFA
- Pesticides = 2 000 FCFA
- Main-d'œuvre (temporaire) = 4 000 FCFA

**CV = 8 000 + 5 000 + 2 000 + 4 000 = 19 000 FCFA**

#### Fonctionnalité utilisateur
- Affiché dans la **fiche détail de campagne**
- Comparé au PB via la **marge brute**
- **Signal d'alerte:** Si CV > PB, tu perds sur la production directe

#### Propriété importante
- **Proportionnel au volume:** Doubler la production → doubler CV (idéalement)
- **Contrôlable:** L'agriculteur peut réduire CV en optimisant les intrants

---

### 3️⃣ **CF (Coûts Fixes)**

#### Définition agricole
Les **coûts structurels** qui existent même si tu ne produis rien :
- Loyer/amortissement du terrain
- Salaires permanents
- Assurances, taxes foncières
- Amortissement du matériel
- **Ces coûts ne changent pas avec la quantité produite.**

#### Formule du code
```php
$CF = $depenses->where('nature', 'fixe')->sum('montant');
```

#### Décomposition
```
CF = Σ (montants des transactions où type='depense' ET nature='fixe')
```

#### Exemple concret
Même agriculteur, ses coûts fixes mensuels :
- Loyer du terrain = 3 000 FCFA/mois
- Salaire du gardien = 2 000 FCFA/mois
- Assurance matériel = 500 FCFA/mois

**CF = 3 000 + 2 000 + 500 = 5 500 FCFA/mois**

#### Fonctionnalité utilisateur
- **Indicateur critique pour le seuil de rentabilité**
- Affiché dans la fiche campagne
- Utilisé pour calculer le **SR (montant minimum à vendre)**

#### Cas particuliers
- **CF = 0:** Aucun coût fixe → SR = 0 (rentable immédiatement)
- **CF très élevé:** Nécessite beaucoup de recettes pour équilibrer

---

### 4️⃣ **CT (Coûts Totaux)**

#### Définition agricole
La **somme de tous les coûts** : tout ce qui a été dépensé pendant la campagne.

#### Formule du code
```php
$CT = $CV + $CF;
```

#### Décomposition
```
CT = Coûts Variables + Coûts Fixes
CT = 19 000 + 5 500 = 24 500 FCFA
```

#### Fonctionnalité utilisateur
- **Base pour calculer le profit (RNE)**
- Comparé au PB pour **évaluer la viabilité générale**

#### Logique
- Si PB > CT → Bénéfice ✅
- Si PB = CT → Point mort (0 bénéfice, 0 perte)
- Si PB < CT → Déficit ❌

---

### 5️⃣ **CI (Charges Intermédiaires)**

#### Définition agricole
Un sous-ensemble de coûts variables : les **intrants de production** (ce qui entre dans la production) :
- Semences, engrais, pesticides, aliments pour bétail, etc.
- **Vs.** autres dépenses variables comme le carburant, transport, salaire temporaire

#### Formule du code
```php
$ciSlugs = TransactionCategories::slugsChargesIntermediaires();
$CI = $depenses->filter(function ($d) use ($ciSlugs) {
    if (in_array($d->categorie, $ciSlugs, true)) {
        return true;
    }
    return $d->intrant_production === true;
})->sum('montant');
```

#### Décomposition
```
CI = Σ (transactions où categorie ∈ [semences, engrais, pesticides, ...] OU intrant_production=true)
```

#### Algorithme détaillé
1. **Étape 1:** Récupère liste des catégories "charges intermédiaires" depuis `TransactionCategories::slugsChargesIntermediaires()`
   - Exemple: `['semences', 'engrais', 'pesticides', 'aliments_betail', ...]`
2. **Étape 2:** Pour chaque dépense, vérifie :
   - Est-ce que la catégorie appartient à la liste? → Inclure
   - OU est-ce que `intrant_production` = true? → Inclure
3. **Étape 3:** Somme tous les montants qui satisfont l'une des conditions

#### Exemple concret
Parmi les CV = 19 000 FCFA :
- Semences = 8 000 FCFA ✅ (catégorie semences)
- Engrais = 5 000 FCFA ✅ (catégorie engrais)
- Pesticides = 2 000 FCFA ✅ (catégorie pesticides)
- Main-d'œuvre temporaire = 4 000 FCFA ❌ (pas intrant)

**CI = 8 000 + 5 000 + 2 000 = 15 000 FCFA**

#### Fonctionnalité utilisateur
- **VAB (Valeur Ajoutée Brute) = PB - CI**
- Mesure la **productivité des intrants** (qu'est-ce que l'agriculteur ajoute comme valeur au-delà des matières premières)
- Important en agriculture bio (faible CI, haute VAB = meilleur)

---

## Indicateurs de Marges et Rentabilité

### 6️⃣ **VAB (Valeur Ajoutée Brute)**

#### Définition agricole
La **création de valeur nette** après déduction des matières premières (intrants).

#### Formule du code
```php
$VAB = $PB - $CI;
```

#### Exemple concret
```
PB = 35 000 FCFA (total vendu)
CI = 15 000 FCFA (semences, engrais, pesticides)
VAB = 35 000 - 15 000 = 20 000 FCFA
```

L'agriculteur a **créé 20 000 FCFA de valeur** au-delà du coût des intrants.

#### Interprétation
- **VAB élevé → Bonne productivité**
- **VAB faible → Intrants trop chers ou ventes trop basses**
- **VAB négative → Signal d’alerte : vos achats d’intrants coûtent plus que vos ventes**

#### Fonctionnalité utilisateur
- Affiché au **tableau de bord** comme indicateur de productivité
- Utilisé pour comparer **activités entre elles**

---

### 7️⃣ **MB (Marge Brute)**

#### Définition agricole
Le **montant restant** après déduction des coûts variables — ce qui peut couvrir CF et générer un bénéfice.

#### Formule du code
```php
$MB = $PB - $CV;
```

#### Exemple concret
```
PB = 35 000 FCFA (total vendu)
CV = 19 000 FCFA (coûts variables)
MB = 35 000 - 19 000 = 16 000 FCFA
```

L'agriculteur a **16 000 FCFA** pour couvrir ses CF et faire du profit.

#### Interprétation
- **MB > 0 → Au moins couvre les variables, peut couvrir CF**
- **MB = 0 → Point mort sur variable (chaque vente juste couvre le coût direct)**
- **MB < 0 → Chaque vente creuse davantage le déficit**

#### Relation avec le statut global
- Si **MB > 0 mais RNE < 0** → Statut **ORANGE** (attention, CF trop hauts)
- Si **MB ≤ 0** et **RNE < 0** → Statut **ROUGE** (déficit grave)

#### Fonctionnalité utilisateur
- **Graphique mensuel:** Evolution de MB pour détecter quand les variables montrent un problème
- **Alerte d'orange:** Si MB > 0 mais RNE < 0

---

### 8️⃣ **RNE (Résultat Net d'Exploitation)**

#### Définition agricole
Le **profit ou la perte nette** — ce qui reste après TOUS les coûts.

#### Formule du code
```php
$RNE = $PB - $CT;
```

#### Décomposition
```
RNE = PB - (CV + CF)
RNE = PB - CV - CF
```

#### Exemple concret
```
PB = 35 000 FCFA
CV = 19 000 FCFA
CF = 5 500 FCFA
RNE = 35 000 - 19 000 - 5 500 = 10 500 FCFA
```

L'agriculteur a **fait un bénéfice net de 10 500 FCFA**.

#### Interprétation
- **RNE > 0 → Profit ✅**
- **RNE = 0 → Point mort (ni profit, ni perte)**
- **RNE < 0 → Perte ❌**

#### Utilisation métier
- **Critère principal pour le statut "vert"** (si RNE > 0 ET niveau BFVS atteint)
- Indicateur fondamental pour l'agriculteur
- Permet de **comparer des années** ou des exploitations

#### Fonctionnalité utilisateur
- **Affiché au tableau de bord en gros chiffres**
- Badge rouge si négatif
- Historique mensuel pour voir tendance

---

### 9️⃣ **RF (Rentabilité Financière / ROI)**

#### Définition agricole
Le **pourcentage de profit** relative aux coûts. Mesure **l'efficacité d'utilisation** des coûts engagés.

#### Formule du code
```php
$RF = $CT > 0 ? round(($RNE / $CT) * 100, 2) : 0;
```

#### Décomposition
```
RF = (RNE / CT) × 100%
RF = (10 500 / 24 500) × 100 = 42.86%
```

#### Exemple concret
For every 100 FCFA dépensés, l'agriculteur gagne **42.86 FCFA**.

Interprétation (à titre indicatif) :
- **RF > 20%** → Bon rendement (à titre indicatif) ✅
- **RF 5-20%** → Rendement moyen (à titre indicatif)
- **RF < 5%** → Rendement à améliorer — comparez avec vos autres campagnes ⚠️
- **RF < 0%** → Perte (dépenses > recettes)

#### Notes pédagogiques
- Ce pourcentage indique ce que vous gagnez pour chaque 100 FCFA dépensés.
- Exemple : 42% signifie que pour 100 FCFA dépensés, vous gagnez 42 FCFA.
- Interprétez selon le type d'activité et la durée de la campagne.

#### Algorithme détaillé
1. **Vérification:** CT > 0 ?
   - Si non → RF = 0 (pas de coûts = pas de ratio calculable)
   - Si oui → RF = (RNE / CT) × 100
2. **Arrondi:** `round(... , 2)` → 2 décimales

#### Fonctionnalité utilisateur
- Affiché dans le **tableau KPI** de la campagne
- Permet de **comparer efficacité** entre deux saisons
- Référence pour benchmarking agricole

---

## Seuil de Rentabilité (SR)

### 🔟 **SR (Seuil de Rentabilité)**

#### Définition agricole
Le **montant minimum de recettes** nécessaire pour **couvrir tous les coûts fixes** (atteindre RNE = 0).

**Notion clé:** Combien dois-je vendre pour que mon activité ne me coûte plus d'argent?

#### Formule agricole classique
```
Taux de marge sur CA = (PB - CV) / PB
SR = CF / Taux de marge
```

#### Formule du code
```php
$SR = null;
if ($PB > 0) {
    $tauxMarge = ($PB - $CV) / $PB;      // Taux de marge brute
    $SR = $tauxMarge > 0 ? round($CF / $tauxMarge, 2) : null;
} elseif ($CF > 0) {
    $SR = $CF;  // Cas limite: si aucune vente mais CF existent
}
```

#### Décomposition pas à pas

**Étape 1:** Calculer le taux de marge (%)
```
Taux marge = (PB - CV) / PB
           = (35 000 - 19 000) / 35 000
           = 16 000 / 35 000
           = 0.4571 (45.71% de marge)
```

**Étape 2:** Diviser les CF par ce taux
```
SR = CF / Taux marge
   = 5 500 / 0.4571
   = 12 030.96 FCFA (arrondi: 12 031 FCFA)
```

**Résultat:** L'agriculteur doit vendre au minimum **12 031 FCFA** pour couvrir ses CF.

#### Algorithme détaillé

```
ENTRÉE: PB, CV, CF
SORTIE: SR ou null

SI PB > 0:
    tauxMarge = (PB - CV) / PB
    SI tauxMarge > 0:
        SR = ARRONDIR(CF / tauxMarge, 2)
    SINON:
        SR = null
SINON SI CF > 0:
    SR = CF
SINON:
    SR = null
```

#### Cas pratiques et interprétation

| Cas | PB | CV | CF | Taux Marge | SR | Signification |
|-----|----|----|----|-----------|----|-----|
| ✅ Normal | 35k | 19k | 5.5k | 45.7% | 12k | Vendre minimum 12k pour couvrir CF |
| ⚠️ Faible marge | 20k | 18k | 2k | 10% | 20k | La quasi-totalité du PB juste couvre CF |
| ❌ Négative | 10k | 15k | 5k | -50% | null | Marge négative → impossible d'équilibrer |
| 🟢 Sans CF | 35k | 19k | 0 | 45.7% | 0 FCFA | Aucun CF → rentable immédiatement |
| 🔴 Zéro recette | 0 | 0 | 5.5k | indéfini | 5.5k | Fallback: SR = CF |

#### Cas spécial: CF = 0
```
EXEMPLE: PB=35k, CV=19k, CF=0
Taux marge = 0.4571
SR = 0 / 0.4571 = 0 FCFA ✅ CORRECT
```

**Signification:** Aucun coût fixe → tu commences à faire du profit dès la première vente.
- **PAS une erreur**, c'est **mathématiquement juste**
- Indique que l'agriculteur n'a pas de charges structurelles

#### Fonctionnalité utilisateur
- **Affiché dans la fiche campagne** comme "Ventes nécessaires pour équilibre"
- **Comparé au PB actuel** pour afficher "Atteint" ou "Non atteint"
- **Graphiquement:** Badge vert si PB ≥ SR, rouge sinon
- **Exemple:** Si SR=12k et PB=35k → "✅ Atteint (35k ≥ 12k)"

---

## Statut Global

### Règle de détermination du statut

#### Formule du code
```php
private function determinerStatut(float $PB, float $MB, float $RNE, ?float $SR): string
{
    if ($RNE > 0 && ($SR === null || $PB >= $SR)) {
        return 'vert';    // ✅ Rentable
    }
    if ($MB > 0) {
        return 'orange';  // ⚠️ À surveiller
    }
    return 'rouge';       // ❌ Non viable
}
```

#### Algorithme détaillé

```
ENTRÉE: RNE, SR, PB, MB
SORTIE: 'vert' | 'orange' | 'rouge'

// Condition 1: Profit positif ET SR atteint
SI RNE > 0 ET (SR est null OU PB >= SR):
    RETOURNER 'vert'      ✅ RENTABLE & VIABLE

// Condition 2: Marge brute positive (peut couvrir CF)
SINON SI MB > 0:
    RETOURNER 'orange'    ⚠️  À SURVEILLER

// Condition 3: Tout le reste
SINON:
    RETOURNER 'rouge'     ❌ DÉFICIT
```

#### Tableau récapitulatif

| RNE | MB | SR | PB vs SR | Statut | Explication |
|-----|----|----|----------|--------|---------|
| **> 0** | > 0 | < 35k | PB ≥ SR | **🟢 VERT** | Profit + SR atteint = rentable |
| **0,5k** | 5k | 12k | 12k | **🟢 VERT** | Même avec SR, tu fais +500 FCFA |
| **-2k** | 8k | 15k | 12k | **🟠 ORANGE** | Marge positive mais CF trop hauts (déficit) |
| **-0,5k** | 0 | 10k | 9k | **🔴 ROUGE** | Ni marge, ni profit → non viable |
| **-5k** | -2k | null | 0 | **🔴 ROUGE** | Tout est négatif |

#### Cas d'usage concrets

**Cas 1 — Vert (Rentable)**
```
PB = 35 000 FCFA  (recettes)
CV = 19 000 FCFA  (coûts variables)
CF = 5 500 FCFA   (coûts fixes)

MB = 35k - 19k = 16k ✅ Positif
RNE = 35k - 19k - 5.5k = 10.5k ✅ Positif
SR = 5.5k / 0.457 = 12k
PB (35k) >= SR (12k) ✅

→ STATUT = 'VERT' ✅
```

**Cas 2 — Orange (À surveiller)**
```
PB = 10 000 FCFA
CV = 7 000 FCFA
CF = 6 000 FCFA

MB = 10k - 7k = 3k ✅ Positif
RNE = 10k - 7k - 6k = -3k ❌ Négatif (déficit)
SR = 6k / 0.30 = 20k
PB (10k) < SR (20k) ❌

→ STATUT = 'ORANGE' ⚠️ (Marge positive mais CF trop hauts)
```

**Cas 3 — Rouge (Non viable)**
```
PB = 8 000 FCFA
CV = 9 000 FCFA
CF = 3 000 FCFA

MB = 8k - 9k = -1k ❌ Négatif
RNE = 8k - 9k - 3k = -4k ❌ Négatif
SR = impossible à calculer

→ STATUT = 'ROUGE' ❌
```

#### Fonctionnalité utilisateur
- **Badge couleur** sur la fiche campagne (vert/orange/rouge)
- **Dashboard:** Résumé avec statut global
- **Tri des campagnes:** Par statut pour priorités d'action
- **Alertes:** Notif si bascule d'orange à rouge

---

## Consolidation par Exploitation

### Agrégation multi-campagnes

Quand un agriculteur a **plusieurs campagnes actives** (ex: tomates, maïs, élevage), AgroFinance+ agrège tous les indicateurs.

#### Code
```php
public function calculerExploitation(int $exploitationId, ?string $dateDebutMin = null, ?string $debut = null, ?string $fin = null): array
{
    // Pour chaque campagne active...
    $parActivite = [];
    foreach ($exploitation->activitesActives as $activite) {
        $parActivite[$activite->id] = $this->calculer($activite->id, $debut, $fin, $dateDebutMin);
    }

    // ... alors sommer tous les indicateurs
    $PBt = collect($parActivite)->sum('PB');
    $CVt = collect($parActivite)->sum('CV');
    $CFt = collect($parActivite)->sum('CF');
    $CTt = collect($parActivite)->sum('CT');
    // ...
}
```

#### Formules de consolidation

| Indicateur | Formule | Exemple |
|-----------|---------|---------|
| **PB consolidé** | Σ PB(chaque campagne) | 35k + 28k + 15k = 78k FCFA |
| **CV consolidé** | Σ CV(chaque campagne) | 19k + 12k + 8k = 39k FCFA |
| **CF consolidé** | Σ CF(chaque campagne) | 5.5k + 3k + 2k = 10.5k FCFA |
| **CT consolidé** | CV + CF consolidés | 39k + 10.5k = 49.5k FCFA |
| **MB consolidée** | PB - CV consolidés | 78k - 39k = 39k FCFA |
| **RNE consolidé** | PB - CT consolidés | 78k - 49.5k = 28.5k FCFA |
| **RF consolidé** | (RNE / CT) × 100 | (28.5k / 49.5k) × 100 = 57.6% |

#### Particularités
- **SR au niveau exploitation:** Non calculé (car les campagnes sont indépendantes)
- **Statut consolidé:** Basé sur RNE consolidé et MB consolidée (pas de SR)
- **Donnees_indicatives:** Si < 5 transactions OU déséquilibre recettes/dépenses

#### Cas d'usage utilisateur
- **Dashboard accueil:** Vue globale de la ferme entière
- **Export / Rapports:** Chiffres consolidés pour prêteurs/banques
- **Comparaison:** Quelle campagne rapporte le plus?

---

## Algorithme d'Évaluation des Données

### Indicateur de fiabilité: `donnees_indicatives`

Les indicateurs sont **calculés même avec peu de données**, mais marqués comme "indicatifs" si les données sont insuffisantes.

#### Code
```php
private function evaluerDonneesIndicatives(int $nbTx, int $nbRec, int $nbDep): bool
{
    if ($nbTx < 5) {
        return true;  // Trop peu de transactions
    }
    if ($nbRec > 0 && $nbDep === 0) {
        return true;  // Que des recettes, pas de dépenses
    }
    if ($nbDep > 0 && $nbRec === 0) {
        return true;  // Que des dépenses, pas de recettes
    }
    return false;     // Données fiables
}
```

#### Logique détaillée

| Condition | Exemple | Fiabilité | Pourquoi |
|-----------|---------|-----------|---------|
| **< 5 transactions** | 2 ventes, 1 dépense | 🟡 Indicatif | Trop peu d'échantillon |
| **Que recettes** | 10 ventes, 0 dépenses | 🟡 Indicatif | Budget incomplet, indicateurs biaisés |
| **Que dépenses** | 0 ventes, 15 achats | 🟡 Indicatif | RNE négatif, pas de ventes = test d'achat? |
| **≥ 5 TX + Mix** | 7 dépenses, 8 recettes | ✅ Fiable | Bon volume ET équilibre |

#### Cas concrets

**Cas 1 — Données indicatives (jeune campagne)**
```
PB = 5 000 FCFA (1 seule vente)
CT = 8 000 FCFA (3 achats)
→ nbTx = 4 → donnees_indicatives = TRUE
→ Utilisateur voit ⚠️ "Données incomplètes, à titre informatif"
```

**Cas 2 — Données fiables (campagne mature)**
```
PB = 35 000 FCFA (8 ventes)
CT = 24 500 FCFA (12 dépenses)
→ nbTx = 20 → donnees_indicatives = FALSE
→ Utilisateur voit ✅ "Chiffres fiables"
```

#### Fonctionnalité utilisateur
- **Badge ⚠️ "Données indicatives"** sur les campagnes jeunes
- **Transparence:** L'utilisateur sait que les indicateurs ne sont pas définitifs
- **Graphiques:** Trend ne s'affiche que si fiable

---

## Évolution Mensuelle

### Tracking sur 12 mois

Pour le **graphique du dashboard**, AgroFinance+ calcule les indicateurs pour **chaque mois des 12 derniers mois**.

#### Code
```php
public function evolutionMensuelle(int $activiteId, ?string $dateDebutMin = null): array
{
    $evolution = [];
    for ($i = 11; $i >= 0; $i--) {
        $mois = now()->subMonths($i);  // Itère 11 mois avant, jusqu'à aujourd'hui
        $start = $mois->copy()->startOfMonth()->toDateString();
        $end = $mois->copy()->endOfMonth()->toDateString();

        // Si mois entièrement avant le plancher d'abonnement
        if ($dateDebutMin && $end < $dateDebutMin) {
            $evolution[] = [
                'mois' => $mois->format('M Y'),
                'MB' => 0.0,
                'RNE' => 0.0,
                'PB' => 0.0,
                'CT' => 0.0,
            ];
            continue;
        }

        // Sinon, calcul normal
        $ind = $this->calculer($activiteId, $start, $end, $dateDebutMin);
        $evolution[] = [
            'mois' => $mois->format('M Y'),
            'MB' => $ind['MB'],
            'RNE' => $ind['RNE'],
            'PB' => $ind['PB'],
            'CT' => $ind['CT'],
        ];
    }
    return $evolution;
}
```

#### Algorithme détaillé

```
POUR i = 11 À 0 (12 itérations, mois arrière à présent):
    mois_courant = MAINTENANT - i mois
    debut_mois = 1er du mois_courant
    fin_mois = dernier jour du mois_courant

    SI dateDebutMin EXISTE ET fin_mois < dateDebutMin:
        → Mois trop ancien (plan gratuit n'a pas accès)
        → Afficher zéros
    SINON:
        → Calculer indicateurs pour ce mois normalement

    Ajouter à evolution[]
RETOURNER evolution[]
```

#### Exemple concret (Février 2026)

**Entrée:**
- Activité "Tomates", aujourd'hui = 3 avril 2026
- Plan gratuit: `dateDebutMin` = 1er janvier 2026

**Itération i=11 (mai 2025):**
- `mois = avril 2025`
- `fin_mois = 30 avril 2025`
- ❌ 30/04/2025 < 01/01/2026 → **MB=0, RNE=0, PB=0, CT=0**

**Itération i=6 (septembre 2025):**
- `mois = septembre 2025`
- `fin_mois = 30 septembre 2025`
- ❌ 30/09/2025 < 01/01/2026 → **Zéros**

**Itération i=3 (décembre 2025):**
- `mois = décembre 2025`
- `fin_mois = 31 décembre 2025`
- ❌ 31/12/2025 < 01/01/2026 → **Zéros**

**Itération i=2 (janvier 2026):**
- `mois = janvier 2026`
- `fin_mois = 31 janvier 2026`
- ✅ 31/01/2026 >= 01/01/2026 → **Calcul normal**
  - Transactions du 1-31 janvier 2026
  - Résultat: {MB: 15800, RNE: 8300, PB: 28000, CT: 19700}

**Itération i=1 (février 2026):**
- `mois = février 2026`
- `fin_mois = 28 février 2026`
- ✅ Calcul normal → {MB: 16200, RNE: 9100, PB: 30000, CT: 20900}

**Itération i=0 (mars 2026 / maintenant):**
- `mois = mars 2026`
- `fin_mois = 31 mars 2026`
- ✅ Calcul normal (aujourd'hui = 3 avril, donc mars complet)

#### Fonctionnalité utilisateur
- **Graphique linéaire 12 mois** sur le dashboard
- **Courbes:** MB (marge), RNE (profit), PB (recettes), CT (costs)
- **Détection de tendance:** Rentabilité montante? Coûts explosifs?
- **Alertes:** Si RNE devient négatif les 2 derniers mois

---

## Cas d'Usage Concrets

### Scénario 1 — Campagne de riz mature et rentable

**Données entrées :**
```
Date début: 1er janvier 2026
Date fin: 30 avril 2026

Recettes (type='recette'):
  - Vente riz blanc: 2 × 50 000 FCFA = 100 000 FCFA
  - Vente paille: 5 000 FCFA
  → PB = 105 000 FCFA

Dépenses variables (nature='variable'):
  - Semences: 8 000 FCFA
  - Engrais: 12 000 FCFA
  - Pesticides: 5 000 FCFA
  - Main-d'œuvre temporaire: 15 000 FCFA
  → CV = 40 000 FCFA

Dépenses fixes (nature='fixe'):
  - Loyer terrain: 5 000 FCFA
  - Amortissement matériel: 2 000 FCFA
  → CF = 7 000 FCFA

Charges intermédiaires (CI):
  - Semences: 8 000 ✅ (catégorie semences)
  - Engrais: 12 000 ✅ (catégorie engrais)
  - Pesticides: 5 000 ✅ (catégorie pesticides)
  → CI = 25 000 FCFA
```

**Calculs:**
```
CT = CV + CF = 40 000 + 7 000 = 47 000 FCFA

VAB = PB - CI = 105 000 - 25 000 = 80 000 FCFA
     (Valeur créée au-delà des intrants)

MB = PB - CV = 105 000 - 40 000 = 65 000 FCFA
     (Marge brute positive ✅)

RNE = PB - CT = 105 000 - 47 000 = 58 000 FCFA
      (Profit net ✅)

RF = (RNE / CT) × 100 = (58 000 / 47 000) × 100 = 123.4%
     (123% de rentabilité = très bon!)

Taux Marge = (PB - CV) / PB = 65 000 / 105 000 = 0.619 (61.9%)
SR = CF / Taux Marge = 7 000 / 0.619 = 11 310 FCFA
     (Dès 11.3k FCFA de ventes, l'agriculteur couvre tous les CF)
```

**Résultat:**
- **MB = 65 000** ✅ Marge brute excellente
- **RNE = 58 000** ✅ Profit considérable
- **SR = 11 310 vs PB = 105 000** ✅ Seuil largement atteint
- **RF = 123.4%** ✅ Très rentable
- **Statut = VERT** ✅ Campagne très profitable

**Explication pour la soutenance:**
"Cette campagne de riz a généré 105 000 FCFA de ventes pour 47 000 FCFA de coûts. Après déduction de tous les coûts, l'agriculteur a 58 000 FCFA de profit net. Le seuil de rentabilité est atteint avec marge (11 300 FCFA nécessaires, 105 000 actuels). C'est une campagne qui fonctionne bien."

---

### Scénario 2 — Campagne de maïs avec problème de coûts fixes

**Données entrées :**
```
Recettes:
  - Vente maïs: 30 000 FCFA
  → PB = 30 000 FCFA

Dépenses variables:
  - Semences maïs: 4 000 FCFA
  - Engrais: 6 000 FCFA
  - Pesticides: 2 000 FCFA
  → CV = 12 000 FCFA

Dépenses fixes:
  - Loyer de base du terrain: 5 000 FCFA
  - Salaire gardien permanent: 6 000 FCFA
  - Amortissement magasin: 2 000 FCFA
  → CF = 13 000 FCFA TRÈS ÉLEVÉ!
```

**Calculs:**
```
CT = 12 000 + 13 000 = 25 000 FCFA

MB = 30 000 - 12 000 = 18 000 FCFA ✅ Positive

RNE = 30 000 - 25 000 = 5 000 FCFA ✅ Positif mais faible

RF = (5 000 / 25 000) × 100 = 20%
     (Pour 100 FCFA dépensés, 20 FCFA de marge = faible)

Taux Marge = 18 000 / 30 000 = 0.60 (60%)
SR = 13 000 / 0.60 = 21 667 FCFA ❌
     (Besoin de 21.7k FCFA en ventes pour équilibrer)
     (PB = 30k > SR = 21.7k → OUI, c'est atteint)
```

**Résultat:**
- **MB = 18 000** ✅ Marge brute acceptable
- **RNE = 5 000** 🟡 Profit très faible (15% des recettes!)
- **CF = 13 000** ⚠️ Coûts fixes trop élevés (43% du PB)
- **SR = 21 667** 🟡 Seuil haut, PB juste suffisant
- **Statut = VERT** ✅ Techniquement atteint mais FRAGILE

**Ce qui se passe:** L'agriculteur a des coûts fixes très élevés (salaire, loyer). Bien que la marge brute soit positive, elle est "mangée" par les CF. Le profit est très mince (5k sur 30k). **Si les ventes baissent de 20%, il passe à négatif.**

**Explication pour la soutenance:**
"Cette campagne montre un cas d'alerte : bien que le statut soit vert (seuil atteint), la rentabilité est faible (20%) à cause de coûts fixes trop élevés. L'agriculteur doit soit réduire ses CF (négocier le loyer, réduire les salaires permanents) soit augmenter les ventes pour diluer le poids des CF."

---

### Scénario 3 — Jeune campagne avec données incomplètes

**Données entrées :**
```
Recettes (2 transactions seulement):
  - Vente courges: 7 000 FCFA
  - Vente aubergines: 5 000 FCFA
  → PB = 12 000 FCFA

Dépenses (2 transactions):
  - Semences: 3 000 FCFA
  - Engrais: 2 500 FCFA
  → CV = 5 500 FCFA

CF = 0 (pas encore de frais structurels enregistrés)
→ CT = 5 500 FCFA
```

**Calculs:**
```
MB = 12 000 - 5 500 = 6 500 FCFA
RNE = 12 000 - 5 500 = 6 500 FCFA
RF = 118% (excellent!)
SR = 0 FCFA (pas de CF)

Évaluation de fiabilité:
  nbTx = 4 < 5 → donnees_indicatives = TRUE ⚠️
```

**Résultat:**
- **Statut = VERT** ✅ Techniquement
- **MAIS:** Badge ⚠️ **" Données indicatives"**
- Affichage: "Les chiffres sont basés sur peu de données. Continuer à saisir pour affiner."

**Explication pour la soutenance:**
"Quand une campagne est nouvelle, AgroFinance+ calcule quand même les indicateurs, mais les marque comme 'indicatifs' si moins de 5 transactions. Cela prévient l'agriculteur que ses chiffres ne sont pas encore fiables. Dès qu'il a 5+ transactions ET un équilibre entre recettes/dépenses, les indicateurs deviennent 'fiables'."

---

### Scénario 4 — Campagne avec CF = 0 (activité sans charges fixes)

**Données entrées :**
```
PB = 40 000 FCFA (ventes régulières)
CV = 25 000 FCFA (intrants)
CF = 0 FCFA ← IMPORTANT!
     (Pas de loyer, pas de salaire permanent,
      c'est une petite activité sans structure)
```

**Calculs:**
```
MB = 40 000 - 25 000 = 15 000 FCFA
RNE = 40 000 - 25 000 = 15 000 FCFA (= MB)
  (Sans CF, profit = marge)

Taux Marge = 15 000 / 40 000 = 0.375
SR = 0 / 0.375 = 0 FCFA ✅ CORRECT!
  (Pas de seuil à franchir = rentable immédiatement)
```

**Résultat:**
- **SR = 0 FCFA** (pas une erreur!)
- **Signification:** "Aucun coût fixe — tu es rentable dès la vente suivante"
- **Affichage:** "0 FCFA + ✅ Atteint"

**Pour la soutenance (si question sur le 0):**
"Quand CF = 0, le SR calcule à 0 FCFA. C'est mathématiquement correct. Cela indique que l'agriculteur n'a pas de charges structurelles fixes; il devient rentable dès qu'il commence à vendre. C'est souvent le cas pour les petits commerces ou activités saisonnières."

---

### Scénario 5 — Consolidation multi-campagnes exploitation

**Données (Exploitation = 3 campagnes actives):**

**Campagne 1 — Tomates:**
```
PB1 = 50 000, CV1 = 20 000, CF1 = 5 000
```

**Campagne 2 — Maïs:**
```
PB2 = 30 000, CV2 = 12 000, CF2 = 3 000
```

**Campagne 3 — Élevage poules:**
```
PB3 = 25 000, CV3 = 10 000, CF3 = 2 000
```

**Consolidation:**
```
PB_total = 50k + 30k + 25k = 105 000 FCFA
CV_total = 20k + 12k + 10k = 42 000 FCFA
CF_total = 5k + 3k + 2k = 10 000 FCFA
CT_total = 42k + 10k = 52 000 FCFA

MB_consolidée = 105k - 42k = 63 000 FCFA
RNE_consolidé = 105k - 52k = 53 000 FCFA
RF_consolidé = (53k / 52k) × 100 = 102%
```

**Statut:** VERT (RNE > 0 ET MB > 0)

**Dashboard utilisateur:**
- Affiche "Exploitation générale"
- PB: 105k, CT: 52k, RNE: 53k, RF: 102%
- Avec détail: "Tomates (50k), Maïs (30k), Élevage (25k)"
- Rapport: "La ferme est globalement rentable. Tomates rapportent le plus."

---

## Récapitulatif des Formules

| Indicateur | Formule | Exemple | Valeur attendue |
|-----------|---------|---------|---------|
| **PB** | Σ recettes | 35k + 20k = 55k | Positif |
| **CV** | Σ (dépenses où nature='variable') | 20k + 15k = 35k | 0 à PB |
| **CF** | Σ (dépenses où nature='fixe') | 5k + 2k = 7k | 0 à PB |
| **CT** | CV + CF | 35k + 7k = 42k | CV ≤ CT ≤ PB+CF |
| **CI** | Σ (dépenses où categorie ∈ intrants) | 20k | 0 ≤ CI ≤ CV |
| **VAB** | PB - CI | 55k - 20k = 35k | 0 à PB |
| **MB** | PB - CV | 55k - 35k = 20k | 0 à PB |
| **RNE** | PB - CT | 55k - 42k = 13k | -∞ à +∞ (profit/perte) |
| **RF** | (RNE / CT) × 100% | (13k / 42k) × 100 = 31% | % de rentabilité |
| **SR** | CF / ((PB - CV) / PB) | 7k / (20k/55k) ≈ 19.25k | 0 à +∞ |

---

## Points Importants pour la Soutenance

### ✅ Ce que vous devez pouvoir expliquer

1. **Différence CV vs CF:** Variables changent avec la prod, fixes non.
2. **Pourquoi le SR = 0 quand CF = 0:** Pas de seuil = rentable immédiatement.
3. **Pourquoi MB est orange:** Marge positive mais RNE négatif = CF trop hauts.
4. **Comment fonctionne la consolidation:** Toutes les campagnes additionnées = vue ferme.
5. **Quand les données sont indicatives:** < 5 TX OU déséquilibre recettes/dépenses.
6. **Évolution mensuelle:** Mois trop anciens (plan gratuit) = 0, sinon calcul normal.

### ⚠️ Pièges courants à éviter

- ❌ "SR = 0 c'est une erreur" → ✅ "C'est correct quand CF = 0"
- ❌ "RF de 5% c'est mauvais" → ✅ "Dépend du secteur; en agro, 10-20% est acceptable"
- ❌ "Les données indicatives c'est fiable?" → ✅ "Non, juste informatif jusqu'à 5 TX"
- ❌ "Pourquoi l'exploitation a statut vert mais une campagne orange?" → ✅ "Statuts indépendants, consolidé regarde l'ensemble"

---

## Pour Aller Plus Loin (Optionnel)

### Adaptation aux normes SIM (Systèmes Informatisés de Gestion)

Ces indicateurs suivent partiellement la **norme SIM du secteur agricole africain**, adaptée au contexte des petits exploitants:
- PB et CT sont standards
- SR est une adaptation du "point mort" en gestion commerciale classique
- MB et RNE permettent d'évaluer la viabilité microéconomique

### Cas d'Amélioration Future

1. **Ratio endettement:** Intégrer les dettes/crédits pour raffiner RF
2. **Saisonnalité:** Pondérer les mois par importance saisonnière
3. **Performance par culture:** Comparer rendement/montant d'intrants
4. **Alertes automatiques:** Si CF dépassent 40% PB → warning
5. **Comparaison benchmark:** "Votre RF 102% vs moyenne régionale 85%"

---

**Fin du document — Bonne chance à la soutenance! 🎓**
