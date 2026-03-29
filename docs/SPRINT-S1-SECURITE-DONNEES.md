# Sprint S1 — Sécurité & données (résultat)

Référence plan : `PLAN-CORRECTIONS-PAR-SPRINT.md` — synthèse : `SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`.

## Routes / API revues pour l’ownership (cartographie)

Les contrôleurs API métier sous `auth:sanctum` + `subscribed` filtrent déjà par `auth()->user()->id` (ou scopes `Activite::pourUtilisateur`, `whereHas` exploitation). Aucune utilisation dangereuse de `Auth::id()` trouvée dans le code applicatif (seulement commentaires / docs).

| Zone | Mécanisme |
|------|-----------|
| `GET/POST/PUT /api/v1/exploitations` | `where('user_id', auth()->user()->id)` |
| `ActiviteController` | `Activite::pourUtilisateur` + `Exploitation::where('user_id', …)` pour création |
| `TransactionController` | `whereHas('activite.exploitation', user_id)` |
| `IndicateurController` | `pourUtilisateur` / `Exploitation::where('user_id', $user->id)` |
| `DashboardController` | `$user->id` via `Auth::user()` (modèle complet) |
| `RapportController` | `whereHas('exploitation', user_id)` |

**Tests ajoutés** : `AuthFlowTest::test_api_exploitation_of_another_user_returns_404`.

## Journalisation — pas de secrets en clair (zones modifiées / vérifiées)

- **`OtpService`** : le code OTP n’est écrit dans les logs que si `app()->isLocal()` (inchangé, commentaire D2 renforcé).
- **`Api\Auth\ConnexionController`** : en échec de connexion, log **warning** avec **IP uniquement** (pas de PIN, pas de téléphone complet si on évite le PII — ici IP seule).
- **`AbonnementService` / mock** : non modifiés ce sprint (hors périmètre détaillé FedaPay) ; pas de nouveau jeton en log.

## Thèmes D1–D6 (statut)

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** Auth / PIN / jetons | **Corrigé (partiel)** | Rate limiting `auth-connexion` : **10 requêtes/minute** par couple `(téléphone, IP)` sur `POST /api/v1/auth/connexion` et `POST …/connexion` (web). Réponse API **429** JSON uniforme (`code` : `TOO_MANY_ATTEMPTS`). Déconnexion API existante. |
| **D2** Logs / secrets | **Corrigé (partiel)** | OTP toujours réservé au local ; échecs connexion API journalisés sans PIN. |
| **D3** Cloisonnement | **Déjà OK + test** | Revue des contrôleurs API ; test d’isolement `404` sur exploitation d’un autre utilisateur. |
| **D4** Fichiers justificatifs | **Reporté** | Champ `photo_justificatif` en schéma ; **aucune route d’upload** dans le dépôt — sécurisation à traiter **à l’implémentation** (validation MIME, disque privé, contrôle propriétaire). |
| **D5** Secrets / prod | **Doc** | `.env` non versionné (voir `.env.example`). Checklist prod : HTTPS, secrets, droits DB — voir synthèse domaine 5 / infra. |
| **D6** RGPD / institutionnel | **Reporté** | Pages légales / DPA : pas de modification juridique ce sprint ; voir roadmap synthèse. |

## Recette manuelle suggérée

1. Connexion API avec mauvais PIN **plus de 10 fois** en une minute → **429** avec message JSON.
2. Token valide utilisateur A → `GET /api/v1/exploitations/{id}` d’une exploitation de B → **404**.
3. Requête métier sans `Authorization: Bearer` → **401** (`Non authentifié.`).

## Fichiers touchés

- `app/Providers/AppServiceProvider.php` — `RateLimiter::for('auth-connexion', …)`
- `routes/api.php` — `throttle:auth-connexion` sur connexion API
- `routes/web.php` — idem web
- `bootstrap/app.php` — rendu JSON **429** pour l’API
- `app/Http/Controllers/Api/Auth/ConnexionController.php` — log d’échec sans PIN
- `app/Services/OtpService.php` — commentaire D2
- `tests/Feature/Auth/AuthFlowTest.php` — tests rate limit + cloisonnement
