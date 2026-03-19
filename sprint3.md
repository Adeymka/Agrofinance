# Rapport Sprint 3 — CRUD Exploitations · Activités · Transactions (AgroFinance+)

Document simple pour **soutenance** et **mémoire technique**. Vocabulaire basique.

---

## 1) Rappel du contexte

- **Projet** : AgroFinance+ — gestion financière agricole (SaaS), Bénin.
- **Stack** : Laravel 11, MySQL, API JSON + Sanctum (token).
- **Sprints 1–2** : base de données métier + authentification complète (OTP, PIN, token).

---

## 2) Objectif du Sprint 3

Mettre en place la **hiérarchie métier** complète :

**Exploitation** (ex. Ferme Akobi) → **Activité** (ex. Maïs 2025) → **Transaction** (ex. Engrais 50 000 FCFA)

avec :

- **CRUD** côté API pour exploitations, activités, transactions ;
- **Calcul d’indicateurs** (FSA) après création / mise à jour / suppression de transactions ;
- **Sécurité** : chaque utilisateur ne voit **que ses données** ; ressource inexistante ou d’un autre utilisateur → **404** (pas 403).

---

## 3) Ce qui a été livré (résumé)

### Modèles Eloquent

- `Exploitation` — liée à `User`, a plusieurs `Activite`, `Rapport` ; scope `activitesActives` (statut `en_cours`).
- `Activite` — liée à `Exploitation`, a plusieurs `Transaction` ; méthode `alerteBudget()` (seuils 70 %, 90 %, 100 % du budget prévisionnel).
- `Transaction` — liée à `Activite` ; dépenses avec `nature` (fixe / variable) ; recettes avec `nature` = `null`.
- `Rapport`, `Abonnement` — alignés avec les specs (relations / champs).

### Contrôleurs API

- `ExploitationController` — liste, création, détail (avec activités + compteurs), mise à jour.
- `ActiviteController` — liste, création, détail (transactions + `alerte_budget`), mise à jour, **clôture** (`termine` + `date_fin`).
- `TransactionController` — liste avec filtres, **création en lot** (tableau `transactions`), détail, mise à jour, suppression + **recalcul des indicateurs**.

### Service métier

- `FinancialIndicatorsService` — calcule PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR, **statut** (vert / orange / rouge), compteurs.  
  *Couche métier : pas de `Auth` dedans ; le contrôleur valide déjà que l’activité appartient à l’utilisateur.*

### Routes

Toutes les routes métier Sprint 3 sont sous **`auth:sanctum`** dans `routes/api.php` (en plus des 7 routes `/auth/*` du Sprint 2).

**Total API** : **21 routes** (7 auth + 14 métier Sprint 3).

---

## 4) Règles de sécurité (important pour la soutenance)

1. **Filtrage par utilisateur**  
   - Exploitations : `where('user_id', auth()->user()->id)`.  
   - Activités / transactions : `whereHas(...)` sur l’exploitation liée au **même** `user_id`.

2. **404 plutôt que 403**  
   Si l’ID n’existe pas **pour cet utilisateur**, on répond **404** + message générique **« Introuvable. »** — on ne dit pas « ce compte existe mais pas à toi ».

3. **Token obligatoire**  
   Sans `Authorization: Bearer ...` → **401** + **« Non authentifié. »**

4. **Piège corrigé : `Auth::id()` vs `users.id`**  
   Le modèle `User` utilise le **téléphone** comme identifiant d’auth (`getAuthIdentifierName()`). Du coup **`Auth::id()` renvoie le téléphone**, pas la clé primaire numérique.  
   Pour **`user_id`** en base, le code utilise **`auth()->user()->id`**.

---

## 5) Endpoints métier (liste simple)

| Méthode | URL (préfixe `/api`) | Rôle |
|--------|----------------------|------|
| GET | `/exploitations` | Liste mes exploitations |
| POST | `/exploitations` | Créer une exploitation |
| GET | `/exploitations/{id}` | Détail + activités |
| PUT | `/exploitations/{id}` | Mettre à jour |
| GET | `/activites` | Liste mes activités |
| POST | `/activites` | Créer (avec `exploitation_id` vérifiée) |
| GET | `/activites/{id}` | Détail + transactions + `alerte_budget` |
| PUT | `/activites/{id}` | Mettre à jour |
| POST | `/activites/{id}/cloturer` | Statut `termine` |
| GET | `/transactions` | Liste + filtres (`activite_id`, `type`, etc.) |
| POST | `/transactions` | Lot : `{ "transactions": [ ... ] }` |
| GET | `/transactions/{id}` | Détail |
| PUT | `/transactions/{id}` | Mise à jour + indicateurs |
| DELETE | `/transactions/{id}` | Suppression + indicateurs |

**Base URL locale typique (XAMPP)** :  
`http://localhost/agrofinanceplus/public/api`  
(variable Postman `base_url`, **sans** slash final).

---

## 6) Base de données — ajustements Sprint 3

