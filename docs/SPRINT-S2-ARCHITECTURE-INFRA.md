# Sprint S2 — Architecture & infrastructure

**Statut : terminé** (jalons : documentation déploiement, alignement `.env.example`, renvois README / synthèse ; pas de refonte applicative hors périmètre infra).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md` · checklist S1 `SPRINT-S1-SECURITE-DONNEES.md` §5.

Ce document est la **source de vérité** pour l’**exploitation** : passage **local (XAMPP / `artisan serve`) → production**, files d’attente, stockage, sauvegardes, observabilité, et ce qui reste **hors code** (choix d’hébergeur, DPA, IaC).

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| Documenter la structure (monolithe, API v1, services, jobs) | **Doc** — schéma texte §3 ; pas de changement d’architecture. |
| XAMPP ≠ prod (`APP_URL`, `asset()`, callbacks) | **Doc** — §4 checklist + `.env.example` commenté ; renvoi README. |
| Queues / worker | **Doc** — `QUEUE_CONNECTION=database`, job `GenerateRapportPdfJob` ; Supervisor exemple `docs/supervisor-worker.conf.example` + README. |
| Stockage & sauvegardes | **Doc** — chemins `storage/app/rapports/`, `storage/app/justificatifs/` ; procédure backup §5 (ops). |
| Observabilité (`/up`, logs) | **Constat** — `health: '/up'` dans `bootstrap/app.php` ; `LOG_LEVEL` documenté pour la prod. |
| Checklist S1 D5 opérationnelle | **Doc** — §4 relie chaque point à une action concrète (déploiement / ops). |

---

## 2. Thèmes D1–D6 — état final

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** Structure (monolithe, services, API v1) | **Fait (existant)** | Préfixe `/api/v1`, services métier, `GenerateRapportPdfJob`. *S2 : documenté dans §3.* |
| **D2** Local vs production | **Fait (doc)** | URL XAMPP typique `http://localhost/agrofinanceplus/public` ; prod : `DocumentRoot` → `public/`, HTTPS, `APP_URL` public. Voir §4. |
| **D3** Files d’attente | **Fait (doc)** | Connexion `database`, table `jobs` ; worker obligatoire en prod — §4 étapes 9–10. |
| **D4** Stockage, DB, sauvegardes | **Fait (doc)** | MySQL/MariaDB en prod ; charset documenté dans `AGENTS.md` ; PDF et justificatifs sous `storage/app/…` — §5. |
| **D5** Observabilité, `/up`, logs | **Fait (existant + doc)** | Route **`GET /up`** (Laravel health). Logs `storage/logs` ; `LOG_LEVEL=error` conseillé en prod ; monitoring externe sur `/up` — §6. |
| **D6** Institution (localisation, sous-traitants) | **Doc** | Fiche « à compléter » côté organisation (pays d’hébergement, DPA) — §8 ; pas de page produit dans ce sprint. |

---

## 3. Schéma d’architecture (vue texte)

```
[ Navigateur / PWA ] ──HTTPS──► [ Apache/Nginx → public/index.php → Laravel ]
                                      │
                    ┌─────────────────┼─────────────────┐
                    ▼                 ▼                 ▼
              [ MySQL ]        [ storage/app ]    [ jobs table ]
                    │          rapports,         ◄── queue:work
                    │          justificatifs          (Supervisor)
                    │
[ Client mobile ] ──► [ /api/v1 + Sanctum ]
```

**Règle d’équipe (D1) :** la logique métier reste dans les **services** ; les tâches longues passent par les **jobs** lorsque la queue est utilisée.

---

## 4. Checklist déploiement — local → production

À suivre dans l’ordre pour un passage **répétable** (complète la checklist **S1 §5** — sécurité).

### A. Avant la mise en ligne

