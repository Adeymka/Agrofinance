# Prompt pour Claude — Document de projet (certification **EIG**)

**Usage :** copier-coller ce fichier (ou les sections **INSTRUCTIONS POUR CLAUDE** + **CONTEXTE FACTUEL**) dans une conversation avec Claude. Compléter les champs `[À COMPLÉTER]` ou répondre aux **questions posées par Claude** avant la rédaction finale.

**Référence officielle (dossier de projet EIG) :** le document de projet est la preuve de la capacité du candidat à exécuter le projet de manière efficace et professionnelle. Il comprend le **document de soutenance** et les **livrables** associés.

---

## ⚠️ CORRECTIONS OBLIGATOIRES — AUDIT DE CONFORMITÉ CODE (6 avril 2026)

**Background :** Un audit complet du code Laravel a été effectué (33 fichiers lus, 10+ recherches grep, validation ligne-par-ligne). Les points suivants **doivent** être respectés dans le document de soutenance pour rester conforme à la réalité d'implémentation.

| # | Section | Correction obligatoire | Preuve / Source | Justification |
|---|---------|------------------------|-----------------|-----------------|
| 1 | Tarification | **Essentielle : 5 000 FCFA/mois** (non 1 500) ; **Pro : 10 000 FCFA/mois** (non 5 000) ; **Coopérative : 16 000 FCFA/mois** (non 8 000). | `config/tarifs_abonnement.php` ; `database/migrations/2026_03_19_180323_create_abonnements_table.php` | Source de vérité officielle ; plusieurs tables pointent vers ces montants. |
| 2 | Indicateurs financiers | Exactement **10 indicateurs** implémentés : PB (Produit Brut), CV (Charges Variables), CF (Charges Fixes), CT (Charges Totales), CI (Charges d'Intrants), VAB (Valeur Ajoutée Brute), MB (Marge Brute = PB−CV), RNE (Résultat Net de l'Exploitation), RF (Revenu Familial), SR (Seuil de Rentabilité). **RBE n'est pas implémenté**. | `app/Services/FinancialIndicatorsService.php` (calculs des 10 indicateurs) | Cette service calcule exactement ces 10 ; aucune trace de RBE. |
| 3 | Rôles coopérative | **4 rôles uniquement** : admin, validateur, saisie, lecture. Aucun rôle « contrôleur » ou « gérant ». | `app/Models/CooperativeMember.php` (constantes `ROLE_ADMIN`, `ROLE_VALIDATEUR`, `ROLE_SAISIE`, `ROLE_LECTURE`) | Les roles non-existent en base. |
| 4 | Routes API | **Environ 39 routes API** déclarées (non 45+, non unquantifié). | `routes/api.php` (grep count : 39 `Route::` statements) | Nombre exact et vérifiable. |
| 5 | Rapports PDF — Dossier crédit | Le schéma BDD inclut type = 'dossier_credit', **mais la UI affiche explicitement « Dossier crédit... n'est pas encore disponible »** (Phase 2). **À reformuler** : « Rapport mensuel/annuel livrés ; dossier crédit en phase 2 ». | `database/migrations/2026_03_19_180333_create_rapports_table.php` (type enum) ; `resources/views/rapports/index.blade.php` ligne 295 | Ne pas surclamer ; l'UI est la vérité utilisateur. |
| 6 | Génération PDF async | Job `GenerateRapportPdfJob` **existe** mais n'est **jamais dispatché** dans le codebase actuel (0 appels `dispatch(new GenerateRapportPdfJob...)`). **À clarifier** : « PDFs générés synchronement via `RapportService` ; job disponible pour refactorisation future ». | `app/Jobs/GenerateRapportPdfJob.php` (classe existe) ; grep global pour `dispatch.*GenerateRapport` = 0 résultats | Ne pas prétendre async si sync. |
| 7 | Migrations et tables | **33 migrations** au total ; **~15 tables métier** (users, exploitations, activites, transactions, abonnements, rapports, cooperatives, etc.) + **~8 tables infra** (sessions, cache, jobs, etc.). | `database/migrations/*.php` (file_search : 33 fichiers) | Nombre exact pour crédibilité. |
| 8 | SMS OTP — Provider | **Ordre de résolution** : **1) Africa's Talking** si `AFRICASTALKING_API_KEY` défini ; **2) Vonage** si clés Vonage présentes ; **3) Log local** si aucune clé (dev uniquement). Non pas « Vonage primaire ». | `app/Services/OtpService.php` (logique auto-resolve) | Code est source de vérité. |

