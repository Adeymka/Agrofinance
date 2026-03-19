# Rapport Sprint 2 - Authentification API (AgroFinance+)

## 1) Contexte et objectif du Sprint 2

Le Sprint 2 avait pour but de mettre en place un systeme d'authentification API simple et securise pour l'application AgroFinance+.

Objectifs metier:
- Permettre a un utilisateur de s'inscrire avec son numero de telephone.
- Verifier l'identite avec un code OTP.
- Definir un PIN a 4 chiffres.
- Se connecter et recevoir un token API.
- Recuperer son profil via `/auth/me`.
- Se deconnecter proprement.
- Refuser tout acces aux routes protegees sans token valide.

Objectif securite:
- Verifier explicitement que `GET /auth/me` sans token renvoie bien `401` avec le message `Non authentifie.`

---

## 2) Ce qui a ete implemente (vue globale)

Le Sprint 2 a introduit un flux complet d'authentification base sur:
- OTP (code temporaire),
- PIN (code secret utilisateur),
- Token Sanctum (authentification API stateless).

Fichiers principaux ajoutes/modifies:
- `routes/api.php`
- `bootstrap/app.php`
- `app/Services/OtpService.php`
- `app/Models/User.php`
- `app/Http/Controllers/Api/Auth/InscriptionController.php`
- `app/Http/Controllers/Api/Auth/VerificationOtpController.php`
- `app/Http/Controllers/Api/Auth/PinController.php`
- `app/Http/Controllers/Api/Auth/ConnexionController.php`
- `app/Http/Controllers/Api/Auth/MeController.php`
- `app/Http/Middleware/VerifierAbonnement.php`
- `app/Models/Abonnement.php`
- `app/Models/Exploitation.php`

---

## 3) API livree (endpoints du Sprint 2)

### Routes publiques (sans token)
- `POST /auth/inscription`
- `POST /auth/verification-otp`
- `POST /auth/renvoyer-otp`
- `POST /auth/creer-pin`
- `POST /auth/connexion`

### Routes protegees (token Sanctum requis)
- `POST /auth/deconnexion`
- `GET /auth/me`

---

## 4) Explication simple du flux utilisateur

1. L'utilisateur s'inscrit (`inscription`).
2. Le systeme genere un OTP 6 chiffres et l'envoie (en local: dans les logs).
3. L'utilisateur valide son OTP (`verification-otp`).
4. Il cree un PIN 4 chiffres (`creer-pin`).
5. Il se connecte avec telephone + PIN (`connexion`).
6. L'API renvoie un token Sanctum.
7. Avec ce token, il peut appeler `/auth/me`.
8. Il peut se deconnecter (`deconnexion`) et invalider son token courant.

---

## 5) Algorithmes et logique metier (expliques simplement)

## 5.1 OTP (code temporaire)

Service: `app/Services/OtpService.php`

Logique:
- Generation d'un code aleatoire a 6 chiffres.
- Stockage en cache avec expiration de 10 minutes.
- Suivi du nombre de tentatives.
- Blocage 15 minutes apres trop d'erreurs.

Parametres techniques:
- Duree OTP: 10 minutes.
- Nombre max de tentatives: 5.
- Blocage apres depassement: 15 minutes.

Pourquoi cette approche:
- Le cache est rapide et simple.
- Evite de creer une table OTP au debut.
- Suffisant pour un MVP/API de sprint.

Limite:
- Si le cache est vide/nettoye, l'OTP disparait.
- Pas d'historique OTP en base.

## 5.2 PIN utilisateur

Controller: `PinController`

Logique:
- Validation du format (4 chiffres + confirmation).
- Recherche user par telephone.
- Hash du PIN avant sauvegarde (`Hash::make`).

Pourquoi hash au lieu de stocker le PIN brut:
- En cas de fuite base de donnees, le PIN n'est pas lisible directement.
- Bonne pratique de securite minimale.

## 5.3 Connexion

Controller: `ConnexionController`

Logique:
- Validation telephone + PIN.
- Verification du PIN via `Hash::check` (dans `User::verifierPin`).
- Suppression de l'ancien token nomme `pwa-token`.
- Creation d'un nouveau token Sanctum.

Pourquoi supprimer l'ancien token:
- Evite accumulation de tokens actifs.
- Rend le comportement plus previsible (1 session principale de ce type).

## 5.4 `/auth/me` et protection des routes

Route protegee par `auth:sanctum`.

