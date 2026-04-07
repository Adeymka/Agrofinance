# 📋 PROMPT COMPLET — Document de Soutenance EIG : AgroFinance+

## 🎯 MISSION GLOBALE

Tu es un **rédacteur technique senior en certification EIG**. Tu dois rédiger le **document de soutenance complet** pour le projet **AgroFinance+** (candidat : **Donald ADJINDA**, Bénin).

Le document doit être **professionnel**, **convaincant pour un jury**, **basé sur des faits vérifiés** (audit code Laurent 2026), et suivre **exactement** la table des matières et les standards EIG fournis.

**Durée estimée** : 25–35 pages équivalent Word.
**Ton** : Technique mais accessible aux enseignants et professionnels non-spécialistes.
**Langue** : Français impeccable, sans fautes de grammaire.

---

## 📌 CORRECTIONS OBLIGATOIRES (Audit Code — 6 avril 2026)

**⚠️ CES 8 POINTS DOIVENT ÊTRE RESPECTÉS EXACTEMENT DANS LE DOCUMENT** :

| # | Point | Correction | Source | Justification |
|---|-------|-----------|--------|---------------|
| 1 | **Tarification des plans** | Essentielle : **5 000 FCFA/mois** (non 1 500) ; Pro : **10 000 FCFA/mois** (non 5 000) ; Coopérative : **16 000 FCFA/mois** (non 8 000) | `config/tarifs_abonnement.php`, migrations 2026_03_19 | Source de vérité officielle ; tous les tests API valident ces montants |
| 2 | **Indicateurs financiers** | Exactement **10 indicateurs implémentés** : PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR ; **AUCUN RBE** | `app/Services/FinancialIndicatorsService.php` (lignes 1–500) | Calculs vérifiés ligne par ligne ; RBE n'existe pas en code |
| 3 | **Rôles coopérative** | **4 rôles uniquement** : admin, validateur, saisie, lecture (pas de « contrôleur » ni « gérant ») | `app/Models/CooperativeMember.php` constantes ROLE_* | Roles sont des constantes de classe ; migration définit enum exact |
| 4 | **Nombre de routes API** | **Environ 39 routes API** déclarées (non 45+, non indéfini) | `routes/api.php` grep count | Nombre exact et vérifiable pour crédibilité |
| 5 | **Rapports PDF — Dossier crédit** | Rapports mensuel/annuel/campagne : **oui**. Dossier crédit : **Phase 2** (schéma inclut type, UI dit « pas disponible ») | `resources/views/rapports/index.blade.php` ligne 295 | Ne pas surclamer ; l'UI en prod est source de vérité |
| 6 | **Génération PDF async** | PDFs actuellement **synchrones** via `RapportService` ; job `GenerateRapportPdfJob` existe pour future refactorisation | `app/Jobs/GenerateRapportPdfJob.php` existe ; grep zéro `dispatch(GenerateRapport*)` | Ne pas prétendre async si sync en prod |
| 7 | **Migrations et tables** | **33 migrations** ; **15 tables métier** + **8 tables infra** | `database/migrations/*.php` file_search : 33 fichiers | Nombre exact documenté pour crédibilité |
| 8 | **SMS OTP — Providers** | Ordre résolution : **1) Africa's Talking** (si clé), **2) Vonage** (fallback), **3) Log local** (dev) | `app/Services/OtpService.php` auto-resolve logic | Code est source de vérité ; ne pas inverser l'ordre |

**ACTION** : Appliquer ces 8 corrections dans les sections pertinentes. En cas de doute, utiliser les termes exacts du tableau.

---

## 📑 TABLE DES MATIÈRES — STRUCTURE IMPOSÉE