**Action pour Claude :** Appliquer ces 8 corrections au document fourni. Reformuler les sections concernées avec rigueur, sans inventer de détails. Utiliser les termes exacts du tableau (ex. « 10 indicateurs » et non « 8-12 »).

---

## INSTRUCTIONS POUR CLAUDE (à respecter strictement)

Tu es un rédacteur technique senior. Tu dois rédiger en **français** le **document de projet de certification EIG** pour le projet **AgroFinance+**, candidat : **Donald ADJINDA**.

### Règle prioritaire : questions avant rédaction (parties « personnelles »)

**Avant de rédiger** tout le document, ou **avant** les parties qui dépendent du vécu du candidat, tu dois **poser des questions ciblées** et **attendre les réponses** (ou des placeholders explicitement validés).

Concerne notamment :

- **Introduction** : filière exacte, établissement, année, motivation personnelle courte (si le candidat souhaite l’inclure).
- **Contexte et problématique** : comment la problématique a été identifiée (ex. échanges avec des pairs FSA — **ne pas inventer** d’enquête quantitative ni de statistiques).
- **Objectifs** : priorités du candidat au-delà du cahier technique commun.
- **Méthodologie** : organisation du travail (rythme, outils de suivi, binôme ou non, encadrement).
- **Résultats** : ce qui a été **réellement** livré / testé / montré au tuteur ou au jury (pas de chiffres d’impact inventés).
- **Problèmes rencontrés et solutions** : difficultés **vécues** par le candidat (tu peux suggérer des exemples techniques issus du contexte factuel ci-dessous, mais le candidat doit confirmer lesquels il retient).
- **Conclusion / perspectives** : projets personnels de poursuite (formation, emploi, évolution du produit).

**Si l’utilisateur n’a pas encore répondu**, tu produis uniquement la **liste de questions** puis tu t’arrêtes, **sauf** pour les sections purement techniques (stack, architecture, BDD) que tu peux rédiger à partir du **CONTEXTE FACTUEL** sans invention sur la vie du candidat.

### Contraintes de forme (EIG)

1. **Structure imposée** — Respecter les grandes parties suivantes (titres adaptables, ordre conservé) :
  - **Introduction** — présentation du projet et de ses enjeux ;
  - **Contexte et problématique** — cadre du projet et enjeux visés ;
  - **Objectifs** — objectifs globaux et spécifiques ;
  - **Méthodologie** — démarche créative / de réalisation, **outils et technologies mobilisés** (détailler la stack à partir du contexte technique) ;
  - **Résultats** — résultats obtenus, **pertinence au regard des objectifs** ; appuyer avec **tableaux, schémas ou graphiques** (descriptions Mermaid ou tableaux Markdown acceptés dans le document) ;
  - **Problèmes rencontrés et solutions apportées** ;
  - **Conclusion** — synthèse, enseignements, **perspectives futures** ;
  - **Annexes** — schémas, BDD, captures, tableaux complémentaires.
2. **Volume** — Viser **20 à 30 pages** équivalent Word (en restant **concis** : la **qualité prime sur la quantité** ; pas de remplissage).
3. **Données** — **Ne pas inventer** d’enquête terrain ni de pourcentages. La problématique peut reposer sur **échanges avec des pairs (FSA)**, veille documentaire et analyse du besoin métier.
4. **Visuels obligatoires (aide à la compréhension)** — Inclure au minimum :
  - un **schéma d’architecture** (couches client / serveur / API / BDD / services externes type FedaPay) ;
  - pour les **bases de données** : texte MCD/MPD **et** un **schéma `erDiagram` Mermaid** couvrant les **tables listées dans le CONTEXTE FACTUEL** (relations PK/FK, cardinalités, index/contraintes notables : ex. `client_uuid` unique).
