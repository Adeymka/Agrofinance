# Plan de corrections par sprint — aligné sur les synthèses

**Objectif.** Structurer le travail de **correction et d’amélioration** du produit à partir des analyses des documents `SYNTHESE-SOUTENANCE-*.md`, **sans mélanger** les sujets : un **sprint = un domaine technique ou produit**, avec une **analyse ciblée** du code et de la **sécurité** pertinente pour ce périmètre.

**Ce document ne remplace pas** les synthèses : il sert de **plan opérationnel** et de **table des matières** vers leurs sections **D** (thèmes) et **F** (priorités). Pour l’historique des changements déjà faits dans le code, voir `HISTORIQUE_AMELIORATIONS.md`.

---

## 1. Principes

| Principe | Détail |
|----------|--------|
| **Un sprint = un thème** | Même découpage que les domaines 1 à 6 (le domaine 7 est surtout **doc / positionnement**, pas un sprint code). |
| **Périmètre clair** | Avant de coder, noter quels **modules / dossiers** sont concernés ; le reste est **hors scope** pour ce sprint. |
| **Une source de vérité par sujet** | Si une correction touche deux domaines (ex. métier + UX), choisir le **document principal** et un **renvoi court** dans l’autre. |
| **Fin de sprint** | Liste de correctifs **fermée** ou report explicite ; mise à jour de `HISTORIQUE_AMELIORATIONS.md` pour les évolutions notables. |

---

## 2. Correspondance domaines ↔ fichiers de synthèse

| Domaine | Fichier | Rôle pour les corrections |
|---------|---------|---------------------------|
| **1 — Métier & indicateurs** | `SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md` | Formules, périodes, consolidation, cohérence chiffres / PDF / API. |
| **2 — Sécurité & données** | `SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md` | Auth, cloisonnement, journaux, secrets, fichiers, RGPD minimal. |
| **3 — Produit & parcours** | `SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md` | Parcours inscription → usage, écrans clés, offline, aide. |
| **4 — UX & accessibilité** | `SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md` | Lisibilité, tactile, formulaires, a11y, charge cognitive. |
| **5 — Architecture & infra** | `SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md` | Stack, env, queues, stockage, sauvegardes, observabilité. |
| **6 — Paiement & abonnement** | `SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md` | Plans, FedaPay, droits, mock, expiration. |
| **7 — Stratégie & positionnement** | `SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md` | Textes publics, promesse, partenaires **(hors sprint code sauf pages marketing / FAQ)**. |

---

## 3. Ordre recommandé des sprints (dépendances)

L’ordre ci-dessous **réduit les retours en arrière** : une base **sécurité** et **infra** solide avant d’empiler le **métier**, puis **paiement**, **parcours**, enfin **UX** (souvent en dernier pour ne pas polir une logique encore fausse).

| Ordre | Sprint | Synthèse principale | Pourquoi cet ordre |
|-------|--------|---------------------|---------------------|
| **S1** | Sécurité & données | Domaine **2** | Les règles d’accès et le cloisonnement conditionnent tout le reste. |
| **S2** | Architecture & infra | Domaine **5** | Cadre déploiement, env, callbacks, jobs — stabilise avant gros chantiers métier. |
| **S3** | Métier & indicateurs | Domaine **1** | Cœur calcul et cohérence des indicateurs. |
| **S4** | Paiement & abonnement | Domaine **6** | Dépend des règles métier et de l’infra (URLs, callbacks). |
| **S5** | Produit & parcours | Domaine **3** | Une fois métier + abonnement stables, aligner les parcours utilisateur. |
| **S6** | UX & accessibilité | Domaine **4** | Affinage interface et messages une fois le comportement figé. |
| **S7** *(optionnel)* | Stratégie & contenus | Domaine **7** | Relecture pages publiques, FAQ, centre d’aide, cohérence terminologique. |
| **Transversal** | Tests & recette | Toutes | Contrôles manuels ou automatisés **par sprint** ou sprint final **hardening**. |

*Remarque.* Si la sécurité de base est déjà jugée satisfaisante, on peut **démarrer par S2 + S3** en parallèle (deux personnes) ou enchaîner **S3** juste après **S1** en réduisant S2 au strict nécessaire.

---

## 4. Grille d’analyse — à répéter à chaque sprint

| Étape | Action |
|-------|--------|
| **A. Lire la synthèse** | Parcourir les sections **D1 à D6** et **F** du domaine concerné ; noter les **pistes** déjà listées comme backlog du sprint. |
| **B. Périmètre code** | Lister les dossiers / namespaces ciblés (ex. `app/Services`, `routes/api.php`, `resources/views/...`). |
| **C. Sécurité ciblée** | Appliquer le **sous-ensemble** décrit dans le tableau « Sécurité par sprint » (section 6), pas un audit général à chaque fois. |
| **D. Tickets** | Une ligne par correction : **type** (bug / évolution / doc), **fichiers**, **critère de done**. |
| **E. Recette** | Scénarios courts alignés sur les thèmes D du domaine. |
| **F. Trace** | Mettre à jour `HISTORIQUE_AMELIORATIONS.md` si l’impact est significatif. |

