# Documentation API — AgroFinance+ (client / intégration)

> Document vivant : à adapter au fur et à mesure (URL de production, versions, nouveaux champs).

## Base URL

- **Développement (ex.)** : `http://localhost/agrofinanceplus/public/api/v1`  
- Toutes les routes ci-dessous sont **relatives** à ce préfixe (ex. `GET /dashboard` → `GET …/api/v1/dashboard`).

## Authentification

Les endpoints métier utilisent **Laravel Sanctum** : envoyer le token dans l’en-tête :

```http
Authorization: Bearer <token>
Accept: application/json
```

Sans token valide : réponse **401**.

## Format des réponses réussies

En général :

```json
{
  "succes": true,
  "data": { }
}
```

Les erreurs Laravel classiques s’appliquent (ex. **404** si ressource inexistante ou non accessible par l’utilisateur connecté).

## Données et dates

- Les montants sont souvent des **nombres** ; certains champs de transaction peuvent être renvoyés en **chaîne** décimale selon le modèle (ex. `"450000.00"`).
- Les dates/heures au format ISO avec **`Z`** sont en **UTC**. Pour un affichage « jour civil » local, convertir côté application cliente.

---

# Module authentification (résumé)

| Méthode | Route | Auth |
|--------|--------|------|
| POST | `/auth/inscription` | Non |
| POST | `/auth/verification-otp` | Non |
| POST | `/auth/renvoyer-otp` | Non |
| POST | `/auth/creer-pin` | Non |
| POST | `/auth/connexion` | Non |
| POST | `/auth/deconnexion` | Oui |
| GET | `/auth/me` | Oui |

*Le détail des corps de requête/réponse auth peut être complété ici.*

---

# Ressources métier (token requis)

| Méthode | Route |
|--------|--------|
| GET/POST | `/exploitations`, `/exploitations/{id}` |
| GET/POST/PUT | `/activites`, `/activites/{id}`, `POST /activites/{id}/cloturer` |
| GET/POST/PUT/DELETE | `/transactions`, `/transactions/{id}` |
| POST/GET/DELETE | `/transactions/{id}/justificatif` — pièce jointe (voir section dédiée) |
| GET | `/indicateurs/activite/{id}/evolution` |
| GET | `/indicateurs/activite/{id}` |
| GET | `/indicateurs/exploitation/{id}` |
| GET | `/dashboard` |

---

# Indicateurs financiers agricoles

Les indicateurs suivent une logique de gestion agricole standard (ex. **PB**, **CV**, **CF**, **Marge brute**, **RNE**, **Rentabilité financière RF**, **Seuil de rentabilit SR** quand applicable).

## GET `/indicateurs/activite/{id}`

Indicateurs pour **une activité** appartenant à une exploitation de l’utilisateur connecté.

**Paramètres de requête (optionnels)**

| Param | Description |
|-------|-------------|
| `debut` | Date début (incluse), ex. `2025-01-01` — filtre sur `date_transaction` |
| `fin` | Date fin (incluse), ex. `2025-06-30` |

**Réponse `data` (extrait)**

- `activite_id`, `activite_nom`, `type`, `statut_campagne`
- `PB`, `CV`, `CF`, `CT`, `CI`, `VAB`, `MB`, `RNE`, `RF`, `SR`
- `statut` : `vert` | `orange` | `rouge`
- `nb_transactions`, `nb_depenses`, `nb_recettes`
- `derniere_saisie` : dernière mise à jour parmi les transactions prises en compte (peut être `null`)

## GET `/indicateurs/activite/{id}/evolution`

Série pour **graphique** : **12 mois** glissants, calculés à partir de la **date/heure du serveur** (`now()`).

Pour chaque mois : indicateurs de l’activité **uniquement** sur ce mois civil (du 1er au dernier jour).

**Chaque élément de `evolution` contient**

| Champ | Description |
|-------|-------------|
| `mois` | Libellé court (ex. `Jul 2025`, locale serveur) |
| `mois_num` | Clé `YYYY-MM` |
| `MB`, `RNE`, `PB`, `CT` | Valeurs du mois |

> **Important** : une transaction **avant** la fenêtre des 12 mois n’apparaît dans **aucune** ligne de cette série (comportement normal).

## GET `/indicateurs/exploitation/{id}`

Indicateurs pour une **exploitation** de l’utilisateur.

- Agrège uniquement les **activités actives** du modèle (`activitesActives`).
- `data.par_activite` : objet dont les clés sont les **IDs d’activité** (chaînes JSON), chaque valeur contient `nom`, `type` + les mêmes champs d’indicateurs que pour une activité seule.
- `data.consolide` : totaux exploitation — `PB`, `CT`, `MB`, `RNE`, `RF`, `statut`  
  *(pas tous les champs détaillés du niveau activité.)*

---

# Dashboard

## GET `/dashboard`

Vue d’ensemble pour l’utilisateur connecté.

**`data` contient**

