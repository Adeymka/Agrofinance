# Sprint 5 — Rapports PDF & paiement Mobile Money (FedaPay)

> Détail API : [`API_CLIENT.md`](./API_CLIENT.md) (section Sprint 5).

---

## Rapport de livraison (mars 2026)

### Périmètre livré

| Domaine | Détail |
|---------|--------|
| PDF | DomPDF, vues `resources/views/rapports/pdf/campagne.blade.php` et `dossier-credit.blade.php` |
| Stockage | `storage/app/rapports/` (disque `local`), **pas** de `storage:link` |
| API rapports | `RapportController` — génération, liste, téléchargement sécurisé par `auth()->user()->id` |
| Partage public | `PartageController` — route web `GET /partage/{token}`, expiration 72h, PDF inline ou JSON 404/410 |
| Abonnement | `AbonnementController` — `initier`, `callback` FedaPay public, **`finaliser-mock`** si `FEDAPAY_MOCK=true` |
| Config | `config/dompdf.php`, `config/services.php` (`fedapay`), `.env.example` |
| Tests auto | `tests/Feature/Sprint5Test.php` (PHPUnit) |
| Doc client | `API_CLIENT.md` — section Sprint 5 |

### Tests manuels validés (session de recette)

| # | Scénario | Résultat |
|---|-----------|----------|
| 1 | `GET /api/v1/rapports` sans Bearer | **401** |
| 2 | `POST /api/v1/rapports/generer` (activité autorisée, période) | **201**, `lien_token`, `lien_partage`, indicateurs |
| 3 | `GET /api/v1/rapports` avec Bearer | **200**, liste |
| 4 | `GET /api/v1/rapports/{id}/telecharger` | PDF (propriétaire) |
| 5 | `GET /public/partage/{lien_token}` sans auth | **PDF inline** (token exact depuis `generer`) |
| 6 | `lien_expire_le` passé en BDD puis même URL | **410** JSON (expiration) |
| 7a | `FEDAPAY_MOCK=true` → `POST /abonnement/initier` | **200**, `data.mock: true`, `url_paiement: null` |
| 7b | `POST /abonnement/finaliser-mock` (même Bearer) | **200**, abonnement **actif** |
| — | `GET /dashboard` après 7b | `abonnement.plan: mensuel`, `statut: actif` |
| — | Remise environnement | `FEDAPAY_MOCK=false` + `config:clear` |
| 8 | `GET /public/partage/zzzz-invalide-zzzz` | **404**, `succes: false`, « Lien invalide » |

### Points d’attention

- **`APP_URL`** : doit correspondre au chemin public (ex. `http://localhost/agrofinanceplus/public`) pour les liens dans le PDF et le `callback_url` FedaPay.
- **OAuth / téléphone** : `Auth::id()` = téléphone ; partout où il y a `user_id` en base, utiliser **`auth()->user()->id`**.
- **FedaPay réel** : renseigner `FEDAPAY_SECRET_KEY` (et clés publiques), `FEDAPAY_MOCK=false` ; le callback **`GET /api/v1/abonnement/callback`** est **hors** groupe Sanctum (à déclarer dans le dashboard FedaPay pour les paiements initiés via API).
- **PHPUnit** : sur SQLite `:memory:`, le test « finaliser-mock + persistance » peut être **skipped** (enum `plan` MySQL vs SQLite) ; le flux mock **initier** est tout de même couvert.

---

## Prérequis

- `barryvdh/laravel-dompdf` et `fedapay/fedapay-php` (Composer).
- `config/dompdf.php` : `enable_remote` = true, `dpi` = 150, papier A4.
- `.env` : `FEDAPAY_SECRET_KEY`, `FEDAPAY_PUBLIC_KEY`, `FEDAPAY_ENVIRONMENT=sandbox` pour la vraie API.
- **Sans FedaPay** : `FEDAPAY_MOCK=true` + `POST .../initier` puis **`POST .../finaliser-mock`**. Ne pas utiliser en production.

## Endpoints

| Méthode | Route | Auth |
|---------|--------|------|
| POST | `/api/v1/rapports/generer` | Sanctum |
| GET | `/api/v1/rapports` | Sanctum |
| GET | `/api/v1/rapports/{id}/telecharger` | Sanctum |
| GET | `/partage/{token}` | Public (web) |
| POST | `/api/v1/abonnement/initier` | Sanctum |
| POST | `/api/v1/abonnement/finaliser-mock` | Sanctum (si `FEDAPAY_MOCK=true` uniquement) |
| GET | `/api/v1/abonnement/callback` | Public (API, hors Sanctum) |

## Critères de validation (checklist technique)

- [x] `POST /api/v1/rapports/generer` → fichier sous `storage/app/rapports/`, `lien_token`, `lien_expire_le` ≈ +72h.
- [x] `GET /partage/{token}` sans token → PDF inline si valide.
- [x] Lien expiré → **410** JSON.
- [x] Token invalide → **404** JSON.
- [ ] `POST /api/v1/abonnement/initier` avec clés FedaPay réelles → `url_paiement` (à valider quand compte sandbox disponible).
- [x] Sans clé **et** sans mock → **503** sur `initier` (message explicite).
- [x] `GET /api/v1/rapports` sans Bearer → **401**.

---

*Document équipe — Sprint 5 — mars 2026.*