Logique:
- Si token valide: retourne les infos du profil.
- Si pas de token: renvoie `401`.

Un handling personnalise des exceptions API a ete ajoute dans `bootstrap/app.php` pour renvoyer du JSON clair:
- `AuthenticationException` -> `401` + `Non authentifie.`
- `ValidationException` -> `422`
- `ModelNotFoundException` -> `404`

Pourquoi:
- Eviter les pages HTML d'erreur en API.
- Avoir des reponses standardisees faciles a consommer cote mobile/web.

---

## 6) Problemes rencontres pendant le sprint (et solutions)

## 6.1 `pin_hash` non persiste

Symptome:
- Connexion refusait toujours avec `Numero ou PIN incorrect.`

Cause:
- `pin_hash` n'etait pas dans `fillable` du modele `User`.

Solution:
- Ajout de `pin_hash` dans `protected $fillable`.

Lecon:
- Toujours verifier les champs mass-assignables sur les donnees sensibles.

## 6.2 Classe `Abonnement` introuvable

Symptome:
- Erreur `Class "App\Models\Abonnement" not found` lors de la connexion.

Cause:
- La relation existait dans `User`, mais le modele n'existait pas.

Solution:
- Creation de `app/Models/Abonnement.php` (et `Exploitation.php` pour coherence des relations).

Lecon:
- Toute relation Eloquent referencee doit pointer vers un modele reel.

## 6.3 Erreur URL route `api//auth/me` (double slash)

Symptome:
- `The route api//auth/me could not be found`

Cause:
- `base_url` avec slash final + route commencant par slash.

Solution:
- Standardiser `base_url` sans slash final:
  - `http://localhost/agrofinanceplus/public/api`

Lecon:
- Toujours normaliser les URLs d'environnement Postman.

## 6.4 `Route [login] not defined` sur appel API non authentifie

Symptome:
- Erreur Laravel web au lieu d'une reponse JSON API propre.

Cause:
- Redirection par defaut vers route `login` sur requete non authentifiee.

Solution:
- Personnalisation du rendu des exceptions API dans `bootstrap/app.php`.

Resultat:
- Reponse API JSON claire en `401`:
  - `{"succes": false, "message": "Non authentifie."}`

## 6.5 Environnement local SMS

Contrainte:
- Pas d'envoi SMS reel en local.

Solution:
- OTP logge dans `storage/logs/laravel.log` en environnement local.

Pourquoi:
- Permet de tester tout le flux sans cout SMS pendant le developpement.

---

## 7) Pourquoi ce choix technique au lieu d'un autre

## 7.1 Sanctum vs JWT package externe

Choix:
- Sanctum.

Pourquoi:
- Natif Laravel, integration rapide, simple pour API interne/mobile.
- Moins de complexite qu'une stack JWT avancee au stade sprint.

Alternative non retenue:
- JWT custom avec gestion manuelle de rotation/revocation plus lourde.

## 7.2 OTP en cache vs OTP en base

Choix:
- Cache.

Pourquoi:
- Plus rapide a implementer.
- Suffisant pour valider le flux fonctionnel du sprint.

Limite:
- Pas de trace durable.

Evolution future:
- Ajouter table OTP (audit, suivi, anti-fraude plus poussee).

## 7.3 PIN a 4 chiffres

Choix:
- PIN court pour usage terrain/mobile.

Pourquoi:
- Facile a memoriser pour utilisateurs non techniques.

Limite:
- Espace de recherche faible (10 000 combinaisons).

Evolution future:
- Ajouter rate limit reseau + verrouillage compte + option PIN plus fort.

---

## 8) Tests effectues et resultats

## 8.1 Explication simple des tests manuels (Postman)

### Comment on a teste

On a joue le role d'un vrai utilisateur, du debut a la fin:
- creation du compte,
- validation du code OTP,
- creation du PIN,
- connexion,
- lecture du profil,
- deconnexion.

Ensuite, on a fait le test securite le plus important:
- essayer d'ouvrir une route protegee sans token.

### Ce qu'on attendait

Pour chaque etape, on devait recevoir une reponse claire:
- succes quand l'action est correcte,
- erreur quand il manque la securite (pas de token).

### Ce qu'on a obtenu

- Inscription: OK.
- Verification OTP: OK.
- Creation PIN: OK.
- Connexion: OK (token recu).
- Profil `/auth/me` avec token: OK.
- Deconnexion: OK.
- Profil `/auth/me` sans token: bloque en `401` avec `Non authentifie.`.

