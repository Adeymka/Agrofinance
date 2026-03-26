# Liste des mises à jour et corrections — rapport vs `audit.md`

Document de synthèse : alignement entre les recommandations de **`audit.md`** (toutes sections) et les **modifications réalisées** dans le dépôt. Les niveaux de criticité reprennent la terminologie de l’audit (🔴 critique, 🟠 majeur, 🟡 mineur).

**Légende de statut**

| Statut | Signification |
|--------|----------------|
| **Corrigé** | Implémenté dans le code (ou config) de ce dépôt |
| **Partiel** | Partiellement adressé ou solution alternative à celle préconisée |
| **Non traité** | Pas encore implémenté ; toujours d’actualité selon l’audit |
| **Doc / env** | Principalement documentation ou variables d’environnement |

---

## 1. Performance et base de données (audit § 2.4, Axes 5–7, 9)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| `FinancialIndicatorsService::evolutionMensuelle()` — requêtes multiples / logique coûteuse | 🔴 | **Corrigé** | Agrégation SQL mensuelle + cache avec invalidation (`FinancialIndicatorsService`) |
| `calculer()` — chargement puis filtrage en mémoire | 🔴 | **Corrigé** | Agrégats SQL (`SUM` / `CASE`) + cache versionné |
| N+1 indicateurs (`ActiviteController`, `DashboardController` API) | 🔴 | **Corrigé** | `calculerPourActivites()` / passage d’`Exploitation` déjà chargée |
| Absence d’index sur `transactions` / filtres fréquents | 🔴 | **Corrigé** | Migration `2026_03_26_210000_add_performance_indexes.php` |
| Index `activites` (exploitation + statut) | 🟠 | **Corrigé** | Même migration |
| Aucun cache sur les indicateurs | 🔴 | **Corrigé** | `Cache::remember` (~15 min) + `invalidateForActivity()` sur mutations |
| Cache driver BDD limité en prod | 🟡 | **Non traité** | L’audit recommande Redis ; mention dans `README.md` (section hardening) |
| `$transaction->fresh()` inutile après update | 🟡 | **Corrigé** | Suppression côté API `TransactionController` |
| Génération PDF synchrone bloquante | 🔴 | **Corrigé** | Job `GenerateRapportPdfJob` ; synchro en `local`/`testing`, file `rapports` sinon |
| Rate limiting API métier | 🔴 | **Corrigé** | Groupes `throttle` dans `routes/api.php` (lecture, PDF, création transactions) |

**Fichiers principaux** : `app/Services/FinancialIndicatorsService.php`, `app/Http/Controllers/Api/DashboardController.php`, `app/Http/Controllers/Web/ActiviteController.php`, `app/Http/Controllers/Api/TransactionController.php`, `app/Http/Controllers/Web/TransactionController.php`, `database/migrations/2026_03_26_210000_add_performance_indexes.php`, `app/Jobs/GenerateRapportPdfJob.php`, `routes/api.php`.

---

## 2. Sécurité applicative (audit § 2.3, A02/A05/A07/A09)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| Rate limiting connexion PIN (API / Web) | 🔴 | **Corrigé** | `RateLimiter` dans `Api\Auth\ConnexionController`, `Web\Auth\ConnexionController` |
| PIN 4–6 chiffres + validation | 🟠 | **Corrigé** | `digits_between:4,6` ; vues auth / profil (maxlength, libellés) |
| Renvoi / vérification OTP — brute force | 🟠 | **Corrigé** | Rate limit sur `VerificationOtpController` ; `Web\Auth\OtpController::renvoyer` |
| Création de PIN API sans preuve OTP | 🔴 | **Corrigé** | `pin_creation_token` (`OtpService`) + `otp_token` obligatoire dans `Api\Auth\PinController` |
| OTP loggé en clair | 🔴 | **Partiel** | Log conditionnel : `APP_ENV=local` **et** `OTP_DEBUG_LOG=true` (`OtpService`) |
| Session : `SESSION_ENCRYPT`, `SESSION_SECURE_COOKIE` | 🟠 | **Corrigé** | Défauts conditionnés au mode production dans `config/session.php` ; `.env.example` |
| `APP_DEBUG` par défaut | 🟠 | **Doc / env** | `.env.example` orienté prod (`APP_DEBUG=false`) |
| Route partage PDF `GET /partage/{token}` — expiration NULL = accès illimité | 🔴 | **Corrigé** | `Web\RapportController::partager` : expiration obligatoire ; `Rapport::creating` défaut 72 h ; migration backfill `2026_03_26_230000_backfill_rapport_lien_expire_le.php` |
| PDF stockés en clair sur disque | 🟠 | **Corrigé** | Chiffrement `Crypt::encryptString` à l’enregistrement ; déchiffrement à la lecture + fallback anciens fichiers (`Api` / `Web` `RapportController`, job PDF) |
| `FEDAPAY_MOCK` risque en production | 🟠 | **Corrigé** | `AppServiceProvider::boot` : exception si `production` + `FEDAPAY_MOCK` actif |
| Messages d’erreur FedaPay trop verbeux / logs sensibles | 🟠 | **Corrigé** | Messages utilisateur génériques ; logs sans données tierces sensibles (`Api` / `Web` `AbonnementController`) |

