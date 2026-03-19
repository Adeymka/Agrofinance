## Learned User Preferences
- Toujours repondre en francais.
- Preferer un accompagnement pas a pas pour les tests et validations.
- Fournir les instructions de test une par une plutot qu'un bloc complet.
- En cas de 401 Non authentifie sur l'API, rappeler la verification du header Authorization (Bearer + token), de la variable d'environnement Postman, et la reconnexion via POST /auth/connexion si le token a ete revoque.

## Learned Workspace Facts
- Le projet est une application Laravel servie via XAMPP sous `http://localhost/agrofinanceplus/public`.
- Les appels API locaux utilisent la base `http://localhost/agrofinanceplus/public/api` (`base_url` sans slash final).
- L'authentification API est configuree avec Sanctum et des routes sous `api/auth`.
- Pour `user_id` et les filtres SQL par proprietaire, utiliser `auth()->user()->id` ; ne pas utiliser `Auth::id()` pour ces cas, car l'identifiant d'auth est le telephone.
- Les handlers d'exceptions JSON pour l'API doivent reconnaitre les URLs ou le chemin contient le segment `api` (ex. `.../public/api/...` sous XAMPP), pas seulement le motif de chemin `api/*`.
- Le dashboard et la consolidation par exploitation (`activitesActives` / `calculerExploitation`) n'incluent que les activites au statut `en_cours`; les activites `termine` sont hors consolidation.
- Pour creer des ressources via l'API (activites, transactions, etc.), utiliser la methode POST avec corps JSON ; un GET avec corps ne cree pas la ressource et peut renvoyer une liste vide.