```
TABLE DES MATIÈRES
1. INTRODUCTION
   1.1 Présentation personnelle
   1.2 Présentation du projet
   1.3 Public cible
2. PROBLÉMATIQUE ET OBJECTIFS
   2.1 L'agriculture béninoise : contexte + enjeux
   2.2 Objectifs du projet
3. VEILLES
   3.1 Veille technologique
   3.2 Veille concurrentielle
   3.3 Étude UX — attentes utilisateurs
4. CAHIER DES CHARGES
   4.1 Fonctionnalités du MVP (26 fonctionnalités)
   4.2 Contraintes techniques
   4.3 Planning de développement
5. ARBORESCENCE ET MAQUETTES
   5.1 Arborescence de l'application (20 pages)
   5.2 Principaux écrans et choix UX/UI
6. CHARTE GRAPHIQUE ET DESIGN SYSTEM
   6.1 Concept visuel
   6.2 Palette de couleurs
   6.3 Typographies
   6.4 Composants UI réutilisables
7. DÉVELOPPEMENT
   7.1 Schéma d'architecture système (MERMAID DIAGRAM)
   7.2 Architecture applicative
   7.3 FinancialIndicatorsService — 10 indicateurs
8. BASE DE DONNÉES
   8.1 Schéma d'architecture BDD (MERMAID ER DIAGRAM)
   8.2 Description détaillée des tables
   8.3 Contraintes et index notables
   8.4 Sécurité des données
9. TESTS ET DÉPLOIEMENT
   9.1 Tests fonctionnels
   9.2 Tests automatisés (tests/Feature/)
   9.3 Déploiement
10. DÉMONSTRATION — PARCOURS UTILISATEUR
    10.1 Scénario utilisateur (ex. Madame Aïcha, maraîchère)
11. VIABILITÉ ÉCONOMIQUE
    11.1 Le marché adressable
    11.2 Modèle économique SaaS
12. BILAN
    12.1 Difficultés rencontrées et solutions
    12.2 Résultats obtenus — compétences démontrées
    12.3 Perspectives et évolutions futures
    12.4 Conclusion
ANNEXES
    Annexe A — Livrables (code, captures, arborescence)
    Annexe B — Références bibliographiques
```

---

## 🔍 CONTEXTE FACTUEL — À INTÉGRER PARTOUT

### Identité du projet
- **Nom** : AgroFinance+
- **Type** : Application web full-stack Laravel + PWA mobile
- **Objectif métier** : Suivi financier d'exploitations agricoles (transactions, indicateurs, rapports)
- **Cible** : Petits exploitants agricoles au Bénin (filières maraîchage, élevage, cultures vivrières)
- **Statut** : MVP livré ; Phase 2 (dossier crédit, coopératives avancées) en backlog

### Stack technique (EXACT)
- **Backend** : Laravel 11, PHP 8.2, Sanctum (auth API)
- **Frontend** : Vue SPA ou Blade templates + Vite, Tailwind CSS 4, Alpine.js
- **PWA** : manifest.json, service worker (sw.js), IndexedDB offline sync
- **PDF** : barryvdh/laravel-dompdf (synchrone)
- **Paiement** : FedaPay SDK
- **SMS** : Africa's Talking (primaire) → Vonage (fallback) → log (dev)
- **BDD** : MySQL/MariaDB (ou SQLite tests)
- **Déploiement** : Apache XAMPP local ; Railway/VPS production

### Architecture (SIMPLIFIÉ)
```
Clients (Desktop PWA | Mobile PWA | API Postman)
   ↓
Entry Points (public/index.php | sw.js)
   ↓
Middlewares (DetectPlatform | Auth | Guest | Subscribed)
   ↓
Routes (web.php | api.php)
   ↓
Controllers (Auth | Dashboard | Exploitations | Activités | Transactions | Abonnement | Rapports | Coopérative)
   ↓
Services (FinancialIndicators | Abonnement | OTP | Rapport | Coopérative)
   ↓
Models (User | Exploitation | Activité | Transaction | Abonnement | Rapport | CooperativeMember | Help*)
   ↓
Base de données (33 migrations, 15 tables métier + 8 infra)
   ↓
Services externes (FedaPay | Africa's Talking/Vonage | Unsplash)
```

### Fonctionnalités principales
1. **Authentification** : Inscription (OTP SMS) → Création PIN → Connexion
2. **Gestion exploitations** : CRUD, plusieurs types (maraîchage, élevage, etc.)
3. **Saisie transactions** : Dépenses/Recettes, catégories dynamiques, offline sync (IndexedDB + UUID client)
4. **Calcul indicateurs** : 10 indicateurs (PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR) par activité et exploitation
5. **Dashboard consolidé** : Vue d'ensemble chiffres, graphiques, alertes (mobile + desktop distinct)
6. **Rapports PDF** : Mensuel, annuel, campagne (téléchargement + partage token public)
7. **Abonnement SaaS** : Plans gratuit/essentielle/pro/coopérative (FedaPay)
8. **Coopérative** : Partage données, rôles (admin/validateur/saisie/lecture), audit logs
9. **Centre d'aide** : Articles publics, catégories, images
10. **PWA & offline** : Manifest, service worker, sync automatique en ligne

