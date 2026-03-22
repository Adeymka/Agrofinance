# Sprint 4 — Indicateurs financiers agricoles, évolution mensuelle & dashboard

> Document vivant. Doc API détaillée : [`API_CLIENT.md`](./API_CLIENT.md).

## Objectif

Exposer côté API les **indicateurs financiers agricoles** par activité et par exploitation, une **série mensuelle** pour graphiques, et un **dashboard** consolidé pour l’utilisateur connecté.

## Périmètre technique

| Élément | Emplacement |
|--------|-------------|
| Service calculs | `app/Services/FinancialIndicatorsService.php` |
| Contrôleurs | `app/Http/Controllers/Api/IndicateurController.php`, `DashboardController.php` |
| Routes | `routes/api.php` (middleware `auth:sanctum`) |

**Règle de routage** : la route la plus spécifique en premier —  
`GET /indicateurs/activite/{id}/evolution` **avant** `GET /indicateurs/activite/{id}`.

**Sécurité** : accès aux activités / exploitations via `user_id` de l’utilisateur authentifié (Sanctum).

## Endpoints Sprint 4

| Méthode | Route | Description |
|---------|--------|-------------|
| GET | `/indicateurs/activite/{id}` | Indicateurs activité ; query optionnelle `debut`, `fin` |
| GET | `/indicateurs/activite/{id}/evolution` | 12 mois glissants (mois civil), points `MB`, `RNE`, `PB`, `CT` |
| GET | `/indicateurs/exploitation/{id}` | Par activité active + `consolide` exploitation |
| GET | `/dashboard` | Globale, par exploitation (actives), dernières transactions, alertes, abonnement |

Base URL dev typique : `http://localhost/agrofinanceplus/public/api`  
Headers : `Authorization: Bearer <token>`, `Accept: application/json`.

## Données de référence (tests manuels)

Pour rejouer les scénarios ci-dessous :

- **Exploitation** `id: 2` — « Ferme Akobi »
- **Activité** `id: 2` — « Maïs 2025 bis » (`en_cours`)
- **Transactions** : 1 dépense variable ~50k (mars 2025), 1 recette ~450k (juillet 2025)

*(Ajuster les montants/dates si ta base diverge.)*

## Critères d’acceptation & résultats attendus

### 1. Indicateurs activité (sans filtre)

`GET /indicateurs/activite/2`

- `succes: true`
- `PB` ≈ recettes totales ; `CV`/`CT` reflètent les dépenses retenues
- `MB`, `RNE`, `RF`, `statut`, compteurs transactions cohérents avec les données réelles

### 2. Indicateurs activité (période)

`GET /indicateurs/activite/2?debut=2025-01-01&fin=2025-06-30`

- Seules les transactions dont `date_transaction` est **dans** l’intervalle
- Recette en juillet **exclue** → indicateurs différents du test sans filtre si la recette est hors période

### 3. Évolution mensuelle

`GET /indicateurs/activite/2/evolution`

- Tableau **`evolution`** de **12** entrées, fenêtre basée sur **`now()`** serveur
- Chaque mois : `mois`, `mois_num`, `MB`, `RNE`, `PB`, `CT`
- **Attention** : une opération **antérieure** au premier mois de la fenêtre **n’apparaît dans aucune barre** (comportement normal)

### 4. Indicateurs exploitation

`GET /indicateurs/exploitation/2`

- `par_activite` avec clé **`"2"`** (ID activité) pour l’exemple ci-dessus
- `consolide` aligné avec la somme des activités **actives** de l’exploitation

### 5. Dashboard

`GET /dashboard`

- `user`, `consolide_global`, `indicateurs_par_exploitation`, `dernieres_transactions`
- `indicateurs_par_exploitation` : **uniquement** exploitations ayant **au moins une activité active** (voir code `DashboardController`)
- `nb_exploitations` : total exploitations utilisateur (peut être > nombre d’entrées dans `indicateurs_par_exploitation`)
- `alertes_budget`, `abonnement` présents

## Erreurs attendues

| Cas | Réponse typique |
|-----|-----------------|
| Sans token ou token invalide | **401** |
| `id` activité / exploitation inexistant ou pas à l’utilisateur | **404** |

## Notes produit / intégration

- Dates API souvent en **UTC** (`Z`) : conversion timezone côté client pour l’affichage.
- `statut` : `vert` / `orange` / `rouge` — à mapper vers l’UI.

## Évolutions possibles (backlog doc)

- [ ] Dupliquer ici les payloads JSON exemples (ou lier vers collection Postman)
- [ ] Détailing des règles `statut` (formules + seuils)
- [ ] Spec complète module `alertes_budget` et abonnement

---

*Sprint 4 — document initial, mars 2026.*