1. **[ ]** Copier le code (déploiement Git ou artefact) sur le serveur ; **ne pas** versionner `.env`.
2. **[ ]** `composer install --no-dev --optimize-autoloader` (adapter selon la politique du projet).
3. **[ ]** `cp .env.example .env` puis renseigner **`APP_KEY`**, **`APP_ENV=production`**, **`APP_DEBUG=false`**.
4. **[ ]** **`APP_URL`** = URL publique exacte (**https://…**), **sans** chemin de sous-dossier type XAMPP si le vhost pointe déjà vers `public/`.
5. **[ ]** **Base de données** : `DB_*` MySQL/MariaDB ; compte applicatif à privilèges limités ; port MySQL non exposé Internet si possible.
6. **[ ]** **`php artisan migrate --force`** (après sauvegarde si base existante).
7. **[ ]** **`php artisan config:cache`** et **`php artisan route:cache`** (si pas de conflit avec le flux de déploiement).
8. **[ ]** Droits d’écriture : `storage/`, `bootstrap/cache/` (utilisateur du serveur web / PHP-FPM).
9. **[ ]** **`QUEUE_CONNECTION=database`** : migrations `jobs` / `failed_jobs` ; lancer un **worker** (`php artisan queue:work database …`) sous **Supervisor** — voir `docs/supervisor-worker.conf.example` et README.
10. **[ ]** **Callbacks FedaPay** : dans le dashboard FedaPay, URLs alignées sur **`APP_URL`** (voir commentaires `.env.example`).
11. **[ ]** **`LOG_LEVEL`** : typiquement `error` ou `warning` en production ; rotation / rétention des fichiers de log côté serveur.
12. **[ ]** **`npm run build`** puis publication des assets buildés (ou pipeline CI) ; vérifier le chargement CSS/JS.

### B. Après la mise en ligne

13. **[ ]** **`GET {APP_URL}/up`** → réponse **200** (health Laravel).
14. **[ ]** Page d’accueil et **connexion** en HTTPS.
15. **[ ]** Test **génération de rapport PDF** (ou action qui dispatche un job) avec worker actif.
16. **[ ]** **Sauvegarde** MySQL + dossiers applicatifs nécessaires (`storage/app` au minimum) ; **test de restauration** documenté au moins une fois.

---

## 5. Stockage et sauvegardes (D4)

| Élément | Emplacement / note |
|---------|-------------------|
| Rapports PDF | `storage/app/rapports/` (non servi en direct par `public/`) |
| Justificatifs | `storage/app/justificatifs/` |
| Fichiers publics utilisateur (`storage:link`) | `storage/app/public/` → lien symbolique vers `public/storage` si utilisé |

**Sauvegardes (ops) :** dump SQL planifié + copie des répertoires ci-dessus vers un stockage **externe** au serveur ; valider une **restauration** sur un environnement de test.

**Charset** : en cas d’erreur de collation MySQL, aligner `config/database.php` avec le serveur (voir `AGENTS.md`).

---

## 6. Observabilité (D5)

| Mécanisme | Détail |
|-----------|--------|
| **Health** | `GET /up` — défini dans `bootstrap/app.php` (`health: '/up'`). |
| **Logs applicatifs** | `storage/logs/laravel.log` (ou canal configuré). |
| **Monitoring externe** | Option : sonde HTTP périodique sur `/up` et sur la page publique principale. |

Pas d’APM ni d’agrégation centralisée imposés dans ce sprint — voir §8.

---

## 7. Fichiers et artefacts liés au sprint S2

| Fichier | Rôle |
|---------|------|
| `bootstrap/app.php` | Health `/up`, handlers d’exception API (contexte XAMPP déjà traité en S1). |
| `config/queue.php` | Connexion `database` par défaut. |
| `app/Jobs/GenerateRapportPdfJob.php` | Job PDF (nécessite worker en prod). |
| `.env.example` | Commentaires XAMPP, prod, logs, rappels FedaPay (S2). |
| `docs/supervisor-worker.conf.example` | Modèle Supervisor pour `queue:work`. |
| `README.md` | Section déploiement / lien sprint S2. |
| `docs/SPRINT-S2-ARCHITECTURE-INFRA.md` | Ce document. |

---

## 8. Recette manuelle rapide (S2)

1. En local : `php artisan serve` ou XAMPP — vérifier **`GET …/up`** (selon la base URL utilisée).
2. Avec **`QUEUE_CONNECTION=database`** : lancer **`php artisan queue:work`** ; déclencher une génération de rapport PDF ; vérifier que le fichier apparaît sous `storage/app/rapports/` et que la table `jobs` se vide.
3. Contrôler que **`APP_URL`** dans `.env` correspond à l’URL utilisée dans le navigateur pour les liens générés (`asset()`, mails, callbacks).

---

## 9. Hors périmètre volontaire (S2)

- **Infrastructure as Code** (Terraform, Ansible) et choix définitif d’hébergeur.
- **Redis** / Horizon pour les queues (amélioration future si charge élevée).
- **Sentry**, Pulse, stack ELK — suivi d’erreurs avancé.
- **DPA** et localisation des données au niveau **juridique** (lien domaine 2 / institution).
- **Staging** dédié — recommandé en priorité 1 synthèse, non créé dans le dépôt.

---

*Document figé pour clôture sprint S2 — à actualiser lors du choix concret d’hébergement et des procédures de backup validées.*