### Chiffres clés
- **39 routes API** (`/api/v1`)
- **33 migrations** en base
- **15 tables métier** + 8 infra
- **10 indicateurs** financiers
- **4 rôles** coopérative
- **5/10/16K FCFA** tarification (plans)
- **26 fonctionnalités MVP**
- **~30 tests** Feature automatisés

---

## ❓ QUESTIONS PRÉALABLES À COMPLÉTER (Candidat : Donald ADJINDA)

**ℹ️ Si pas de réponse, utiliser des placeholders génériques motivés par contexte EIG.**

### SECTION 1 : PRÉSENTATION PERSONNELLE
- Quel est ton **parcours académique** (filière, école/université, cursus suivi) ?
- **Année académique** du certificat EIG en cours ?
- **Motivation personnelle** : Pourquoi l'agriculture numérique ? (quelques phrases sincères)
- **Lien tuteur/encadrement** : Qui t'a encadré ? Quel était le rythme (hebdo, bi-hebdo) ?

### SECTION 2 : CONTEXTE & PROBLÉMATIQUE
- **Comment as-tu identifié la problématique** ? (Échanges terrain ? Veille documentaire ? Pairs FSA ?)
- **Données utilisateurs** : As-tu fait des entretiens ? Combien de pairs consultés ? (Ne pas inventer de chiffres d'enquête)
- **Benchmark** : Quels outils concurrents as-tu examinés ? (Enumerez noms, URLs si pertinent)

### SECTION 3 : DIFFICULTÉS & SOLUTIONS
- **Principaux défis rencontrés** (3–5 exemples techniques vécus) :
  - Calcul des indicateurs financiers agricoles ?
  - Offline sync avec IndexedDB ?
  - Intégration FedaPay ?
  - Détection mobile vs desktop ?
  - Autre ?
- **Comment as-tu résolu chacun** ? (Recherche, pair programming, doc, tuto ?)

### SECTION 4 : RÉSULTATS TESTÉS
- **Démo lancée** ? À qui (tuteur, jury, utilisateurs réels) ?
- **Feedback reçu** ? (Positif/négatif, quels ajustements ?)
- **Non-livrables** : Qu'est-ce qui a dû être reporté (Phase 2) et pourquoi ? (Dossier crédit, coopérative avancée, etc.)

### SECTION 5 : PERSPECTIVES
- **Évolution personnelle** : Formation/emploi envisagé post-EIG ?
- **Feuille de route produit** : 1–3 priorités Phase 2 ?

---

## 📝 DIRECTIVES DE RÉDACTION (STRICTES)

### Généralités
1. **Ne jamais inventer** : Données, statistiques, entretiens utilisateurs, feedback jury — à confirmer par candidat
2. **Citations & preuves** : Pour chaque affirmation technique, citer fichier/ligne ou test (ex. `FinancialIndicatorsService.php:42–78`)
3. **Tonalité** : Professionnel, honnête, convaincant sans surclamer
4. **Français** : Relire orthographe/grammaire ; pas d'anglicisme sauf termes tech requis (MVP, OTP, PWA, etc.)

### Par section

#### Section 1 — INTRODUCTION
- **1.1 Présentation personnelle** : 2–3 paragraphes, insertion CV court/formation/motivation
- **1.2 Présentation du projet** : 1 paragraphe résumé (domaine, cible, MVP vs Phase 2)
- **1.3 Public cible** : 1 paragraphe (exploitants agricoles Bénin, profil type)

#### Section 2 — PROBLÉMATIQUE & OBJECTIFS
- **2.1 L'agriculture béninoise** : Contexte factuel (sources documentaires, pas d'invention)
  - Importance économique + sous-équipement numérique
  - Besoins métier : suivi financier, indicateurs, rapports
- **2.2 Objectifs** : Répartir en objectifs généraux (vision EIG) + spécifiques (MVP)
  - Général : Démocratiser l'outillage financier pour petits exploitants
  - Spécifiques : App web responsive, 10 indicateurs, gestion multi-exploitations, offline, rapports PDF, SaaS

#### Section 3 — VEILLES
- **3.1 Technologique** : Stack choisi (Laravel, PWA, Sanctum, etc.) + justification (maturité, doc, écosystème)
- **3.2 Concurrentielle** : Benchmarking outils similaires (noms, forces, faiblesses vs AgroFinance+)
- **3.3 UX utilisateurs** : Retours terrain / entretiens (ou placeholder si aucun)

