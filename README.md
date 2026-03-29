# AgroFinance+

Application web et API **Laravel 11** de suivi financier pour exploitations agricoles : activités, transactions, indicateurs (marge, RNE, etc.), rapports PDF et abonnements (FedaPay).

**Stack principale** : PHP **8.2+**, Laravel **11**, **Sanctum** (API), **DomPDF**, **Vite** + **Tailwind CSS** + **Alpine.js**, paiements **FedaPay**, SMS OTP (Vonage en production).

---

## Sommaire

1. [Prérequis](#prérequis)  
2. [Installation](#installation)  
3. [Configuration](#configuration)  
4. [Démarrage local](#démarrage-local)  
5. [Architecture](#architecture)  
6. [API REST](#api-rest)  
7. [Sécurité](#sécurité)  
8. [Déploiement & infra (S2)](#déploiement--infra-compte-rendu-s2)  
9. [Production : Supervisor (queues)](#production--supervisor-queues)  
10. [Tests](#tests)  
11. [Licence](#licence)

---

## Prérequis

- **PHP** 8.2 ou supérieur (extensions courantes : `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` si utilisé)
- **Composer** 2.x
- **Node.js** 18+ et **npm** (assets front)
- **SQLite** (développement par défaut) ou **MySQL/MariaDB** en production

---

## Installation

```bash
git clone <url-du-depot> agrofinance
cd agrofinance

composer install

cp .env.example .env
php artisan key:generate
```

**Base de données (SQLite, défaut dans `.env.example`)** :

```bash
touch database/database.sqlite
php artisan migrate
```

**Front** :

```bash
npm install
npm run build
```

Pour le développement avec rechargement des assets : `npm run dev` (Vite).

---

## Configuration

Les variables importantes sont dans **`.env`** (voir **`.env.example`**) :

| Zone | Variables (exemples) |
|------|----------------------|
| Application | `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG` (jamais `true` en prod) |
| Base | `DB_CONNECTION` (`sqlite` ou `mysql`), chemins ou identifiants MySQL |
| Session / cache | `SESSION_DRIVER`, `CACHE_STORE` (souvent `database` en local) |
| Files d’attente | `QUEUE_CONNECTION` (`database` par défaut) — voir [Supervisor](#production--supervisor-queues) |
| FedaPay | `FEDAPAY_SECRET_KEY`, `FEDAPAY_PUBLIC_KEY`, `FEDAPAY_ENVIRONMENT`, `FEDAPAY_MOCK` (**ne pas** activer le mock en production) |
| SMS (OTP) | Clés **Vonage** dans `config/services.php` si envoi réel hors environnement local |

Après modification de `.env` : `php artisan config:clear` si besoin.

---

## Démarrage local

```bash
php artisan serve
```

Dans un second terminal : `npm run dev` pour servir les assets via Vite.

- Interface web : URL de l’app (ex. `http://127.0.0.1:8000`) — routes définies dans `routes/web.php`.  
- API JSON : préfixe **`/api/v1`** — routes dans `routes/api.php` (groupe `Route::prefix('v1')`).

**Worker de queue (optionnel en local)** : si vous dispatch des jobs, lancez `php artisan queue:work` ou configurez Supervisor en production.

---

## Architecture

### Vue d’ensemble

- **Double canal** : application **web** (sessions, vues Blade, CSRF) et **API stateless** (token **Laravel Sanctum**).
- **Domaine métier** : utilisateur → **exploitations** → **activités** (campagnes) → **transactions** (recettes / dépenses) ; calculs d’**indicateurs financiers** et **rapports PDF** stockés sur disque.

### Organisation du code (indicatif)

| Emplacement | Rôle |
|-------------|------|
| `app/Http/Controllers/Web` | Pages et actions navigateur (dashboard, exploitations, transactions, rapports, abonnement web) |
| `app/Http/Controllers/Api` | Endpoints JSON sous `/api/v1` |
| `app/Services` | Logique métier partagée : `FinancialIndicatorsService`, `RapportService`, `AbonnementService`, `DashboardService`, `OtpService`, `ActiviteStatutService`, etc. |
| `app/Models` | Modèles Eloquent (`User`, `Exploitation`, `Activite`, `Transaction`, `Rapport`, `Abonnement`, …) |
| `app/Jobs` | Jobs asynchrones (ex. génération PDF) si la queue est utilisée |
| `app/Http/Middleware` | Dont `VerifierAbonnement` (alias `subscribed`) pour les zones « abonnement actif requis » |
| `resources/views` | Vues Blade ; `resources/js`, `resources/css` — build Vite |
| `routes/web.php` / `routes/api.php` | Déclaration des routes |

### Middleware et accès

- **`auth`** (web) : session utilisateur.  
- **`auth:sanctum`** (API) : token Bearer.  
- **`subscribed`** : vérifie un abonnement actif via `AbonnementService` ; en API, réponse **403** avec `code: ABONNEMENT_REQUIS` (aucune formule active, pas d’historique) ou `ABONNEMENT_EXPIRE` (période terminée) ; certaines routes (abonnement, profil, déconnexion, etc.) restent accessibles selon `VerifierAbonnement`.

### Règles métier transverses

- Portée des ressources souvent limitée au **propriétaire** (ex. `Activite::pourUtilisateur`, `whereHas` exploitation / user).  
- Les exceptions HTTP pour l’API sont harmonisées en JSON dans `bootstrap/app.php` (401, 404, 422).

---

## API REST

- **Préfixe** : toutes les routes API sont sous **`/api/v1`** (ex. `GET /api/v1/dashboard`).  
- **Authentification** : en-tête `Authorization: Bearer <token>` et `Accept: application/json` pour les routes protégées.  
- **Réponses** : souvent `{ "succes": true, "data": { ... } }` ; erreurs de validation **422** avec `errors` (voir gestion dans `bootstrap/app.php`).

### Aperçu des groupes de routes

**Sans token** — préfixe `auth` : inscription, OTP, création PIN, connexion.

**Avec token Sanctum** : déconnexion token, `GET /auth/me`, initiation abonnement, finalisation mock.

**Avec token + abonnement actif (`subscribed`)** : exploitations, activités (dont clôture / abandon), transactions, indicateurs, dashboard, rapports (liste, génération, téléchargement).

**Callback FedaPay** : `GET /api/v1/abonnement/callback` (sans Sanctum — redirection navigateur après paiement initié via **API**). Le flux **web** utilise `GET /abonnement/callback` (session). Déclarer l’URL correspondante dans le dashboard FedaPay ; **`APP_URL`** doit matcher l’URL publique (voir **`.env.example`**).

**Clients (Postman, app mobile, scripts)** : utiliser la base **`{{APP_URL}}/api/v1`** (sans slash final) pour toutes les routes JSON — variable d’environnement type `base_url` dans Postman. Guide : **[`docs/POSTMAN.md`](docs/POSTMAN.md)**.

La documentation détaillée des corps de requêtes, champs et cas limites se trouve dans **[`docs/API_CLIENT.md`](docs/API_CLIENT.md)** (indicateurs, dashboard, rapports, FedaPay, etc.).

---

## Sécurité

- **API** : authentification par **token Sanctum** ; ne pas exposer les tokens (stockage sûr côté mobile / SPA).  
- **Web** : cookies de session ; protection **CSRF** sur les formulaires Laravel.  
- **`APP_DEBUG=false`** et **`APP_URL`** en **HTTPS** en production.  
- **Abonnement** : le middleware `subscribed` limite l’accès aux fonctionnalités payantes ; les clés **FedaPay** et secrets SMS restent uniquement côté serveur (`.env`, jamais versionnés).  
- **`FEDAPAY_MOCK`** : réservé aux environnements de test ; **désactivé** en production.  
- **OTP** : en environnement local, les codes peuvent apparaître dans les logs ; en production, s’appuyer sur l’envoi SMS réel.  
- **Partage de rapports** : liens signés par token à durée limitée (`GET /partage/{token}` côté web) — ne pas partager des URL de production publiquement sans conscience du risque.
- **Connexion** : limitation du nombre de tentatives (rate limiting) sur la route de connexion API et web — voir le détail dans `docs/SPRINT-S1-SECURITE-DONNEES.md`.

Pour signaler une vulnérabilité dans **Laravel** lui-même, voir la [politique de sécurité du framework](https://laravel.com/docs/contributions#security-vulnerabilities).

---

## Déploiement & infra (compte rendu S2)

Le passage **développement local** (XAMPP, `php artisan serve`) à la **production** est détaillé dans **`docs/SPRINT-S2-ARCHITECTURE-INFRA.md`** : checklist (HTTPS, `APP_URL`, base de données, `config:cache`, droits `storage/`, sauvegardes, worker de file d’attente), schéma d’architecture et **recette** rapide.

- **Santé applicative** : Laravel expose **`GET /up`** (déclaré dans `bootstrap/app.php`) — utile pour un load balancer ou une sonde de disponibilité.  
- **Supervisor** : modèle versionné **`docs/supervisor-worker.conf.example`** (complément de la section ci-dessous).  
- **Sprint S1 (sécurité)** : checklist production **`docs/SPRINT-S1-SECURITE-DONNEES.md`** §5 ; les **actions concrètes** sont regroupées dans le document S2 §4.

---

## Production : Supervisor (queues)

Les jobs en arrière-plan (génération PDF, etc.) ne sont traités que si un worker `queue:work` tourne en continu. En production, [Supervisor](http://supervisord.org/) maintient ce processus actif et le relance en cas de crash.

### Prérequis

1. **Connexion queue** : dans `.env`, `QUEUE_CONNECTION=database` (valeur par défaut du projet, voir `config/queue.php`).  
2. **Tables** : migrations Laravel pour `jobs` et `failed_jobs`.  
3. **Chemins** : adapter le chemin absolu vers `artisan` et l’utilisateur système (souvent `www-data` sur Debian/Ubuntu).

### Fichier de programme Supervisor

Créer un fichier du type `/etc/supervisor/conf.d/agrofinance-worker.conf` :

```ini
[program:agrofinance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agrofinance/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/agrofinance-worker.log
stopwaitsecs=3600
```

| Directive | Rôle |
|-----------|------|
| `command` | Worker sur la connexion `database` ; `--sleep` : pause si file vide ; `--tries` : tentatives par job ; `--max-time` : redémarrage périodique (fuites mémoire). |
| `user` | Compte d’exécution PHP. |
| `numprocs` | Nombre de workers (adapter à la charge). |
| `stdout_logfile` | Journal Supervisor (répertoire existant ou créé). |

Remplacez `/var/www/agrofinance` par le chemin de déploiement réel.

### Recharger Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start agrofinance-worker:*
```

Vérifier : `sudo supervisorctl status`.

### Références

- [Queues — Laravel](https://laravel.com/docs/queues)  
- [Configuration Supervisor (Laravel)](https://laravel.com/docs/queues#supervisor-configuration)

---

## Tests

```bash
php artisan test
```

( PHPUnit configuré dans le projet Laravel. )

---

## Licence

Le projet s’appuie sur le framework **Laravel**, publié sous [licence MIT](https://opensource.org/licenses/MIT). La licence du dépôt AgroFinance+ peut être précisée par les mainteneurs du projet ; en l’absence de fichier `LICENSE` dédié, se référer aux choix du dépôt source.

---

<p align="center"><a href="https://laravel.com" target="_blank">Laravel</a> — documentation officielle : <a href="https://laravel.com/docs">laravel.com/docs</a></p>
