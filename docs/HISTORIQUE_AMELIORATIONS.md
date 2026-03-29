# Historique des améliorations — AgroFinance+

Document de synthèse : modifications apportées au code et intérêt métier / technique de chaque évolution.

---

## Sprint S1 — Sécurité & données

| Modification | Avantage |
|--------------|----------|
| **Rate limiting** `auth-connexion` (10/min par téléphone + IP) sur **POST** connexion **API** et **web** | Réduit le bruteforce sur le PIN à 4 chiffres (synthèse sécurité D1). |
| Réponse **JSON 429** pour l’API (`TOO_MANY_ATTEMPTS`) via handler `ThrottleRequestsException` | Comportement prévisible pour les clients mobiles / Postman. |
| **Log** d’échec de connexion API (**IP** uniquement, jamais le PIN) | Traçabilité légère sans fuite de secret (D2). |
| Tests **Feature** : rate limit après 10 échecs, **404** si accès à l’exploitation d’un autre utilisateur | Documentation exécutable du cloisonnement (D3). |

Détail et cartographie des routes : `docs/SPRINT-S1-SECURITE-DONNEES.md`.

---

## Justificatifs de transaction & pages légales

| Modification | Avantage |
|--------------|----------|
| **`TransactionJustificatifService`** + stockage sous `storage/app/justificatifs/` (disque **local**, noms UUID) | Fichiers non exposés par URL publique directe ; validation MIME / taille. |
| API **POST/GET/DELETE** `/transactions/{id}/justificatif` ; modèle **Transaction** : `has_justificatif` en JSON, chemin masqué | Clients mobiles / Postman ; pas de fuite du chemin disque. |
| Web : champs fichier sur **création** / **édition** de transaction + téléchargement **`GET …/transactions/{id}/justificatif`** | Parcours exploitant cohérent avec le web. |
| Pages **`/confidentialite`** et **`/conditions-utilisation`** (texte RGPD/CGU minimal) + liens **footer** `app-public` et **inscription** | Transparence utilisateur sans jargon inutile. |
| Doc **`API_CLIENT.md`** section justificatifs | Alignement documentation / code. |

---

## 1. PDF et rapports

| Modification | Avantage |
|--------------|----------|
| **`RapportService`** : `preparerPdfRapport()` (PDF en mémoire), `stockerPdfLocal()`, `creerEtDispatcher()` qui enchaîne création, génération et stockage | Logique centralisée, moins de duplication entre flux synchrone et futur traitement async. |
| **`GenerateRapportPdfJob`** avec `try/catch` autour de `Storage::put` et journalisation d’erreur | En cas d’échec disque : trace claire, possibilité de retry / échec explicite côté queue. |
| **`resoudrePeriode()`** : `periode_debut` / `periode_fin` **nullable** + **fallback** unifié (API et Web), puis contrôle `fin >= début` | Comportement identique selon le canal ; validation cohérente après fusion des dates. |

---

## 2. OTP et téléphone

| Modification | Avantage |
|--------------|----------|
| **`OtpService::normaliserTelephone()`** — suppression de la branche ternaire morte (`? :` aux deux branches identiques) | Code plus lisible ; retour par défaut explicite (`+` + chiffres). |
| Déplacement de la logique depuis **`Web\Auth\InscriptionController`** vers **`OtpService`** | Réutilisable (API, autres parcours) ; un seul point de vérité pour le format `+229`. |

---

## 3. Tableau de bord

| Modification | Avantage |
|--------------|----------|
| **`DashboardService`** : `resoudreHeroEtGraphique()`, `construireCartesActivites()`, `alertesDepuisCartes()` | Contrôleur web allégé ; règles « hero / cartes / alertes budget » isolées et plus faciles à tester. |
| Enregistrement **singleton** dans **`AppServiceProvider`** | Aligné avec les autres services métier. |
| Paramètre **`?exploitation_id=`** sur le dashboard (multi-exploitation) | Sélection d’exploitation explicite, données non mélangées. |

---

## 4. Abonnement et FedaPay

