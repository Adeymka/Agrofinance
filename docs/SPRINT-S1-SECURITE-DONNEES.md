# Sprint S1 — Sécurité & données

**Statut : terminé** (jalons code : rate limiting / logs / tests ; compléments D4 & D6 : lot justificatifs + pages légales).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`

Ce document est la **source de vérité** pour savoir ce qui a été fait pour couvrir les thèmes **D1 à D6** de la synthèse sécurité, et ce qui reste **hors produit** (contrats prestataires, audit externe).

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| Limiter le bruteforce sur le PIN (connexion) | **Oui** — `throttle:auth-connexion` (10/min par téléphone + IP), API + web ; 429 JSON. |
| Ne pas exposer secrets dans les logs | **Oui** (zones ciblées) — OTP loggable uniquement en local ; échec connexion API → IP seule. |
| Cloisonnement `user_id` | **Oui** — revue API + test 404 cross-user ; règle `auth()->user()->id` documentée. |
| Fichiers justificatifs sécurisés | **Oui** (lot complémentaire) — service dédié, disque privé, API + web, `has_justificatif` sans chemin en JSON. |
| Transparence minimale (RGPD / usage) | **Oui** (lot complémentaire) — `/confidentialite`, `/conditions-utilisation`, liens footer + inscription. |
| Checklist production (secrets, HTTPS) | **Doc** — section 5 ci-dessous ; exécution = **sprint infra / exploitation** (S2). |

---

## 2. Thèmes D1–D6 — état final

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** Authentification | **Fait** | Rate limiting sur `POST` connexion API et web ; handler **429** API (`TOO_MANY_ATTEMPTS`) ; déconnexion API existante. *Non fait dans ce périmètre :* rate limiting sur OTP / inscription (piste priorité 2 synthèse). |
| **D2** Journaux / secrets | **Fait** | OTP uniquement si `app()->isLocal()` ; log warning échec connexion API sans PIN. |
| **D3** Cloisonnement | **Fait** | Contrôleurs métier API ; test `AuthFlowTest::test_api_exploitation_of_another_user_returns_404`. |
| **D4** Fichiers (justificatifs) | **Fait** | `TransactionJustificatifService`, stockage `storage/app/justificatifs/`, routes API + web, propriété vérifiée. Voir aussi `docs/API_CLIENT.md` et `HISTORIQUE_AMELIORATIONS.md` (section justificatifs). |
| **D5** Secrets & production | **Doc** | Pas de `.env` versionné. Checklist **§5** ; mise en œuvre serveur = hors ce sprint code. |
| **D6** Cadre RGPD / institutionnel | **Partiel (produit)** | Pages confidentialité + CGU + liens. *Hors app :* DPA avec hébergeur / SMS / paiement, registre des traitements, conseil juridique. |

---

## 3. Routes / API — ownership (cartographie)

Les contrôleurs API métier sous `auth:sanctum` + `subscribed` filtrent par `auth()->user()->id` (ou `Activite::pourUtilisateur`, `whereHas` exploitation). **`Auth::id()`** ne doit pas servir pour `user_id` en base (identifiant d’auth = téléphone).

| Zone | Mécanisme |
|------|-----------|
| Exploitations, activités, transactions, indicateurs, dashboard, rapports | Filtres propriétaire documentés en sprint |
| **Justificatifs** | `TransactionJustificatifController` : `whereHas('activite.exploitation', user_id)` |

**Tests automatisés :** `tests/Feature/Auth/AuthFlowTest.php` (rate limit, 404 exploitation) ; `tests/Feature/TransactionJustificatifApiTest.php` (upload, `has_justificatif`).

---

## 4. Journalisation (D2)

- **`OtpService`** : pas d’OTP en clair hors environnement local.
- **`Api\Auth\ConnexionController`** : `Log::warning` avec **IP** uniquement en cas d’échec (pas de PIN).
- **Abonnement / FedaPay** : pas de modification spécifique sprint S1 sur les traces (hors périmètre détaillé).

---

## 5. Checklist production (D5) — à cocher au déploiement

Checklist **opérationnalisée** (étapes concrètes local → prod) : **`docs/SPRINT-S2-ARCHITECTURE-INFRA.md`** §4. Compléter avec l’hébergeur réel :

- [ ] **HTTPS** obligatoire sur l’URL publique (pas de token ou formulaire en clair sur HTTP).
- [ ] **`.env`** uniquement sur le serveur ; secrets non présents dans les dépôts ni backups publics.
- [ ] **Base de données** : compte applicatif à privilèges limités ; port non exposé Internet si possible.
- [ ] **Sauvegardes** : plan de sauvegarde + test de restauration au moins une fois.
- [ ] **PHP / Laravel / Composer** : versions supportées et mises à jour de sécurité suivies.
- [ ] **Logs** : accès restreint ; rétention définie ; pas de secrets dans les fichiers de log en prod.

---

## 6. Recette manuelle recommandée

**S1 — auth & cloisonnement**

1. Connexion API avec mauvais PIN **plus de 10 fois** en une minute → **429** + JSON `TOO_MANY_ATTEMPTS`.
2. Token utilisateur A → `GET /api/v1/exploitations/{id_B}` → **404**.
3. Requête métier sans `Authorization: Bearer` → **401**.

**Compléments D4 / D6**

4. Déposer un justificatif (web ou `POST` API multipart) → `has_justificatif` à true ; autre utilisateur → pas d’accès au fichier.
5. Ouvrir `/confidentialite` et `/conditions-utilisation` ; vérifier les liens depuis le footer public et la page d’inscription.

---

## 7. Fichiers et jalons code

### Lot initial (auth, logs, tests)

- `app/Providers/AppServiceProvider.php` — `RateLimiter::for('auth-connexion', …)`
- `routes/api.php`, `routes/web.php` — `throttle:auth-connexion`
- `bootstrap/app.php` — JSON **429** pour l’API
- `app/Http/Controllers/Api/Auth/ConnexionController.php`
- `app/Services/OtpService.php` (commentaire D2)
- `tests/Feature/Auth/AuthFlowTest.php`
- `tests/Feature/ExampleTest.php`, `tests/Feature/Sprint5Test.php` — alignement schéma / redirection (CI)

### Lot complémentaire (D4 fichiers + D6 pages légales)

- `app/Services/TransactionJustificatifService.php`
- `app/Http/Controllers/Api/TransactionJustificatifController.php`
- `app/Http/Controllers/Web/TransactionController.php`, `Api/TransactionController.php`
- `app/Models/Transaction.php`
- `resources/views/transactions/create.blade.php`, `edit.blade.php`
- `resources/views/public/confidentialite.blade.php`, `conditions-utilisation.blade.php`
- `resources/views/layouts/app-public.blade.php`, `auth/inscription.blade.php`
- `app/Http/Controllers/Web/PublicController.php`
- `docs/API_CLIENT.md`

---

## 8. Ce qui reste volontairement hors sprint S1

- Audit de sécurité externe (pentest), WAF, monitoring avancé.
- Contrats **DPA** avec sous-traitants (hébergeur, SMS, FedaPay) — à traiter au niveau **juridique / commercial**.
- Rate limiting sur **OTP** / **inscription** (piste d’amélioration continue).
- Export structuré des données personnelles « portabilité RGPD » (priorité 2 synthèse).

---

*Document figé pour clôture sprint S1 — à actualiser si de nouvelles mesures de sécurité sont ajoutées au produit.*
