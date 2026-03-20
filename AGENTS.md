## Learned User Preferences
- Toujours repondre en francais.
- Preferer un accompagnement pas a pas pour les tests et validations.
- Fournir les instructions de test une par une plutot qu'un bloc complet.
- En cas de 401 Non authentifie sur l'API, rappeler la verification du header Authorization (Bearer + token), de la variable d'environnement Postman, et la reconnexion via POST /auth/connexion si le token a ete revoque.
- Pour la recette des sprints API, proposer des etapes numerotees (ex. Sprint 5 : tests 1 a 8) avec resultats attendus par code HTTP.

## Learned Workspace Facts
- Projet Laravel sous XAMPP : URL publique `http://localhost/agrofinanceplus/public` ; API `http://localhost/agrofinanceplus/public/api` (`base_url` sans slash final).
- Commandes PHP en CLI : utiliser `C:\xampp1\php\php.exe` sur ce poste (XAMPP du projet).
- Si MySQL/MariaDB renvoie "Unknown collation utf8mb4_0900_ai_ci", aligner `charset` et `collation` dans `config/database.php` sur une collation supportee par le serveur (ex. utf8mb4_unicode_ci).
- Lien de partage PDF (sans auth, route web) : `GET {APP_URL}/partage/{lien_token}` ; le token est celui renvoye par la generation de rapport, pas un placeholder Postman.
- L'authentification API est configuree avec Sanctum et des routes sous `api/auth` ; les handlers d'exceptions JSON pour l'API doivent reconnaitre les URLs ou le chemin contient le segment `api` (ex. `.../public/api/...` sous XAMPP), pas seulement le motif de chemin `api/*`.
- Pour `user_id` et les filtres SQL par proprietaire, utiliser `auth()->user()->id` ; ne pas utiliser `Auth::id()` pour ces cas, car l'identifiant d'auth est le telephone.
- Auth web : les redirections "invite a se connecter" doivent cibler la route nommee `connexion` (pas `login`). Parcours inscription : `/inscription` puis `/verification-otp` puis `/creer-pin` puis connexion ; en environnement local, le code OTP est aussi journalise dans `storage/logs/laravel.log` (lignes `[OTP LOCAL]`).
- Typographie UI (app desktop, auth, dashboard) : Space Grotesk pour titres, logo et grands chiffres ; Inter pour navigation, formulaires et corps ; definies dans `resources/css/app.css` et les layouts.
- Le dashboard et la consolidation par exploitation (`activitesActives` / `calculerExploitation`) n'incluent que les activites au statut `en_cours`; les activites `termine` sont hors consolidation. Pour creer des ressources via l'API, utiliser POST avec corps JSON ; un GET avec corps ne cree pas la ressource et peut renvoyer une liste vide.
- Rapports PDF : stockage sous `storage/app/rapports/` (disque local), servis via les controleurs ; pas d'exposition directe du dossier via `public` ni besoin de `php artisan storage:link` pour ces fichiers.
- FedaPay sans cles API (developpement) : `FEDAPAY_MOCK=true` dans `.env`, puis `POST /api/abonnement/initier` et `POST /api/abonnement/finaliser-mock` ; pour les vrais paiements, `FEDAPAY_MOCK=false`, renseigner les cles FedaPay, puis `php artisan config:clear`. Le callback `GET /api/abonnement/callback` est hors middleware Sanctum ; `POST /api/abonnement/initier` reste protege.
- Depots Git : ignorer `*.sql` (dumps locaux) et `.cursor/` ; ne jamais committer `.env`.