Conclusion simple:
- Le parcours utilisateur fonctionne.
- La protection de securite fonctionne aussi.
- Donc une personne non connectee ne peut pas lire les infos privees.

## 8.2 Incidents observes pendant les tests (et resolution)

- Incident: `Route api//auth/me not found`
  - Cause: URL mal construite (double slash).
  - Correction: normaliser `base_url` sans slash final.

- Incident: `Route [login] not defined`
  - Cause: reponse de redirection web par defaut sur appel API non authentifie.
  - Correction: gestion personnalisee des exceptions API dans `bootstrap/app.php`.

- Incident: `Numero ou PIN incorrect` apres creation PIN
  - Cause: `pin_hash` non persiste.
  - Correction: ajout de `pin_hash` dans `$fillable` du modele `User`.

## 8.3 Explication simple des tests automatiques

### C'est quoi un test automatique ici?

C'est un petit programme qui lance des appels API tout seul et verifie le resultat.
Au lieu de cliquer a la main dans Postman, le test fait le travail automatiquement.

### Ce que ces tests verifient

- Le scenario complet fonctionne du debut a la fin.
- Sans token, la route `/auth/me` refuse l'acces.
- Avec un mauvais PIN, la connexion est refusee.

### Resultat actuel

- `3 tests` automatiques passent.
- `22 verifications` internes sont correctes.

### Pourquoi c'est utile (en termes simples)

- On detecte vite un bug si quelqu'un casse une partie du login plus tard.
- On gagne du temps avant une demo.
- On a une preuve concrete que le systeme marche, pas seulement une impression.

### Ce qu'on ajoutera apres

- Tester OTP faux, OTP expire, trop de tentatives.
- Tester plus de cas d'erreurs de saisie.
- Renforcer les tests de securite avances.

---

## 9) Insuffisances actuelles (a connaitre pour la soutenance)

Voici les limites actuelles a presenter de facon transparente:

- OTP en cache uniquement (pas d'historique durable).
- OTP local via logs (pas encore un vrai SMS production valide de bout en bout).
- Pas de refresh token dedie.
- PIN sur 4 chiffres (convivial mais moins robuste).
- Pas de systeme complet de monitoring securite (ex: alertes brute force avancees).
- Middleware `VerifierAbonnement` existe mais n'est pas encore applique a des routes metier critiques dans ce sprint.

---

## 10) Corrections et ameliorations futures (plan concret)

Priorite haute:
- Ajouter un vrai provider SMS testable en preproduction.
- Ajouter rate limiting global sur endpoints sensibles (`connexion`, `verification-otp`, `creer-pin`).
- Ajouter journal d'audit securite (tentatives, blocages, IP, device).

Priorite moyenne:
- OTP stocke en base (avec hash du code OTP et date d'expiration).
- Regles de mot de passe/PIN plus fortes selon profil utilisateur.
- Endpoints de gestion session multi-appareil (liste/revocation de tokens).

Priorite basse:
- Documentation OpenAPI/Swagger de tous les endpoints auth.
- Elargir la couverture des feature tests sur les cas d'erreur avances.

---

## 11) Questions possibles a la soutenance (et reponses simples)

Q: Pourquoi avoir choisi Sanctum?
R: Parce que c'est natif Laravel, rapide a integrer et adapte a notre API mobile/web.

Q: Comment vous protegez les donnees sensibles?
R: Le PIN est hashe, jamais stocke en clair. Les routes critiques utilisent un token.

Q: Que se passe-t-il sans token?
R: L'API renvoie `401` avec `Non authentifie.`, pas une page HTML.

Q: Pourquoi OTP en log local?
R: Pour developper/tester sans cout SMS; c'est un mode dev, pas la cible finale prod.

Q: Quelle est la principale limite actuelle?
R: OTP non persiste en base et securite anti-bruteforce encore a renforcer globalement.

Q: Quelle est votre prochaine priorite?
R: Renforcer la securite (rate limit, audit, provider SMS reel) avant extension metier.

---

## 12) Bilan du Sprint 2

Le Sprint 2 a livre un socle d'authentification API fonctionnel et coherent:
- flux inscription -> OTP -> PIN -> connexion -> profil -> deconnexion,
- protection des routes par token,
- reponses JSON propres pour les erreurs API,
- test securite final valide.

Le socle est pret pour la suite, avec des axes clairs d'industrialisation securite pour les prochains sprints.