---

## 5. Détail par sprint

### Sprint S1 — Sécurité & données (`SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S1-SECURITE-DONNEES.md` (thèmes D1–D6, checklist prod, recette).

**Objectif.** Garantir une authentification cohérente, un **cloisonnement** des données par utilisateur / exploitation, et des **pratiques saines** (logs, secrets, fichiers).

**Thèmes D à traiter en priorité (référence doc) :**

- **D1** — Authentification (PIN, OTP, jetons API)  
- **D2** — Journaux, traces et fuite de secrets  
- **D3** — Cloisonnement des données et pièges de développement  
- **D4** — Fichiers justificatifs et pièces jointes  
- **D5** — Secrets, environnements et mise en production  
- **D6** — Cadre institutionnel (RGPD minimal, etc.)

**Périmètre code typique.** Middleware Sanctum / web auth, policies ou contrôles `user_id` / propriétaire, `config/`, stockage des fichiers, handlers d’exception API, `.env` (sans le committer).

**Livrable de fin de sprint.** Liste des routes / API revues pour l’**ownership** ; pas de secret en clair dans les logs ; checklist **D5** pour la prod.

*Compte rendu du sprint S1 réalisé :* `docs/SPRINT-S1-SECURITE-DONNEES.md`.

---

### Sprint S2 — Architecture & infra (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S2-ARCHITECTURE-INFRA.md` (checklist local → prod, D1–D6, recette, hors périmètre).

**Objectif.** Aligner **environnements**, **URLs** (dont sous-dossier XAMPP si pertinent), **queues**, **stockage**, **sauvegardes** et **observabilité**.

**Thèmes D :**

- **D1** — Structure applicative (monolithe, services, API v1)  
- **D2** — Développement local vs production  
- **D3** — Files d’attente, jobs  
- **D4** — Stockage, base de données et sauvegardes  
- **D5** — Observabilité, logs et `/up`  
- **D6** — Institution : localisation, sous-traitants

**Périmètre code typique.** `bootstrap/`, `config/`, jobs, `storage/`, healthcheck, documentation des commandes de déploiement.

**Livrable.** Document court ou section README : **comment** passer de local à prod ; jobs supervisés si utilisés.

*Compte rendu du sprint S2 réalisé :* `docs/SPRINT-S2-ARCHITECTURE-INFRA.md`.

---

### Sprint S3 — Métier & indicateurs (`SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S3-METIER-INDICATEURS.md` (D1–D6, intrant production, recette, hors périmètre).

**Objectif.** Cohérence des **calculs**, **périodes**, **consolidation multi-campagnes**, **PDF / API** avec les mêmes règles.

**Thèmes D :**

- **D1** — Période couverte par les chiffres  
- **D2** — Saisie référentiel vs libre (charges intermédiaires, VAB)  
- **D3** — Dépenses fixes vs variables  
- **D4** — Plusieurs campagnes actives  
- **D5** — Seuil de rentabilité et statut exploitation  
- **D6** — Peu de lignes saisies (dossier incomplet)

**Périmètre code typique.** `FinancialIndicatorsService`, contrôleurs dashboard / rapports, ressources API des indicateurs, vues qui affichent les totaux.

**Livrable.** Comportement **explicite** sur la période et les campagnes **en_cours** ; alignement avec la section **F** de la synthèse métier.

*Compte rendu du sprint S3 réalisé :* `docs/SPRINT-S3-METIER-INDICATEURS.md`.

---

### Sprint S4 — Paiement & abonnement (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S4-PAIEMENT-ABONNEMENT.md` (D1–D6, tarifs centralisés, recette, hors périmètre).

**Objectif.** Aligner **prix affichés**, **`AbonnementService`**, **FedaPay**, **middleware subscribed**, **droits** (PDF, exploitations, historique).

**Thèmes D :**

- **D1** — Grille tarifaire et cohérence offre / moteur  
- **D2** — Parcours payeur (web, mobile, après paiement)  
- **D3** — Mode mock et environnement de développement  
- **D4** — Droits fonctionnels  
- **D5** — Institution : facturation, sponsoring  
- **D6** — Expiration, essai et communication

**Périmètre code typique.** `AbonnementService`, routes abonnement / callback, vues tarifs, middleware d’abonnement.

**Livrable.** Tableau **plan → droits** vérifiable dans l’UI ; parcours post-paiement clair ; mock documenté pour les démos.

