# AgroFinance+ — Logique fonctionnelle (technique)

Document de référence sur **l’architecture applicative**, les **parcours**, les **routes**, les **modules** et les **intégrations**. Complète `LOGIQUE_METIER.md`.

---

## 1. Stack et principes

| Couche | Technologie |
|--------|-------------|
| Backend | **Laravel 11**, PHP 8.2+ |
| Auth API | **Laravel Sanctum** (tokens Bearer) |
| Auth web | Session + garde `auth` ; identifiant utilisateur pour la session basé sur le **téléphone** (voir `User::getAuthIdentifierName`) |
| Front web | **Blade**, **Vite**, **Tailwind CSS** (v4 via `@import "tailwindcss"`) |
| Paiement | **FedaPay** (SDK), mode **mock** via `.env` |
| PDF | **DomPDF** (Barryvdh) |

**Principes observés dans le code :**

- Logique métier d’abonnement centralisée dans **`App\Services\AbonnementService`**.
- Indicateurs financiers dans **`App\Services\FinancialIndicatorsService`**.
- OTP dans **`App\Services\OtpService`**.
- Fonds d’écran hebdomadaires : **`App\Support\WeeklyBackgroundImages`**.
- Réponses API d’erreur harmonisées pour les requêtes « API » (y compris sous **XAMPP** `/public/api/v1/...`) : détection du segment `api` dans **`bootstrap/app.php`**.

---

## 2. Point d’entrée et routage

### 2.1 Web (`routes/web.php`)

| Méthode / URL | Middleware | Fonction |
|---------------|------------|----------|
| `GET /` | — | Redirige vers `dashboard` si connecté, sinon `connexion` |
| `GET/POST /connexion`, inscription, OTP, PIN | `guest` | Parcours d’accès invité |
| `GET /partage/{token}` | — | Téléchargement / vue rapport partagé (`RapportController@partager`) |
| `POST /deconnexion` | `auth` | Déconnexion session |
| `GET /abonnement`, `POST initier`, `finaliser-mock`, `callback` | `auth` | Abonnement et paiement |
| `GET/PUT /profil` | `auth` | Profil |
| `GET /dashboard`, exploitations, activités, transactions, rapports | `auth`, **`subscribed`** | Cœur applicatif |

**Alias middleware** (`bootstrap/app.php`) :

- `auth` → `Authenticate`
- `guest` → `RedirectIfAuthenticated`
- `subscribed` → **`VerifierAbonnement`**

### 2.2 API (`routes/api.php`)

Préfixes Laravel **`/api`** + groupe applicatif **`/v1`** → URLs **`/api/v1/...`**.

| Zone | Middleware | Contenu |
|------|------------|---------|
| `POST /api/v1/auth/*` | — | Inscription, OTP, PIN, connexion |
| `GET/POST /api/v1/auth/me`, déconnexion | `auth:sanctum` | Utilisateur connecté par token |
| `POST /api/v1/abonnement/initier`, `finaliser-mock` | `auth:sanctum` | Initiation paiement (sans `subscribed`) |
| Reste (dashboard, exploitations, activités, transactions, indicateurs, rapports) | `auth:sanctum`, **`subscribed`** | Données métier |
| `GET /api/v1/abonnement/callback` | — | Callback FedaPay (sans Bearer) — à déclarer dans FedaPay pour les paiements initiés via API |

---

## 3. Middleware `VerifierAbonnement`

Fichier : `app/Http/Middleware/VerifierAbonnement.php`.

- Si pas d’utilisateur → redirection **connexion** (web).
- **Routes toujours autorisées** sans abonnement actif :  
  `abonnement`, `abonnement.initier`, `abonnement.callback`, `abonnement.finaliser-mock`, `profil`, `profil.update`, `deconnexion`.
- Préfixe **`api/v1/abonnement/*`** et **`api/v1/auth/*`** : laissés passer (initier paiement, auth).
- Sinon : si **`AbonnementService::estActif`** est faux → **403 JSON** (API) ou **redirect** vers `abonnement` avec message (web).

---

## 4. Modules fonctionnels

### 4.1 Authentification web