5. **Ton** — Clair, professionnel, **convaincant pour un jury** (enseignants / professionnels).
6. **Projet technique** — Intégrer fidèlement le **CONTEXTE FACTUEL** (nom du projet, modules, middleware `subscribed`, Sanctum, indicateurs financiers, PWA, etc.).

---

## Correspondance avec l’ancien plan détaillé (si besoin de livrables séparés)

Les éléments suivants peuvent être intégrés **dans** les parties EIG ou fournis en **annexes** :


| Thème                                      | Placement suggéré dans le document EIG          |
| ------------------------------------------ | ----------------------------------------------- |
| Veille techno & concurrentielle            | Méthodologie (+ éventuellement Contexte)        |
| Cahier des charges / périmètre fonctionnel | Contexte, Objectifs ou Annexe                   |
| Arborescence applicative, UX/UI, moodboard | Méthodologie ou Résultats (+ visuels en Annexe) |
| Développement détaillé (front, back, API)  | Méthodologie et Résultats                       |
| Base de données (MCD, schéma, sécurité)    | Méthodologie + **Annexes** (schéma complet)     |
| Tests, déploiement                         | Résultats (+ contraintes en Méthodologie)       |
| Démo / parcours utilisateur                | Résultats ou Annexe                             |


---

## CONTEXTE FACTUEL DU PROJET (AgroFinance+) — À INTÉGRER DANS LA RÉDACTION

### Identité du projet

- **Nom :** AgroFinance+
- **Type :** Application web Laravel (Blade), **API REST** versionnée (`/api/v1`), **PWA** (manifest dynamique, service worker), interface **desktop** et **mobile** distinctes (middleware `DetectPlatform` : `layouts.app-mobile` vs `layouts.app-desktop`).
- **Domaine métier :** suivi financier d’**exploitations agricoles** : **exploitations** → **activités** (campagnes `en_cours` / `termine` / `abandonne`) → **transactions** (dépenses / recettes), indicateurs financiers agricoles (PB, CV, CF, MB, RNE, SR, etc.).

### Stack technique (réelle)


| Couche                | Technologies                                                                                                                                                                                             |
| --------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Back-end              | PHP **8.2**, **Laravel 11**                                                                                                                                                                              |
| Auth API              | **Laravel Sanctum** (tokens ; précision métier : `User` utilise `telephone` comme `getAuthIdentifierName()`, donc `**Auth::id()` = téléphone** ; pour clés étrangères utiliser `**auth()->user()->id`**) |
| Front build           | **Vite 5**, **Tailwind CSS 4** (`@tailwindcss/vite`), **Alpine.js**                                                                                                                                      |
| HTTP client           | **Axios** (présent dans `package.json`)                                                                                                                                                                  |
| PDF                   | **barryvdh/laravel-dompdf**                                                                                                                                                                              |
| Paiement              | **FedaPay** (SDK `fedapay/fedapay-php`) ; `FEDAPAY_MOCK` pour dev sans clés                                                                                                                              |
| Serveur local typique | **Apache** (XAMPP), sous-dossier `public`                                                                                                                                                                |
| Base                  | **MySQL/MariaDB** ou **SQLite** selon `.env`                                                                                                                                                             |


### Architecture applicative