Des migrations **d’ajout / alignement** ont complété les tables existantes (sans tout recréer) :

- **exploitations** : colonne **`superficie`** ; **`localisation` nullable** (un exploitant peut ne pas la renseigner tout de suite).
- **activites** : **`description`** ; enum **statut** aligné (`en_cours`, `termine`, `abandonne`, défaut `en_cours`).
- **abonnements** : **`montant`** ; enums **plan** / **statut** selon spec Sprint 3.

*Les `type` en enum côté MySQL avec validation `string|max:100` côté API sont un choix **pragmatique** (flexibilité des libellés au Bénin).*

---

## 7) Indicateurs et transactions (explication simple)

- **Dépense** : peut avoir **`nature`** `fixe` ou `variable` (pour CV / CF / CT).
- **Recette** : **`nature`** mise à **`null`** (les ventes ne sont pas classées fixe / variable comme les charges).
- Après **POST / PUT / DELETE** sur une transaction, la réponse inclut **`indicateurs`** recalculés pour l’activité concernée.

Exemple vérifiable en recette :

- Déjà vu en test : **dépense 50 000** seule → **MB = -50 000**, **statut rouge**.  
- **+ recette 450 000** → **MB = 400 000**, **statut vert**.  
- **Suppression de la recette** → retour à **MB négatif**, **rouge**.

---

## 8) Problèmes rencontrés pendant les tests — et solutions

| Problème | Cause | Solution |
|----------|--------|----------|
| Erreur SQL `user_id` + téléphone sur exploitation | `Auth::id()` = téléphone, pas `users.id` | Utiliser `auth()->user()->id` partout pour `user_id` |
| `GET /activites` avec body → `data: []` | Le body n’est pas lu en GET ; il fallait **POST** pour créer | Utiliser **POST** pour la création |
| Réponse 404 avec énorme JSON d’erreur | Sous XAMPP l’URL est `.../public/api/...` : `is('api/*')` ne matchait pas ; + `NotFoundHttpException` non gérée comme `ModelNotFoundException` | Dans `bootstrap/app.php` : détection API si segment **`api`** **ou** `api/*` ; handler pour **NotFoundHttpException** |

---

## 9) Tests manuels réalisés (Postman) — résultat

Séquence exécutée avec **`Authorization: Bearer {{token}}`** :

1. GET `/exploitations` → liste (vide au départ) OK  
2. POST `/exploitations` → **201**, exploitation créée OK  
3. GET `/exploitations/{id}` → détail OK  
4. POST `/activites` → **201**, activité créée OK  
5. GET `/activites/{id}` → détail, `alerte_budget` null si pas assez de dépenses OK  
6. POST `/transactions` (dépense 50k) → indicateurs **MB -50k**, **rouge** OK  
7. POST `/transactions` (recette 450k) → **MB 400k**, **vert** OK  
8. GET `/transactions?activite_id=...` → 2 lignes OK  
9. DELETE transaction recette → indicateurs **recalculés**, **rouge** OK  
10. POST `/activites/{id}/cloturer` → **termine** OK  
11. GET `/exploitations` **sans** token → **401** « Non authentifié » OK  
12. GET `/exploitations/99999` avec token → **404** « Introuvable. » OK (après correctif exceptions API)

**Note budget** : avec budget 200k et seulement 50k de dépenses (25 %), **pas d’alerte 70 %** — comportement normal du code.

---

## 10) Critères de validation Sprint 3 (check-list)

- [x] Dépense 50k → MB négatif, statut rouge  
- [x] Recette 450k → MB 400k, statut vert  
- [x] DELETE → indicateurs recalculés  
- [x] Clôture activité → `termine`  
- [x] Sans token → 401  
- [x] ID inexistant / pas à soi → 404 JSON uniforme  
- [ ] Alerte budget ≥ 70 % : non testée sur ce scénario (nécessite dépenses plus élevées vs budget)

---

## 11) Limites et évolutions possibles

- Tests **automatisés** Feature pour exploitations / activités / transactions (comme le Sprint 2 pour l’auth).  
- **Rate limiting** sur POST transactions / connexion.  
- **Synchronisation offline** : champ `synced` déjà présent — logique métier à étendre.  
- **Rapports PDF** : tables `rapports` prêtes, génération à venir.

---

## 12) Phrases courtes pour l’oral

- *« Chaque requête métier est liée à l’utilisateur connecté via son id en base et des `whereHas`, pour qu’un fermier ne voie jamais les données d’un autre. »*  
- *« Les transactions partent en lot ; après chaque changement, on recalcule les indicateurs financiers de l’activité. »*  
- *« On renvoie 404 « Introuvable » plutôt que 403, pour ne pas révéler qu’une ressource existe chez quelqu’un d’autre. »*  
- *« Sous XAMPP, l’API est sous `/public/api` ; on a adapté le format des erreurs JSON pour que ça marche aussi dans ce cas. »*

---

**Fin du rapport Sprint 3.**