#### Section 4 — CAHIER DES CHARGES
- **4.1 Fonctionnalités** : Tableau listant 26 fonctionnalités (Authentification, CRUD Exploitations, Saisie Transactions, Dashboard, Indicateurs, Rapports, Abonnement, Coopérative, Aide, PWA, etc.)
- **4.2 Contraintes** : Techniques (PHP 8.2, responsive 320-1920px), métier (calcul financier exact), sécurité (CSRF, Sanctum, throttling)
- **4.3 Planning** : Roadmap phases (MVP → Phase 2 → Phase 3) avec quasi-durées estimées

#### Section 5 — ARBORESCENCE & MAQUETTES
- **5.1 Arborescence** : Tableau ou tree structure montrant ~20 pages (Accueil, Connexion, Dashboard, Exploitations, Activités, Transactions, Rapports, Coopérative, Profil, Paramètres, Aide, etc.)
- **5.2 Écrans principaux** : 3–5 maquettes ou captures annotées (Desktop + Mobile) avec choix design (ex. « Sidebar glassmorphism desktop, bottom nav mobile »)

#### Section 6 — CHARTE GRAPHIQUE & DESIGN SYSTEM
- **6.1 Concept** : Dark glassmorphism, accessibility (outdoor boost mode), responsive 2 layouts distincts
- **6.2 Couleurs** : Palette CSS (--af-primary, --af-secondary, etc.) + contraste WCAG AA
- **6.3 Typographies** : Space Grotesk (titres) + Inter (corps) ; justify `config/app.css`
- **6.4 Composants** : Boutons, cartes, modales, formulaires (conformes Tailwind + Alpine)

#### Section 7 — DÉVELOPPEMENT
- **7.1 Schéma architecture système** : 
  - ✅ **INCLURE DIAGRAMME MERMAID COMPLET** (9 couches : clients → entry → middlewares → routes → controllers → services → models → views → BDD → externals)
  - Accompagner d'explication (flux général, point de sécurité, offline sync)
- **7.2 Architecture applicative** :
  - Routes web (`routes/web.php` : publiques, invité, auth, subscribed)
  - Routes API (`routes/api.php` : 39 endpoints, `/api/v1` préfixe)
  - Middlewares clés (DetectPlatform, Subscribed, Auth, CSRF, throttle)
  - Contrôleurs (Auth, Dashboard, Exploitations, etc.)
  - Services métier (FinancialIndicators, Abonnement, OTP, Rapport, Coopérative)