**Fichiers principaux** : contrôleurs `app/Http/Controllers/Api/Auth/*`, `app/Http/Controllers/Web/Auth/*`, `app/Http/Controllers/Web/ProfilController.php`, `app/Services/OtpService.php`, `config/session.php`, `.env.example`, `app/Http/Controllers/Web/RapportController.php`, `app/Models/Rapport.php`, `app/Providers/AppServiceProvider.php`, `tests/Feature/Auth/AuthFlowTest.php`.

---

## 3. Intégrations externes — FedaPay & Vonage (audit § 2.4.5, Axe 8, dépendances)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| FedaPay : pas de timeout HTTP | 🔴 | **Corrigé** | `App\Support\FedaPayHttpConfig` → `CurlClient::setTimeout` / `setConnectTimeout` ; `config('services.fedapay.*_timeout_seconds')` ; appel après config clé/env. dans les contrôleurs abonnement |
| FedaPay : pas de retry | 🟠 | **Corrigé** | `appelerFedaPayAvecRetry()` avec backoff (API + Web `AbonnementController`) |
| Vonage : non déclaré dans Composer | 🔴 | **Corrigé** | `vonage/client` dans `composer.json` (contrainte `^4.0`) |
| Vonage : pas de timeout | 🔴 | **Corrigé** | `GuzzleHttp\Client` avec timeouts depuis `config/services.php` (`VONAGE_HTTP_*`) |
| Vonage : pas de retry | 🟠 | **Corrigé** | Boucle avec backoff dans `OtpService::envoyerSMS` |
| SDK FedaPay « pré-stable » | 🟡 | **Non traité** | Version toujours `fedapay/fedapay-php ^0.4.7` — suivi des releases FedaPay à prévoir |

**Fichiers** : `app/Support/FedaPayHttpConfig.php`, `config/services.php`, `app/Http/Controllers/Api/AbonnementController.php`, `app/Http/Controllers/Web/AbonnementController.php`, `app/Services/OtpService.php`, `composer.json`, `.env.example`.

---

## 4. Concurrence, idempotence, transactions (audit Axe 3, batch transactions)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| Race callback FedaPay — double activation | 🔴 | **Partiel** | Contrainte **unique** `ref_fedapay` + gestion `QueryException` dans `AbonnementService::activer()` ; migration `2026_03_26_220000_add_unique_ref_fedapay_to_abonnements.php`. L’audit suggérait aussi `Cache::lock` — non ajouté en plus de la contrainte BDD |
| Batch `TransactionController::store()` non atomique | 🔴 | **Corrigé** | `DB::transaction()` autour de la boucle de création (API) |

**Fichiers** : `app/Services/AbonnementService.php`, `database/migrations/2026_03_26_220000_add_unique_ref_fedapay_to_abonnements.php`, `app/Http/Controllers/Api/TransactionController.php`.

---

## 5. Observabilité et ops applicatives (audit § 2.7.4, Axe 10, checklist prod)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| Health check étendu (BDD, cache, stockage) | 🟠 | **Corrigé** | `GET /health` dans `routes/web.php` |
| Endpoint `/metrics` (Prometheus) | 🔴 | **Corrigé** | `GET /metrics` — métriques minimales ; protection optionnelle par `METRICS_TOKEN` |
| APM / Sentry / Telescope prod | 🔴 | **Non traité** | Non intégré (hors périmètre actuel) |
| Logs centralisés / alertes | 🔴 | **Non traité** | Toujours à prévoir en infra |
| Checklist prod (Redis, backups, CI, Docker…) | 🔴 | **Partiel** | `README.md` — section **Production Hardening** ; pas de Dockerfile / workflow CI dans le dépôt |