| Modification | Avantage |
|--------------|----------|
| Centralisation **`traiterCallbackFedaPay()`** et **`initierPaiementFedaPay()`** dans **`AbonnementService`** | Un seul cœur métier pour le web et l’API ; moins d’écarts de comportement. |
| Flux API plus **stateless** (réduction de l’usage de `Session` sur initiation / callback API), idempotence côté cache | Adapté aux clients mobiles et intégrations serveur ; moins de dépendance à la session PHP. |

---

## 5. Authentification, activités, abonnement (durée)

| Modification | Avantage |
|--------------|----------|
| OTP : journalisation / exposition du code **uniquement en environnement local** | Limite la fuite d’OTP en préproduction / production. |
| **`ActiviteStatutService`** avec **`DB::transaction()`** et **`lockForUpdate()`** pour clôture / abandon d’activité | Limite les conditions de course et les états incohérents. |
| Scope **`Activite::pourUtilisateur($userId)`** | Requêtes plus lisibles ; filtrage « propriétaire » homogène. |
| **`dureeAbonnementEnJours()`** avec **`match()`** explicite sur les plans | Comportement des durées lisible et maintenable. |

---

## 6. Autorisation (Gates Laravel)

| Modification | Avantage |
|--------------|----------|
| Cinq **`Gate::define()`** dans **`AppServiceProvider`** : `gerer-exploitation`, `gerer-activite`, `gerer-transaction`, `gerer-rapport`, `abonnement-actif` | Base prête pour `authorize()`, `@can` ou policies ; règles de propriété et d’abonnement exprimées une fois. |

---

## 7. API versionnée (`/api/v1`)

| Modification | Avantage |
|--------------|----------|
| **`Route::prefix('v1')`** dans **`routes/api.php`** | Versionnement explicite ; possibilité d’introduire une **`v2`** sans couper immédiatement les clients **`v1`**. |
| Mise à jour associée : **`VerifierAbonnement`**, **`Api\AbonnementController`** (URL de callback FedaPay), **tests**, **vues** (`meta api-base`, fetch graphique dashboard), **`offline-transactions.js`**, documentation | FedaPost, clients mobiles, PWA et docs utilisent la même base d’URL. |
| **`docs/POSTMAN.md`** et commentaires **`.env.example`** (callback API vs callback web) | Configuration FedaPay et Postman sans ambiguïté. |
| Mise à jour de **`audit.md`**, **`LOGIQUE_FONCTIONNALITE.md`**, **`docs/sprint5.md`**, **`AGENTS.md`**, etc. | Cohérence entre code, audit et guides internes. |

---

## 8. Documentation et indicateurs

| Modification | Avantage |
|--------------|----------|
| **PHPDoc détaillé** sur **`FinancialIndicatorsService`** (paramètres, formes de tableaux retour) | Meilleure lisibilité pour l’équipe et les outils (IDE, analyse statique). |
| **`README.md`** : installation, architecture, API, sécurité, **Supervisor** | Onboarding et déploiement plus simples. |
| Section **Supervisor** (worker `queue:work` sur connexion `database`) | Les jobs en file sont traités en continu en production au lieu de rester bloqués dans `jobs`. |

---

## 9. Bénéfices transverses

- **Moins de duplication** (rapports, abonnement, périodes, téléphone, dashboard).
- **Sécurité et exploitation** : OTP en local uniquement, gates prêts à l’emploi, URLs API versionnées et documentées.
- **Robustesse** : transactions + verrous sur les changements de statut d’activité, gestion d’erreur sur le stockage PDF, queues supervisées.
- **Évolutivité** : API sous **`v1`**, services découpés, documentation et audit alignés sur le code actuel.

---

## 10. Pistes de suite (hors périmètre déjà livré)

- Brancher explicitement **`GenerateRapportPdfJob`** depuis les contrôleurs si l’on veut une génération PDF **asynchrone** (aujourd’hui le flux principal reste souvent synchrone via `creerEtDispatcher()`).
- Utiliser les **Gates** dans les contrôleurs (`authorize()`) pour remplacer progressivement les contrôles dupliqués.

---

*Document généré pour suivi projet — à mettre à jour lors des prochains refactors.*