- **7.3 FinancialIndicatorsService** :
  - ✅ **EXACTEMENT 10 indicateurs** (pas 8, pas 12) :
    1. PB (Produit Brut) = sum(recettes)
    2. CV (Charges Variables) = formule
    3. CF (Charges Fixes) = formule
    4. CT (Charges Totales) = CV + CF
    5. CI (Charges d'Intrants) = sous-ensemble CV
    6. VAB (Valeur Ajoutée Brute) = PB − CV
    7. MB (Marge Brute) = PB − CV (identique VAB)
    8. RNE (Résultat Net Exploitation) = PB − CT
    9. RF (Revenu Familial) = RNE − impôts (estimé)
    10. SR (Seuil Rentabilité) = CT / (1 − CV/PB)
  - Formules complètes + exemples numériques
  - **Pas de RBE** (important : RBE n'existe pas en code)
  - Statuts coloriés : vert/orange/rouge selon seuils

#### Section 8 — BASE DE DONNÉES
- **8.1 Schéma BDD** :
  - ✅ **INCLURE DIAGRAMME MERMAID ER DIAGRAM** (tables, PK, FK, relations, cardinalités)
  - Exemple tables : users, exploitations, activites, transactions, abonnements, rapports, cooperatives, cooperative_members, help_*, etc.
- **8.2 Description tables** : Tableau détaillé (table, colonnes, types, contraintes, indexes)
  - **Spécial attention** :
    - `users.telephone` = unique, auth identifier (non integer `id`)
    - `transactions.client_uuid` = unique nullable (offline sync idempotence)
    - `abonnements.plan` = enum (gratuit|essentielle|pro|cooperative)
    - `rapports.type` = enum (campagne|mensuel|annuel|dossier_credit) — **dossier_credit en Phase 2**
    - `cooperative_members.role` = enum (admin|validateur|saisie|lecture)
- **8.3 Contraintes & indexes** :
  - Indexes perf sur `(activite_id, date_transaction)` + `date_transaction`
  - Clés étrangères avec ON DELETE/UPDATE logique
- **8.4 Sécurité** :
  - Chiffrement PINs (bcrypt)
  - Rapports hors public direct (token temporaire)
  - Throttling connexion
  - CSRF protection

#### Section 9 — TESTS & DÉPLOIEMENT
- **9.1 Tests fonctionnels** :
  - Parcours utilisateur complets (inscription → OTP → PIN → connexion → créer exploitation → saisir transaction → voir indicateurs → générer rapport)
  - Offline sync validation (JSON enqueué → POST en ligne → synchronisé)
  - Abonnement workflow (FedaPay mock)
- **9.2 Tests automatisés** :
  - Référencer `tests/Feature/` (ex. AuthenticationTest, FinancialIndicatorsTest, AbonnementTest, CooperativeTest)
  - Couverture ~30 tests ; CI pipeline (GitHub Actions si présent)
- **9.3 Déploiement** :
  - Local : XAMPP Windows
  - Production : VPS Apache + Supervisor (job queues, rapports async futur)
  - Env vars : .env (FedaPay keys, Africa's Talking SMS, etc.)

#### Section 10 — DÉMONSTRATION — PARCOURS UTILISATEUR
- **10.1 Scénario exemple** : « Madame Aïcha, maraîchère à Calavi »
  - Profil : ~0.5 ha, vend au marché local, 1 téléphone basique, peu de numératie
  - Parcours : Inscription (reçoit OTP SMS) → Crée exploitation « Maraîchage 2026 » → Enregistre activité « Tomate » → Saisit transactions offline (dépenses semences, main-d'œuvre ; recettes vente) → Se reconnecte WiFi → App synchro → Voit indicateurs (MB = 850K FCFA) → Génère rapport PDF mensuel → Partage avec collecteur groupe CEL → Souscrit plan Essentielle (5K FCFA/mois FedaPay) → Peut ajouter 2 exploitations supplémentaires
  - Points d'innovation mis en avant : SMS OTP (pas email), offline, indicateurs agricoles vrais, rapports physiques partageables, coopérative

#### Section 11 — VIABILITÉ ÉCONOMIQUE
- **11.1 Marché adressable** :
  - Bénin : ~1.2M exploitations (source FAO / gouvernement) ; cible : 5–10K premières années
  - TAM (Total Addressable Market) : exploitation moyenne facture 5K FCFA/mois = 60K/an
  - SOM (Serviceable Addressable Market) : 25K exploitations × 60K/an = 1.5B FCFA
- **11.2 Modèle SaaS** :
  - Revenue streams : Abonnements individuels (gratuit/essentielle/pro) + coopérative (bulk discount)
  - Cost structure : Serveurs (Railway/Heroku ~300€/mois), SMS providers, FedaPay commission, support
  - Breakeven estimé : 1500–2000 utilisateurs payants (plan essentielle moyen)
  - Profitability year 2–3 si adoption régionale

#### Section 12 — BILAN
- **12.1 Difficultés rencontrées & solutions** :
  - Exemple 1 : Calcul indicateurs financiers complexe → Solution : étude agronomie + itération feedback
  - Exemple 2 : Offline sync idempotence → Solution : client_uuid + grep tests
  - Exemple 3 : SMS OTP providers → Solution : auto-resolve Africa's Talking → Vonage → log
  - (3–5 exemples minimum, vécus par candidat)
- **12.2 Résultats & compétences** :
  - Livrable : MVP production-ready (code open source si possible)
  - Compétences démontrées : Full-stack Laravel+Vue, PWA, UX responsive, SaaS principles, gestion projet agile, soft skills (écoute utilisateur, itération)
  - Couverture tests, documenté (README, API.md), déployable en 1 commande
- **12.3 Perspectives** :
  - Phase 2 : Dossier crédit détaillé, coopérative avancée (consensus validations), mobile app native si ROI
  - Phase 3 : Intégration données gouvernementales / crédit, marketplace services agri
  - Perso : [Formation/emploi post-EIG candidat ?]
- **12.4 Conclusion** :
  - Récapitulatif : Objectifs atteints, MVP livré, prêt pour Phase 2
  - Impact : Outil disruptif pour suivi financier agricole Bénin

### ANNEXES

#### Annexe A — Livrables du projet
- **Code source** : GitHub/GitLab (ou ZIP si non public)
  - Repo structure : `/app`, `/routes`, `/database`, `/resources`, `/tests`
  - Documentation : README (setup local + prod), API.md
- **Captures d'écran** : 5–10 vues principales (desktop + mobile)
- **Arborescence détaillée** : Tree complet navigation
- **Tests** : Résultats exécution suite tests Feature (output terminal)
- **Rapports PDF exemple** : Samples générés

#### Annexe B — Références bibliographiques
- **Sources agricoles** : FAO reports, Bénin agricultural ministry data
- **Tech** : Laravel docs, Sanctum auth, Tailwind CSS, Service Workers MDN, DomPDF
- **SaaS/Business** : Articles pitch SaaS, tarification freemium analysis
- **Benchmarks** : URLs + screenshots outils concurrent (si benchmark effectué)

---

## 🛠️ FORMAT OUTPUT ATTENDU

### Structure
1. **Couverture + Résumé exécutif** (1 page)
2. **Table des matières** (1 page)
3. **Corps du document** (25–35 pages) respectant TDM exactement
4. **Annexes** (5–10 pages)

### Mise en forme
- **Marges** : 2.5 cm (haut, bas, gauche, droite)
- **Police** : Calibri 11pt ou Arial, interligne 1.5
- **Titres** : H1 (18pt bold), H2 (14pt bold), H3 (12pt bold), corps (11pt)
- **Numérotation** : Pages continue, numéros en bas à droite
- **Figures** : Numérotée, captionnée, listée en TDM avec pages
- **Tableaux** : Numérotés, captionnés, source notée
- **Diagrammes Mermaid** : Insérés comme images PDF/SVG ou inline (si Markdown acecepté)
- **Code snippets** : Formatés monospace, contexte limité (5–15 lignes max par snippet)

### Langues & termes
- **Français uniquement**, sauf termes tech en anglais quand obligatoire (MVP, OTP, PWA, Sanctum, etc.)
- Acronymes : définis au première occurrence
- Termes agricoles : expliquants ou liens vers ressources (FSA, CEL, etc.)

---

## ✅ CHECKLIST QUALITÉ (AVANT LIVRAISON)

- [ ] Table des matières respectée exactement (12 sections + annexes)
- [ ] 8 corrections obligatoires intégrées (tarification, indicateurs, rôles, routes, PDF, async, migrations, SMS)
- [ ] Aucune invention de données utilisateurs / statistiques
- [ ] Diagramme architecture (Mermaid) complet + explication 
- [ ] Schéma BDD (Mermaid ER) complet + description tables
- [ ] 10 indicateurs détaillés (formules + exemples numériques)
- [ ] Tests mentionnés (Feature tests, exemples)
- [ ] Parcours utilisateur cohérent (scénario Aïcha complet A→Z)
- [ ] Viabilité économique chiffrée (TAM, SOM, break-even)
- [ ] Difficultés réelles & solutions (3–5 exemples)
- [ ] Orthographe impeccable, grammaire OK
- [ ] Format pages (25–35 pages, marges correctes)
- [ ] Annexes complètes (code, captures, références)
- [ ] Tonalité convaincante mais honnête (pas d'hype, réaliste)

---

## 🚀 INSTRUCTIONS DE DÉMARRAGE

1. **Répondre aux 5 questions préalables** ci-dessus (ou valider placeholders)
2. **Claude produit** le document sectio par section, itérations si besoin
3. **Relecture** : Candidat + Tuteur + Claude (corrections finales)
4. **Export** : PDF / DOCX final avec couverture officielle EIG

---

## 📌 NOTES SPÉCIALES POUR CLAUDE

- **Ton** : Professionnel senior (jury universitaire + industrie) ; convaincant sans esbroufe
- **Exactitude** : Citer sources code ou données précises ; pas de « environ », « peut-être »
- **Longueur** : 25–35 pages = dense mais lisible ; qualité > quantité
- **Français** : Relire systématiquement ; corriger accords/conjugaisons/ponctuation
- **Diagrammes** : Mermaid OK ; si trop complexe, découper en sous-diagrammes
- **Itérations** : Proposer brouillon section-by-section ; ajuster sur feedback candidat
