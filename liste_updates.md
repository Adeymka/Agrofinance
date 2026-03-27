# TODO — Corrections Audit AgroFinance+

## ✅ Sprints précédents
- [x] Agrégations SQL, cache, N+1, index, rate limits, PDF chiffrés, FedaPay/Vonage timeouts+retry, health check, metrics

## ✅ Session 1 (#1–#6, #13–#14, #17–#18, #20–#21, #26–#27, #31, #34, #38–#40)
- [x] Dashboard multi-exploitation, RapportService DRY, FedaPay centralisé, Session API, OTP conditionnel, lockForUpdate, Cache::lock, scope Eloquent, durée abonnement match(), try/catch PDF, normaliserTelephone, locale/timezone/URL, Enum FSA, axios, CHANGELOG, .env.production, LOG_LEVEL prod

## ✅ Session 2 (#15, #22, #24, #25, #28)
- [x] DashboardService créé, validation unifiée, PHPDoc FinancialIndicatorsService, Supervisor doc, README complet

## ✅ Session 3 (#7, #8, #24b, #29)
- [x] **#7** Dockerfile multi-stage + docker-compose.yml (5 services) + Nginx + PHP configs
- [x] **#8** GitHub Actions CI — tests PHPUnit, PHPStan, build Docker
- [x] **#24b** PHPDoc OtpService + RapportService
- [x] **#29** `docs/API.md` — documentation complète de tous les endpoints

## 🔴 Restant — Critique
- [ ] **#9** Stratégie sauvegarde (spatie/laravel-backup)
- [ ] **#10** APM / Sentry
- [ ] **#11** Sessions + cache → Redis en prod
- [ ] **#12** PDFs → S3/Object Storage

## 🟠 Restant — Majeur
- [ ] **#16** `AbonnementService` → Gate/Policy
- [ ] **#19** Alerte budget → vérification unification complète
- [ ] **#23** Versioning API `/api/v1/...`
- [ ] **#24c** PHPDoc AbonnementService (initierPaiementFedaPay, traiterCallbackFedaPay)

## 🟡 Restant — Mineur
- [ ] **#30** Vue `alertes/index.blade.php` non routée
- [ ] **#32** `HelpArticle::rechercher()` → HelpSearchService
- [ ] **#33** `TransactionCategories` → config/BDD
- [ ] **#35** SDK FedaPay pre-1.0
- [ ] **#36** Commentaires migrations
- [ ] **#37** PHPDoc contrôleurs
- [ ] **#41** Cache → Redis en prod
- [ ] **#42** Config déploiement Nginx/PHP-FPM/SSL
