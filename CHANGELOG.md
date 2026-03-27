# Changelog — AgroFinance+

Tous les changements notables de ce projet sont documentes dans ce fichier.

Format inspire de [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/)
et le projet respecte [Semantic Versioning](https://semver.org/lang/fr/).

---

## [Unreleased]

### Ajoute
- `RapportService` : centralise la creation Rapport + dispatch Job PDF (#2)
- `app/Enums/IndicateurFinancier.php` : enum des 10 indicateurs FSA avec libelles (#31)
- `.env.production.example` : template de deploiement production avec Redis
- Scope Eloquent `Activite::pourUtilisateur()` : remplace le whereHas repete (#17)
- Endpoint `GET /health` : verifications BDD, cache, stockage
- Endpoint `GET /metrics` : metriques Prometheus minimales (protege par METRICS_TOKEN)
- Job `GenerateRapportPdfJob` avec timeout 120s, 3 retries, backoff exponentiel

### Ameliore
- `DashboardController` Web : support multi-exploitation via `?exploitation_id=X` (#1)
- `AbonnementService` : methodes `initierPaiementFedaPay()` et `traiterCallbackFedaPay()` avec `Cache::lock()` (#3, #4, #14)
- `AbonnementService::activer()` : duree calculee avec match() explicite (#18)
- `Api/AbonnementController` : plus de `Session::put()` dans les routes API (#5)
- `InscriptionController` : message OTP conditionnel (masque le chemin log en production) (#6)
- `InscriptionController::normaliserTelephone()` : branche ternaire morte supprimee (#21)
- `Api/ActiviteController::cloturer()` et `abandonner()` : `lockForUpdate()` dans `DB::transaction()` (#13)
- `GenerateRapportPdfJob::handle()` : try/catch autour de `Storage::put()` (#20)
- `package.json` : axios deplace de devDependencies vers dependencies (#34)
- `.env.example` : `APP_LOCALE=fr`, `APP_TIMEZONE=Africa/Porto-Novo`, URL XAMPP (#26, #27)

### Securite
- Rate limiting connexion PIN (10 essais / 5 minutes)
- Rate limiting OTP (5 essais / 15 minutes)
- Expiration obligatoire sur les liens de partage PDF (defaut 72h)
- Chiffrement AES-256-CBC des PDFs sur disque (Crypt::encryptString)
- Garde `FEDAPAY_MOCK` bloque en production (AppServiceProvider)
- `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` en production
- Retries avec backoff exponentiel sur les appels FedaPay et Vonage

### Corrige
- N+1 queries sur le dashboard : `calculerPourActivites()` + eager loading
- `evolutionMensuelle()` : une seule requete SQL agregee (vs 12 requetes PHP)
- Index MySQL sur `transactions(activite_id, date_transaction)` et `activites(exploitation_id, statut)`
- `ref_fedapay` : contrainte UNIQUE pour eviter la double activation
- Batch transactions : `DB::transaction()` pour l'atomicite

---

## [0.5.0] — 2026-03-24

### Ajoute
- Sprint 5 : API Abonnements FedaPay mock, generation PDF, partage public avec expiration
- Centre d'aide : seeders `help:seed-premiers-pas` et `help:seed-campagnes`
- Pages marketing : accueil, a-propos, contact (layout `app-public`)
- PWA : manifest.json, sw.js (Cache First assets, Network First HTML, Network Only API)
- Route `/offline` pour le fallback Service Worker

## [0.4.0] — 2026-03-10

### Ajoute
- Dashboard desktop glassmorphisme avec sidebar 260px/72px
- Dashboard mobile dark glassmorphisme avec bottom dock
- Detection plateforme (middleware `DetectPlatform`)
- Carrousel hebdomadaire fonds d'ecran (`WeeklyBackgroundImages`)

## [0.3.0] — 2026-02-xx

### Ajoute
- API REST Sanctum : exploitations, activites, transactions, indicateurs
- `FinancialIndicatorsService` : calcul PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR
- `AbonnementService` : logique plan/duree/droits

## [0.1.0] — 2026-01-xx

### Ajoute
- Authentification par telephone + code OTP (Vonage) + PIN bcrypt
- Gestion exploitations, activites, transactions
- Parcours inscription complet en 4 etapes

---

[Unreleased]: https://github.com/Adeymka/Agrofinance/compare/v0.5.0...HEAD
[0.5.0]: https://github.com/Adeymka/Agrofinance/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/Adeymka/Agrofinance/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/Adeymka/Agrofinance/compare/v0.1.0...v0.3.0