**Fichiers** : `routes/web.php`, `config/services.php`, `.env.example`.

---

## 6. Architecture, qualité de code, DRY (audit § 2.1, 2.2)

| Thème (audit) | Niveau | Statut | Réalisation |
|---------------|--------|--------|-------------|
| Duplication logique FedaPay API / Web | 🟠 | **Non traité** | Toujours deux contrôleurs ; retry/timeout factorisés partiellement via helpers |
| `DashboardController` Web trop volumineux | 🟠 | **Non traité** |
| Absence de versioning API | 🟡 | **Non traité** |
| Web mono-exploitation | 🔴 | **Non traité** | Comportement produit / refonte d’ampleur |
| Sessions + fichiers locaux = mono-serveur | 🔴 | **Non traité** | Documenté dans `README` (Redis / S3 recommandés) |
| README Laravel par défaut | 🔴 | **Partiel** | Section hardening ajoutée ; pas de réécriture complète du README |
| Documentation OpenAPI / Postman | 🔴 | **Non traité** |
| Dockerfile / CI/CD | 🔴 | **Non traité** |
| Stratégie de sauvegarde | 🔴 | **Non traité** |

---

## 7. Tests et validation

| Sujet | Statut |
|--------|--------|
| Tests automatisés (`php artisan test`) après `composer install` | **À exécuter** par l’équipe — dépend de l’environnement |
| `tests/Feature/Auth/AuthFlowTest.php` | **Mis à jour** pour le flux `pin_creation_token` |

---

## 8. Index des fichiers touchés (vue d’ensemble)

| Fichier / zone | Type de changement |
|----------------|-------------------|
| `database/migrations/2026_03_26_210000_add_performance_indexes.php` | Créé — index performance |
| `database/migrations/2026_03_26_220000_add_unique_ref_fedapay_to_abonnements.php` | Créé — idempotence FedaPay |
| `database/migrations/2026_03_26_230000_backfill_rapport_lien_expire_le.php` | Créé — données liens partage |
| `app/Services/FinancialIndicatorsService.php` | Perf + cache + SQL |
| `app/Services/AbonnementService.php` | Doublons `ref_fedapay` |
| `app/Services/OtpService.php` | OTP, Vonage, retry, timeouts, token PIN |
| `app/Support/FedaPayHttpConfig.php` | Créé — timeouts FedaPay |
| `app/Jobs/GenerateRapportPdfJob.php` | Créé — PDF async |
| `app/Models/Rapport.php` | Expiration par défaut à la création |
| `app/Providers/AppServiceProvider.php` | Garde `FEDAPAY_MOCK` prod |
| `app/Http/Controllers/Api/*.php` | Dashboard, Transaction, Rapport, Abonnement, Auth |
| `app/Http/Controllers/Web/*.php` | Activite, Transaction, Rapport, Abonnement, Auth, Profil |
| `config/services.php` | FedaPay, Vonage, metrics |
| `config/session.php` | Chiffrement / cookie sécurisé |
| `routes/api.php` | Throttle |
| `routes/web.php` | `/health`, `/metrics` |
| `resources/views/auth/*.blade.php`, `profil/index.blade.php` | PIN 4–6 |
| `.env.example` | Sécurité, timeouts, Vonage, metrics |
| `README.md` | Section Production Hardening |
| `composer.json` | `vonage/client ^4.0` |
| `tests/Feature/Auth/AuthFlowTest.php` | API création PIN |

---

## 9. Synthèse

- Les points **critiques** portant sur **performances (requêtes, index, cache, PDF async)**, **sécurité auth (rate limits, OTP/PIN)**, **intégrations (timeouts, retry, dépendance Vonage)**, **intégrité (transaction batch, unicité FedaPay, partage PDF)**, et **observabilité minimale (`/health`, `/metrics`)** sont **largement couverts** par les changements listés ci-dessus.
- Restent **hors code** ou **partiels** : **scalabilité horizontale** (Redis, stockage objet), **CI/CD**, **Docker**, **OpenAPI**, **APM/Sentry**, **backups**, **refonte** de certains contrôleurs et du **README** complet, ainsi que l’**évolution du SDK FedaPay** si une version stable majeure est publiée.

*Document généré pour cartographier les corrections par rapport à l’intégralité des thèmes abordés dans `audit.md`.*