*Compte rendu du sprint S4 réalisé :* `docs/SPRINT-S4-PAIEMENT-ABONNEMENT.md`.

---

### Sprint S5 — Produit & parcours (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S5-PRODUIT-PARCOURS.md` (D1–D6, messages abonnement, synchro offline, recette).

**Objectif.** Cohérence **inscription → OTP → PIN → abonnement → usage** ; **mobile vs desktop** ; **offline** ; **aide**.

**Thèmes D :**

- **D1** — Parcours d’entrée  
- **D2** — Mobile versus desktop  
- **D3** — Tableau de bord et synthèses  
- **D4** — Saisie des transactions (dont offline)  
- **D5** — Rapports PDF, partage, déconnexion  
- **D6** — Aide en ligne, pages publiques, mode hors ligne

**Périmètre code typique.** Contrôleurs auth web, vues `app-mobile` / `app-desktop`, JS offline (`offline-transactions.js`), routes web concernées.

**Livrable.** Parcours **sans impasse** ; même logique métier sur les deux plateformes ; synchro offline alignée sur `docs/API_CLIENT.md`.

*Compte rendu du sprint S5 réalisé :* `docs/SPRINT-S5-PRODUIT-PARCOURS.md`.

---

### Sprint S6 — UX & accessibilité (`SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`)

**Statut : terminé** — voir le compte rendu consolidé `docs/SPRINT-S6-UX-ACCESSIBILITE.md` (D1–D6, focus, erreurs, recette).

**Objectif.** Lisibilité (**thème glass**, contraste), **tactile**, **formulaires et erreurs**, **charge cognitive**, **a11y** de base.

**Thèmes D :**

- **D1** — Lisibilité, contraste, thème  
- **D2** — Zones tactiles, dock, une main  
- **D3** — Formulaires, erreurs, couleur seule  
- **D4** — Charge cognitive et hiérarchie  
- **D5** — Accessibilité technique  
- **D6** — Démonstration institutionnelle

**Périmètre code typique.** `resources/css/app.css`, composants Blade, JS UI, textes d’erreur.

**Livrable.** Pas de régression fonctionnelle ; critères de la section **F** de la synthèse UX appliqués progressivement.

*Compte rendu du sprint S6 réalisé :* `docs/SPRINT-S6-UX-ACCESSIBILITE.md`.

---

### Sprint S7 — Stratégie & contenus (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`)

**Objectif.** **Pas de refactor code** sauf pages marketing / FAQ / centre d’aide. Aligner **messages**, **terminologie** (« indicateurs financiers agricoles »), **promesses** avec le produit réel.

**Thèmes D :** D1 à D6 (vision, marché, modèle éco, risques, partenariats, marque).

**Livrable.** Relecture croisée avec les domaines **1**, **3** et **6** pour éviter les écarts entre **communication** et **comportement**.

---

## 6. Sécurité : sous-ensemble par sprint (éviter l’audit infini)

| Sprint | Focus sécurité |
|--------|----------------|
| **S1** | Complet pour ce volet (auth, ownership, secrets, fichiers). |
| **S2** | Secrets en prod, permissions disque, exposition `/up` et logs. |
| **S3** | Pas d’exposition d’autres exploitations via IDs dans l’API ; cohérence des filtres par utilisateur. |
| **S4** | Pas de contournement abonnement ; callbacks signés / validés ; pas de fuite de données dans les URLs de paiement. |
| **S5** | Tokens stockés côté client (bonnes pratiques) ; synchro offline sans dupliquer des données sensibles hors cadre. |
| **S6** | Peu de surface nouvelle ; vérifier que les correctifs UX ne cassent pas focus / clavier si a11y visé. |

---

## 7. Transversal — tests et recette

- Après **chaque sprint** : jeu de **scénarios manuels** tirés des sections **D** du domaine terminé.  
- **Sprint hardening** (optionnel) : reprise des régressions sur les parcours **critiques** (connexion, saisie, dashboard, PDF, abonnement).  
- Les **tests automatisés** existants : les lancer avant fusion ; en compléter **par sprint** si le projet le prévoit (sans alourdir ce document).

---

## 8. Modèle de tableau de suivi (à copier dans un tableur ou des issues)

| ID | Sprint | Synthèse (section D) | Description courte | Type | Statut |
|----|--------|----------------------|-------------------|------|--------|
| ex. | S3 | D1 | Afficher période sur le dashboard | évolution | à faire |

---

## 9. Renvois

- Synthèses détaillées : `docs/SYNTHESE-SOUTENANCE-*.md`  
- API : `docs/API_CLIENT.md`  
- Historique des évolutions code : `docs/HISTORIQUE_AMELIORATIONS.md`

---

*Document vivant : ajuster l’ordre des sprints selon l’état réel du dépôt et les contraintes de calendrier.*