Contrôleurs sous `App\Http\Controllers\Web\Auth\` :

- **Connexion** : formulaire téléphone + PIN ; session + éventuellement **token API** en session pour le JavaScript du dashboard (`meta api-token`).
- **Inscription** → **OTP** → **PIN** → redirection vers connexion ou dashboard selon implémentation.

Layout : **`layouts/app-auth.blade.php`** (fond fixe Unsplash).

### 4.2 Authentification API

Contrôleurs sous `App\Http\Controllers\Api\Auth\` :

- `POST /api/v1/auth/inscription`, `verification-otp`, `renvoyer-otp`, `creer-pin`, `connexion`.
- `GET /api/v1/auth/me` : profil avec token.
- `POST /api/v1/auth/deconnexion` : révoque le token courant.

Format JSON typique : `{ "succes": bool, "message": "...", "data": { ... } }`.

### 4.3 Abonnement (web + API)

- **Web** : `Web\AbonnementController` — liste des plans, `POST initier` (validation `mensuel|annuel|cooperative`), cache **pending** FedaPay ou mock, `finaliserMock`, **callback** redirection navigateur.
- **API** : `Api\AbonnementController` — même logique en JSON.

Service : **`AbonnementService`** — `montantFacturation`, `planPourBase`, `activer` (clôture des anciens abonnements actifs, création d’une ligne, durée en jours).

### 4.4 Exploitations

- **Web** : création guidée ; messages si **limite** atteinte (`messageLimiteExploitations`).
- **API** : CRUD partiel (liste, création, détail, mise à jour) avec **user_id** = `auth()->user()->id`.

### 4.5 Activités

- Liées à une exploitation ; statuts **`en_cours`** / **`termine`** / **`abandonne`** (modèle `Activite::STATUT_*`).
- **Clôture** : route dédiée (`cloturer`) pour passer une campagne en terminée.
- **Alertes budget** : méthode **`alerteBudget()`** sur le modèle (seuils 70 %, 90 %, 100 %).

### 4.6 Transactions

- Création / édition / suppression avec catégories issues de **`TransactionCategories`**.
- Types `depense` / `recette`, nature `fixe` / `variable` pour les dépenses.

### 4.7 Dashboard

- **Web** (`DashboardController`) : première exploitation avec activités actives ; **`FinancialIndicatorsService::calculerExploitation`** ; cartes par activité ; dernières transactions ; **Chart.js** si token session + activité (route API évolution).
- **API** (`Api\DashboardController`) : **toutes** les exploitations avec activités actives ; consolidé global ; alertes budget ; 10 dernières transactions.

### 4.8 Indicateurs (`IndicateurController`)

- Routes **`/api/indicateurs/activite/{id}`**, **`.../evolution`**, **`/exploitation/{id}`** pour graphiques et agrégats (ordre des routes : **évolution** avant la route générique `{id}`).

### 4.9 Rapports PDF

- Génération : **`RapportController`** (web + API) ; stockage fichier + enregistrement **`rapports`**.
- Contrôle d’accès : trait **`HandlesPdfAbonnement`** — refuse si le plan ne permet pas le **type** demandé (`campagne` vs `dossier_credit`).
- **Téléchargement** : vérifie le **type** du rapport existant vs droits.
- **Partage** : `GET /partage/{token}` — **sans auth**, contrôle expiration du token.

---

## 5. Services clés (résumé technique)

| Service | Responsabilité |
|---------|----------------|
| `AbonnementService` | Normalisation plan, droits PDF / multi-exploitations, quotas, activation après paiement, infos pour la sidebar |
| `FinancialIndicatorsService` | `calculer`, `calculerExploitation`, `evolutionMensuelle` |
| `OtpService` | Génération, envoi SMS (ou log), vérification, anti-bruteforce |

---

## 6. Modèles Eloquent (liens)

- `User` : `exploitations`, `abonnements`, `abonnementActif()` ; **`getAuthIdentifierName` = `telephone`**.
- `Exploitation` : `activites`, **`activitesActives`** (scope `statut = en_cours`), `rapports`.
- `Activite` : `exploitation`, `transactions`.
- `Transaction` : `activite`.
- `Abonnement` : utilisateur.
- `Rapport` : exploitation.

---

## 7. UI desktop connecté

- Layout **`layouts/app-desktop.blade.php`** : sidebar réductible (260 px / 72 px, **`localStorage` `sidebarReduced`**), fond en couches avec **rotation JS** d’URLs fournies par **`WeeklyBackgroundImages::weeklySlideUrls()`**.
- Typographie : **Space Grotesk** + **Inter** (Google Fonts + `resources/css/app.css`).

---

## 8. Fichiers et stockage

- **Rapports PDF** : chemins en base ; fichiers sous **`storage/app/rapports/`** (pas d’exposition directe depuis `public` sans contrôleur).
- **Images de fond** : **`public/images/`** — classification par nom de fichier pour la rotation hebdomadaire.

---

## 9. Configuration notable

- **`config/services.php`** : clés **FedaPay**, flag **mock** (`FEDAPAY_MOCK`).
- **`config/database.php`** : en cas d’erreur de **collation** MySQL (`utf8mb4_0900_ai_ci`), aligner sur une collation supportée par le serveur.
- **`.env`** : `APP_URL`, base de données, FedaPay, `FEDAPAY_MOCK`.

---

## 10. Tests automatisés

- Répertoire **`tests/`** : tests Feature (auth, abonnement mock, rapports) ; certains tests peuvent être **ignorés** selon le driver SQLite vs MySQL (schéma `abonnements.plan`).

---

## 11. Dépendances externes

- **FedaPay** : création de transaction, redirection utilisateur, callback HTTP GET.
- **Vonage** (ou équivalent) : envoi SMS en production pour OTP — log local en développement.

---

## 12. Parcours utilisateur résumé (flux)

1. **Invité** : connexion OU inscription → OTP → PIN → connexion.
2. **Connecté sans abonnement actif** : accès **abonnement** / **profil** ; souscription (ou mock).
3. **Connecté avec abonnement actif** : **dashboard**, **exploitations**, **activités**, **transactions**, **rapports**.
4. **API mobile / PWA** : même logique avec **Bearer Sanctum** ; module **subscribed** pour les données métier.

---

*Document généré pour la documentation interne ; à maintenir lors des évolutions de routes ou des services.*
