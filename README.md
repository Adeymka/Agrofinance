# AgroFinance+

Application web et mobile de gestion financière agricole pour les exploitants du Bénin et d'Afrique de l'Ouest.

## Fonctionnalités

- **Authentification** par numéro de téléphone + OTP (Vonage SMS) + code PIN bcrypt
- **Gestion des exploitations** (multi-exploitation selon le plan)
- **Activités agricoles** (campagnes, élevage, maraîchage, transformation, mixte)
- **Transactions** (recettes / dépenses, catégories, nature fixe/variable)
- **Indicateurs financiers agricoles** — PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR (méthode FSA)
- **Rapports PDF chiffrés** (génération asynchrone via Job, lien de partage 72h)
- **Abonnements** — Gratuit / Essentielle / Pro / Coopérative (paiement FedaPay)
- **Dashboard Web** glassmorphisme desktop + dark mobile
- **PWA** (Service Worker, manifest, mode hors ligne)
- **API REST** complète (Sanctum Bearer)
- **Centre d'aide** avec articles et FAQ
- **Pages marketing** — accueil, à propos, contact

---

## Prérequis

| Outil | Version |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| MySQL / MariaDB | 8.0+ |
| Node.js | 20+ |
| XAMPP (local) | 8.2 |

---

## Installation locale (XAMPP)

```bash
# 1. Cloner le dépôt dans htdocs
# git clone ... C:\xampp1\htdocs\agrofinanceplus

# 2. Installer les dépendances PHP
C:\xampp1\php\php.exe composer.phar install

# 3. Copier et configurer le fichier d'environnement
copy .env.example .env
# Éditer .env : DB_DATABASE, VONAGE_*, FEDAPAY_* ou FEDAPAY_MOCK=true

# 4. Générer la clé d'application
C:\xampp1\php\php.exe artisan key:generate

# 5. Migrer la base de données
C:\xampp1\php\php.exe artisan migrate --seed

# 6. Compiler les assets
npm install
npm run dev

# 7. Optionnel : lancer le worker de queue (PDF asynchrone)
C:\xampp1\php\php.exe artisan queue:work --queue=rapports
```

**URL locale :** `http://localhost/agrofinanceplus/public`  
**URL API :** `http://localhost/agrofinanceplus/public/api`

---

## Variables d'environnement clés

| Variable | Description |
|---|---|
| `APP_ENV` | `local` ou `production` |
| `FEDAPAY_MOCK=true` | Mode simulation (dev sans clés FedaPay) |
| `FEDAPAY_SECRET_KEY` | Clé secrète FedaPay (sandbox ou live) |
| `VONAGE_API_KEY` | Clé Vonage pour les SMS OTP |
| `OTP_DEBUG_LOG=true` | Log OTP dans `storage/logs/laravel.log` (local seulement) |
| `METRICS_TOKEN` | Jeton pour l'endpoint `GET /metrics` |
| `QUEUE_CONNECTION=database` | File d'attente (utiliser `redis` en production) |

Consulter `.env.example` pour le développement local et `.env.production.example` pour la production.

---

## Architecture

```
app/
├── Http/Controllers/
│   ├── Api/          # Contrôleurs API REST (Sanctum)
│   └── Web/          # Contrôleurs Web (Blade + session)
├── Services/
│   ├── AbonnementService.php     # Logique abonnement + FedaPay
│   ├── DashboardService.php      # Logique métier tableau de bord
│   ├── FinancialIndicatorsService.php  # Calcul indicateurs FSA
│   ├── OtpService.php            # OTP + PIN creation tokens
│   └── RapportService.php        # Création Rapport + dispatch Job
├── Models/           # Eloquent : User, Exploitation, Activite, Transaction, Rapport, Abonnement
├── Jobs/             # GenerateRapportPdfJob (chiffrée en AES-256-CBC)
└── Enums/            # IndicateurFinancier (acronymes FSA)
resources/views/      # Blade (layouts: app-desktop, app-mobile, app-public)
```

---

## Queue workers (production) — #25

Les rapports PDF sont générés de façon asynchrone via Laravel Queue (`rapports`).  
En production, configurer **Supervisor** pour maintenir les workers :

```ini
; /etc/supervisor/conf.d/agrofinance-worker.conf
[program:agrofinance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agrofinanceplus/artisan queue:work redis --queue=rapports --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/agrofinance-worker.log
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start agrofinance-worker:*
```

---

## Déploiement production

Voir `.env.production.example` pour la configuration complète.  
Étapes essentielles :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
```

**Stack recommandée :** Nginx + PHP-FPM + Redis (sessions, cache, queues) + S3 (PDFs).

---

## Tests

```bash
# Tests PHPUnit
php artisan test

# Vérification des routes
php artisan route:list

# Indicateurs (local)
php artisan tinker
```

---

## Endpoints API principaux

| Méthode | Route | Description |
|---|---|---|
| `POST` | `/api/auth/connexion` | Connexion (téléphone + PIN) |
| `GET` | `/api/exploitations` | Liste des exploitations |
| `GET` | `/api/activites` | Liste des activités |
| `POST` | `/api/transactions` | Saisir des transactions |
| `POST` | `/api/rapports/generer` | Générer un rapport PDF |
| `POST` | `/api/abonnement/initier` | Initier un paiement FedaPay |
| `GET` | `/health` | Health check (BDD, cache, stockage) |

Voir la collection Postman dans `docs/` pour la liste exhaustive.

---

## Sécurité

- Rate limiting sur toutes les routes sensibles (OTP : 5/15min, PIN : 10/5min)
- PDFs chiffrés en AES-256-CBC (Crypt::encryptString)
- Sessions chiffrées et cookie sécurisé en production
- FEDAPAY_MOCK bloqué automatiquement en production
- Expiration obligatoire sur les liens de partage PDF (72h par défaut)

---

## Abonnements

| Plan | Tarif | Exploitations | PDF |
|---|---|---|---|
| Gratuit | 0 FCFA | 1 | Non |
| Essentielle | 1 500 FCFA/mois | 1 | Oui |
| Pro | 5 000 FCFA/mois | 5 | Oui + dossier crédit |
| Coopérative | 8 000 FCFA/mois | Illimité | Oui + dossier crédit |

---

## Licence

Projet propriétaire — © AgroFinance+ 2026