- **Routes web** (`routes/web.php`) : pages publiques (accueil, contact, aide), auth **invité** (inscription, OTP, PIN, connexion), **auth** (profil, abonnement, callback FedaPay), groupe `**auth` + `subscribed`** : dashboard, exploitations, activités, transactions, coopérative, rapports.
- **Routes API** (`routes/api.php`, préfixe `v1`) : auth publique, puis `auth:sanctum`, puis `auth:sanctum` + `subscribed` pour le cœur métier ; callback FedaPay en **GET** public.
- **Middleware `subscribed`** (`VerifierAbonnement`) : accès aux fonctionnalités « métier » uniquement si abonnement **actif** ou **essai** non expiré ; sinon redirection **abonnement** ou JSON 403 API.
- **Services métier clés :** `FinancialIndicatorsService` (formules PB, CV, CF, MB = PB−CV, RNE, RF, seuil de rentabilité SR, statut vert/orange/rouge), `AbonnementService`, `DashboardService`, `RapportService`, `CooperativeService`, etc.
- **Synchro hors ligne (mobile) :** IndexedDB + `client_uuid` sur transactions pour idempotence ; `POST /api/v1/transactions` avec UUID client.

### Tarification (config)

- Fichier `config/tarifs_abonnement.php` : **mensuel** 5 000 FCFA, **annuel** 10 000 FCFA, **cooperative** 16 000 FCFA (montants indicatifs).
- Plans persistés : `gratuit`, `essentielle`, `pro`, `cooperative` ; clés FedaPay `mensuel`/`annuel` mappées vers plans métier.

### Tables principales (synthèse pour le schéma BDD en annexe)

- `**users`** : nom, prénom, **téléphone unique**, pin_hash, email nullable, type_exploitation, département, commune.
- `**exploitations`** : `user_id` → users, nom, type, description, localisation.
- `**activites`** : `exploitation_id` → exploitations, nom, type, dates, statut, budget_previsionnel.
- `**transactions**` : `activite_id` → activites ; type depense/recette ; nature fixe/variable ; categorie ; montant ; date_transaction ; synced ; **client_uuid** unique nullable ; statut_validation ; FK vers users pour validateurs ; champs coopération / validation multi-niveaux ; `intrant_production` ; justificatif photo.
- `**abonnements`** : `user_id`, plan, dates, statut, ref_fedapay.
- `**rapports`** : `exploitation_id`, type, période, chemin_pdf, lien_token, lien_expire_le.
- `**help_categories**`, `**help_articles**`, `**help_article_images**` : centre d’aide.
- `**exploitation_categorie_suggestions**` : suggestions de catégories par exploitation.
- `**cooperatives**`, `**cooperative_members**`, `**cooperative_audit_logs**` : gestion coopérative, invitations, rôles, journal d’audit.
- **Tables Laravel / infra :** `sessions`, `cache`, `jobs`, `failed_jobs`, `personal_access_tokens`, `password_reset_tokens`.

### Sécurité (points à citer)

- CSRF formulaires web, **throttle** sur connexion (`auth-connexion`), Sanctum pour API, **SecurityHeadersMiddleware**, exceptions JSON uniformes pour chemins contenant le segment `api` (compatibilité XAMPP).
- Rapports PDF stockés hors `public` direct ; partage via **token** `GET /partage/{token}`.

### Tests automatisés présents dans le dépôt

- Tests Feature : auth, indicateurs financiers, abonnement, sprint API, coopérative, justificatifs, etc. (`tests/Feature/`).

### Fichiers / dossiers utiles pour référence

- `routes/web.php`, `routes/api.php`
- `app/Services/FinancialIndicatorsService.php`
- `app/Http/Middleware/VerifierAbonnement.php`
- `database/migrations/*.php`
- `resources/views/layouts/app-mobile.blade.php`, `app-desktop.blade.php`
- `public/sw.js`, `PwaController`

---

## CHAMPS À COMPLÉTER PAR LE CANDIDAT (Donald ADJINDA)

*(Optionnel si tu réponds plutôt aux questions posées par Claude au début.)*

- **Filière / établissement / année académique :** `[À COMPLÉTER]`
- **Parcours personnel (quelques phrases) :** `[À COMPLÉTER]`
- **Retours tuteur / jury / utilisateurs testeurs :** `[À COMPLÉTER]`
- **URL de démo ou hébergement :** `[À COMPLÉTER OU N/A]`

---

*Fin du prompt — pour la certification **EIG**, le dossier complet inclut aussi les **livrables** produits (code, captures, maquettes exportées, etc.) en complément de ce document.*