| Champ | Description |
|-------|-------------|
| `user` | `nom`, `prenom` |
| `consolide_global` | Somme des `consolide` des exploitations **ayant au moins une activité active** : `PB`, `MB`, `RNE`, `CT`, `RF`, `statut` |
| `indicateurs_par_exploitation` | Objet clé = `exploitation_id` ; uniquement les exploitations avec **au moins une activité active** ; même structure que `GET /indicateurs/exploitation/{id}` (`nom`, `par_activite`, `consolide`) |
| `dernieres_transactions` | Jusqu’à **10** transactions, tri **par `date_transaction`** décroissante, toutes exploitations de l’utilisateur ; relation `activite` (`id`, `nom`) chargée |
| `alertes_budget` | Tableau d’alertes issues du budget par activité active (structure détaillée à documenter si besoin) |
| `nb_exploitations` | Nombre total d’exploitations du user (**toutes**, pas seulement celles avec indicateurs) |
| `abonnement` | Si abonnement actif : `plan`, `statut` ; sinon `{ "plan": "aucun", "statut": "inactif" }` |

**Statut global du dashboard** (`consolide_global.statut`) : vert si `RNE > 0`, sinon orange si `MB > 0`, sinon rouge.

---

# Justificatifs de transaction (fichier)

Les transactions peuvent avoir une pièce jointe (photo ou PDF). Le chemin interne n’est **pas** exposé dans les JSON : chaque transaction inclut un booléen **`has_justificatif`**.

| Méthode | Route | Description |
|---------|--------|-------------|
| POST | `/transactions/{id}/justificatif` | Corps **multipart** : champ fichier **`justificatif`** (obligatoire). Types : JPEG, PNG, WEBP, PDF ; max. **5120 Ko**. Campagne doit être **en cours**. |
| GET | `/transactions/{id}/justificatif` | Téléchargement du fichier (propriétaire uniquement). **404** si aucun fichier ou campagne non autorisée. |
| DELETE | `/transactions/{id}/justificatif` | Supprime le fichier et remet `has_justificatif` à faux. |

La saisie **hors ligne** (mobile) ne gère pas l’envoi de justificatif : ajouter le fichier **en ligne** après synchronisation des transactions, via cet endpoint ou via le formulaire web.

---

# Codes et erreurs utiles

| Situation | Comportement typique |
|-----------|----------------------|
| Token absent ou invalide | **401** |
| `id` activité / exploitation inconnu ou pas à l’utilisateur | **404** |

---

# Notes pour l’équipe produit / mobile

- Prévoir gestion **hors ligne / sync** : champ `synced` sur les transactions quand présent.
- Les libellés de **`statut`** (`vert` / `orange` / `rouge`) sont destinés à être mappés vers l’UI (couleurs, libellés métier).
- Penser **timezone** pour comparer les filtres `debut` / `fin` avec ce que voit l’utilisateur sur le terrain.

---

# Sprint 5 — Rapports PDF & abonnement FedaPay

## Rapports (auth requise sauf partage)

| Méthode | Route | Description |
|--------|--------|-------------|
| POST | `/api/v1/rapports/generer` | Body JSON : `activite_id`, `type` (`campagne`, `dossier_credit`, `mensuel`, `annuel`), `periode_debut`, `periode_fin` — crée un PDF dans `storage/app/rapports/`, token de partage 72h |
| GET | `/api/v1/rapports` | Liste des rapports de l’utilisateur (+ exploitation) |
| GET | `/api/v1/rapports/{id}/telecharger` | Téléchargement PDF (propriétaire uniquement) |

**Partage public (sans token)** — route **web** : `GET /partage/{token}` (ex. `{{APP_URL}}/partage/...`). Réponses JSON si lien invalide / expiré (**404**, **410**), sinon PDF **inline**.

> Définir `APP_URL` (ex. `http://localhost/agrofinanceplus/public`) pour des liens corrects dans le PDF.

## Abonnement FedaPay

| Méthode | Route | Auth |
|--------|--------|------|
| POST | `/api/v1/abonnement/initier` | Oui — body : `plan` (`mensuel` \| `annuel`), `telephone` → `data.url_paiement` |
| GET | `/api/v1/abonnement/callback` | **Non** — redirection FedaPay ; active l’abonnement si statut `approved` / `transferred` |

Variables `.env` : `FEDAPAY_SECRET_KEY`, `FEDAPAY_PUBLIC_KEY`, `FEDAPAY_ENVIRONMENT` (`sandbox` / `live`). Clés : compte **FedaPay** (<https://fedapay.com>).

**Sans API FedaPay (démo / Postman)** : `FEDAPAY_MOCK=true`  
→ `POST /api/v1/abonnement/initier` ne appelle pas FedaPay ; réponse avec `data.mock: true` et `url_paiement: null`.  
→ Ensuite **`POST /api/v1/abonnement/finaliser-mock`** (Bearer requis) pour créer l’abonnement comme si le paiement avait réussi.  
**Ne pas activer `FEDAPAY_MOCK` en production.**

Sans clé **et** sans mock : `initier` répond **503** avec message explicite.

Le contexte réel FedaPay est stocké en **cache** (`fedapay_pending:{transaction_id}`) + **session** à l’initiation.

---

*Dernière mise à jour du document : mars 2026 — Sprints indicateurs, dashboard, rapports PDF & FedaPay.*
