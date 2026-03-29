# Synthèse pour soutenance — Sécurité, données personnelles et confiance

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** comment l’application **protège l’accès** et les **données** aujourd’hui, les **risques** pour trois acteurs (CTO, utilisateur, institution), des **exemples concrets**, des **pistes de solution** et des **priorités**.  
**Liens avec les autres volets :** le **domaine 1** (`SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`) définit **ce qui doit être vrai** dans les chiffres ; le **domaine 3** (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`) décrit **comment** l’exploitant **navigue** ; le **domaine 4** (`SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`) traite **lisibilité** et **accessibilité** ; le **domaine 5** (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`) l’**hébergement** et l’**exploitation** ; le **domaine 6** (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`) les **paiements** et **passerelles** ; le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`) les **engagements** **vis-à-vis** **des** **financeurs** **et** **partenaires** ; ce document définit **comment** protéger les personnes et les systèmes qui manipulent ces chiffres.

---

## A. Lexique — comprendre les termes utilisés

**Authentification**  
Prouver **qui** se connecte : ici, en grande partie **numéro de téléphone** + **PIN** (code à quatre chiffres) pour l’API mobile / PWA, avec une phase **OTP** (code reçu par SMS) lors de l’inscription.

**Jeton d’API (token)**  
Chaîne secrète renvoyée après une **connexion réussie** ; le client la renvoie dans l’en-tête `Authorization` pour les requêtes protégées. Dans le projet : **Laravel Sanctum** ; le jeton est nommé **`pwa-token`** côté serveur.

**Hachage (du PIN)**  
Le PIN n’est **pas** stocké en clair en base : on enregistre une **empreinte** (hash) permettant de **vérifier** le code sans le relire tel quel.

**OTP (one-time password)**  
Code à usage limité dans le temps, envoyé par **SMS**, pour valider le numéro à l’inscription. Stocké côté serveur en **cache** avec **expiration** et **limite de tentatives**.

**Sanctum**  
Extension Laravel pour sécuriser l’**API** avec des jetons personnels.

**Données personnelles**  
Tout ce qui permet d’identifier une personne : **nom**, **prénom**, **téléphone**, **e-mail**, et par extension les **données agrégées** liées à un compte identifiable.

**RGPD**  
Règlement européen sur la protection des données ; au-delà de l’UE, il sert souvent de **référence** pour structurer la **transparence**, les **droits** des personnes et les **contrats** avec les sous-traitants.

**DPA (accord de traitement des données)**  
Contrat entre **responsable** et **sous-traitant** : qui fait quoi, où sont les données, combien de temps, etc.

**Sous-traitant**  
Prestataire qui traite des données pour le compte de l’éditeur : **hébergeur**, **routeur SMS**, **passerelle de paiement**, etc.

**Rate limiting (limitation de débit)**  
Plafond du nombre de **tentatives** de connexion ou d’appels API sur une fenêtre de temps, pour limiter le **bruteforce** (essais automatiques de PIN ou de mots de passe).

**Cloisonnement**  
Garantir qu’un utilisateur **A** ne peut pas lire ou modifier les exploitations / transactions d’un utilisateur **B** : en pratique, filtrer toutes les requêtes avec le **bon** identifiant utilisateur.

**HTTPS**  
Chiffrement du canal entre le navigateur ou l’app et le serveur ; **indispensable** en production pour limiter l’**interception** des jetons et des formulaires.

**Journal (log)**  
Fichier ou service où le serveur enregistre des **événements** ; risque si y figurent des **secrets** (codes OTP, PIN, jetons).

**CTO (Chief Technology Officer)**  
Responsable des choix **techniques**, de la **sécurité** opérationnelle et de la **dette** sécurité.

**Institution (microfinance, coopérative, partenaire)**  
Acteur qui lit des **synthèses** ou des **exports** ; exige souvent **clarté contractuelle**, **localisation** des données et **traçabilité**.

---

## B. Rôle de ce document pour ta soutenance

Tu peux t’en servir pour montrer une **deuxième couche** de maturité produit : au-delà des **formules** et des **écrans**, tu maîtrises les **risques** (accès, fuite, conformité) et les **acteurs** concernés. Les trois **lunettes** (CTO, utilisateur, institution) évitent de réduire la « sécu » à une liste d’outils sans **contexte**.

---

## C. Ce que le programme fait aujourd’hui (résumé en français)

**Inscription** : création de compte avec **téléphone** ; envoi d’un **OTP** (cache + expiration + blocage après trop d’échecs dans `OtpService`).  
**Création du PIN** : le PIN est **haché** (`Hash::make`) et enregistré ; le champ `pin_hash` est **masqué** dans les réponses JSON du modèle `User`.  
**Connexion API** : vérification téléphone + PIN ; **révocation** des anciens jetons nommés `pwa-token` puis création d’un **nouveau** jeton Sanctum.  
**Requêtes métier** : routes API protégées par **Sanctum** et, pour une partie du module, par un contrôle **d’abonnement actif** ; les contrôleurs métier filtrent en général sur **`auth()->user()->id`** pour les données liées au propriétaire (à ne pas confondre avec `Auth::id()` qui renvoie le **téléphone** dans ce projet — voir commentaire dans `User`).  
**Environnement local** : développement type **XAMPP** ; les **secrets** attendus dans `.env` (non versionné).  
**Paiement** : intégration **FedaPay** avec mode **mock** possible en développement.

Ce résumé ne remplace pas un **audit** de sécurité ni une **analyse juridique** complète.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

Chaque partie suit le même schéma : **ce qui se passe aujourd’hui**, **ce qu’on voudrait**, **un exemple concret**, puis **pistes de solution** détaillées.

---

### D1. Authentification (PIN, OTP, jetons API)

**Situation actuelle.**  
Le **PIN** comporte **quatre chiffres** : pratique sur le terrain, mais **peu de combinaisons** possibles face à une attaque **sans limite** de tentatives sur la route de **connexion**. L’**OTP** bénéficie d’une **logique de tentatives** et de **blocage** temporaire dans le service dédié. Les **jetons** Sanctum sont **renouvelés** à chaque connexion (suppression des anciens `pwa-token`).

**Pourquoi c’est gênant.**  
Un téléphone **volé** ou un **compte observé** peut permettre des **essais rapides** de PIN si rien ne ralentit l’API. Un **jeton** volé (appareil déverrouillé) donne accès au compte **jusqu’à déconnexion** ou **nouvelle connexion** ailleurs.

**Objectif après amélioration.**  
**Compenser** la faiblesse relative du PIN par des **mécanismes de défense** (limitation de débit, alertes, option de **changement de PIN**, politique de **durée** des jetons). Garder la **simplicité** pour l’exploitant.

**Exemple.**  
Quelqu’un teste **cent combinaisons** de PIN contre un numéro connu : sans **rate limiting**, le serveur accepte les requêtes ; avec une **limite** (par IP + par téléphone), les essais sont **ralentis** ou **bloqués** après un seuil.

**Intérêt pour un agent de microfinance / institution.**  
Moins de risque que des **comptes exploitants** servent de **point d’entrée** pour des abus ; image de **sérieux** sur la **protection des dossiers**.

---

#### Pistes de solution (D1 — à ne pas oublier)

**Côté serveur**  
- **Rate limiting** sur `POST /api/auth/connexion` (et éventuellement sur la création de PIN) : par **téléphone**, par **IP**, avec **délai** progressif ou blocage temporaire.  
- **Journalisation** des **échecs** de connexion (sans enregistrer le PIN saisi) pour **détecter** les abus.  
- **Politique de jetons** : durée de vie **limitée** + **rafraîchissement** ; documenter le stockage côté PWA (risque **localStorage** vs mémoire).  
- Conserver la **révocation** à la nouvelle connexion ; ajouter une route **déconnexion** clairement utilisée par le client mobile.

**Côté produit (utilisateur)**  
- Texte court : **ne pas partager** le PIN ; **déconnexion** sur téléphone prêté ; verrouillage de l’écran du téléphone.  
- **Changement de PIN** (parcours authentifié) dans une **V2** si absent.

**Formulation courte (mémoire / oral)**  
*« Le PIN reste simple pour le terrain ; la sécurité repose aussi sur la limitation des tentatives, la gestion des jetons et l’éducation utilisateur. »*

**Priorisation**  
- **V1** : rate limiting connexion + message utilisateur.  
- **Suite** : politique d’expiration des jetons, changement de PIN, monitoring.

---

### D2. Journaux, traces et fuite de secrets

**Situation actuelle.**  
En **développement local**, des pratiques utiles au debug (par exemple **OTP** ou indices de vérification **journalisés**) peuvent exister ; en **production**, tout **log** contenant des **codes** ou des **jetons** constitue une **fuite**.

**Pourquoi c’est gênant.**  
Un accès aux **fichiers de log** (mauvaise config serveur, copie de sauvegarde) expose des **secrets** ou des **données à usage unique**. Une **institution** ou un **auditeur** peut refuser un hébergeur **non maîtrisé**.

**Objectif après amélioration.**  
**Politique de logs** : **jamais** de PIN, **jamais** de jeton complet, **jamais** d’OTP en clair en prod ; **niveaux** de log distincts **dev** / **prod**.

**Exemple.**  
Un extrait de log contient une ligne du type « code reçu : 123456 » : toute personne lisant le fichier peut **valider** l’OTP ou comprendre le **comportement** du système.

**Intérêt pour la microfinance / institution.**  
Traçabilité **sans** exposition de données sensibles ; alignement avec les **exigences** de sous-traitance et d’**audit**.

---

#### Pistes de solution (D2 — à ne pas oublier)

**Configuration**  
- **Variables d’environnement** `LOG_LEVEL`, canaux, et **filtres** pour masquer les champs sensibles dans les **exceptions** ou dumps.  
- **Revue** des `Log::` et des traces OTP en **production** : désactiver ou **redacter** (derniers chiffres uniquement si besoin de support).

**Processus**  
- **Checklist** avant mise en ligne : « **Aucun** secret dans les logs ».  
- **Rétention** : durée de conservation des logs + procédure d’**effacement**.

**Formulation courte (mémoire / oral)**  
*« Les journaux servent au diagnostic, pas au stockage de secrets ; la production applique une politique stricte de contenu. »*

**Priorisation**  
- **V1** : audit des `Log::` liés à l’auth et à l’OTP ; coupure en prod.  
- **Suite** : centralisation des logs (SIEM) si scale.

---

### D3. Cloisonnement des données et pièges de développement

**Situation actuelle.**  
Le modèle `User` utilise le **téléphone** comme **`Auth` identifier** : **`Auth::id()`** renvoie donc le **téléphone**, alors que les **clés étrangères** en base (`user_id`) pointent sur l’**identifiant numérique** de la table `users`. Le code métier documenté utilise **`auth()->user()->id`** pour les filtres « propriétaire ».

**Pourquoi c’est gênant.**  
Une **erreur** de copier-coller (`Auth::id()` dans une clause `where('user_id', …)`) peut **casser** le cloisonnement ou produire des **requêtes vides** / incohérentes — risque **grave** selon le contexte.

**Objectif après amélioration.**  
**Règle d’équipe** claire + **revue de code** sur toute route touchant `user_id` ; éventuellement **tests automatisés** sur l’**isolation** (utilisateur A ne lit pas B).

**Exemple.**  
Un nouveau endpoint utilise `Auth::id()` pour charger des exploitations : la valeur est `+229…` alors que `user_id` est `42` → **aucune** exploitation trouvée **ou**, dans un autre scénario mal conçu, **mauvaise** jointure.

**Intérêt pour la microfinance / institution.**  
Garantir que **chaque dossier** reste **attaché** au bon producteur ; base d’une **défense** en cas de litige.

---

#### Pistes de solution (D3 — à ne pas oublier)

**Développement**  
- **Convention** documentée dans le dépôt (README ou `AGENTS.md`) : *toujours **`auth()->user()->id`** pour `user_id`*.  
- **Tests** d’intégration API : deux utilisateurs, **jeux de données** séparés, vérifier **403/404** et **listes vides** correctes.  
- **Analyse statique** ou **grep** en CI sur `Auth::id()` dans les contrôleurs API.

**Formulation courte (mémoire / oral)**  
*« Le cloisonnement repose sur des filtres systématiques par propriétaire ; un piège Laravel identifiant/téléphone impose une discipline de code et des tests. »*

**Priorisation**  
- **V1** : revue manuelle + règle écrite + 1–2 tests critiques.  
- **Suite** : couverture élargie des routes `api/`.

---

### D4. Fichiers justificatifs et pièces jointes

**Situation actuelle.**  
Le schéma des **transactions** prévoit un champ **`photo_justificatif`** (nom de fichier) ; la **mise en œuvre** complète (upload, contrôle MIME, stockage hors dossier public, URL signée) doit être **vérifiée** route par route.

**Pourquoi c’est gênant.**  
Un fichier **mal contrôlé** peut être une **faille** (exécution, XSS via nom, saturation disque). Un fichier **public** avec nom **devinable** permet la **lecture** sans authentification.

**Objectif après amélioration.**  
Stockage **privé** (disque non servi directement par le web), **contrôle** type/taille, **téléchargement** uniquement pour le **propriétaire** (ou lien de partage **limité** dans le temps).

**Exemple.**  
Un justificatif est déposé sous `/storage/...` **exposé** : une URL partagée par erreur permet de voir une **facture** d’un autre utilisateur si les noms de fichiers sont **séquentiels**.

**Intérêt pour la microfinance / institution.**  
Les **justificatifs** sont souvent **sensibles** ; leur traitement doit être **explicable** dans un **DPA**.

---

#### Pistes de solution (D4 — à ne pas oublier)

**Technique**  
- **Validation** : extensions autorisées (images, PDF), **taille max**, renommage **UUID**.  
- **Contrôleur** de téléchargement : vérifie **`activite` → `exploitation` → `user_id`**.  
- **Pas** d’URL directe vers le dossier d’upload depuis `public`.

**Produit**  
- Message : *« Photo stockée de façon sécurisée, visible uniquement par vous »* (si exact).

**Formulation courte (mémoire / oral)**  
*« Toute pièce jointe est validée, stockée hors accès public et servie après contrôle du propriétaire. »*

**Priorisation**  
- **Dès que** l’upload est activé en prod : **V1** sécurité minimale (validation + accès authentifié).  
- **Suite** : compression, antivirus si volume institutionnel.

---

### D5. Secrets, environnements et mise en production

**Situation actuelle.**  
Le projet repose sur **Laravel** et **`.env`** (à ne **pas** commiter). En local, **XAMPP** est adapté au **développement**, pas comme modèle de **sécurité** production.

**Pourquoi c’est gênant.**  
Un `.env` **fuît** (backup, dépôt public) → accès **base de données**, **APP_KEY**, clés **SMS** ou **paiement**. Un serveur **non mis à jour** expose des **failles** connues.

**Objectif après amélioration.**  
**Séparer** clairement **dev** / **recette** / **prod** ; **HTTPS** obligatoire en prod ; **sauvegardes** chiffrées ; compte base de données à **privilèges limités**.

**Exemple.**  
Une clé **FedaPay** de **test** est laissée dans un fichier de **production** : des **paiements** réels partent en **sandbox** ou inversement.

**Intérêt pour la microfinance / institution.**  
Questions classiques du **questionnaire sécurité** : où sont hébergées les données, qui a accès, quelle est la **politique de sauvegarde**.

---

#### Pistes de solution (D5 — à ne pas oublier)

**Checklist production**  
- `.env` hors git ; **rotation** des secrets en cas de fuite.  
- **HTTPS**, headers de sécurité, **pare-feu** (ports DB fermés depuis Internet).  
- **Mises à jour** PHP / Laravel / dépendances Composer.  
- **Sauvegardes** testées (restauration au moins une fois).

**Formulation courte (mémoire / oral)**  
*« La confiance repose sur des secrets bien gérés, un environnement de production durci et des sauvegardes vérifiables. »*

**Priorisation**  
- **P0** avant ouverture publique : HTTPS + secrets + droits DB.  
- **P1** : sauvegardes, monitoring.  
- **P2** : durcissement avancé (WAF, pentest).

---

### D6. Cadre institutionnel (RGPD minimal, contrats, exploitation des synthèses)

**Situation actuelle.**  
L’application manipule des **données personnelles** et des **données agricoles** liées à des personnes. Les **synthèses** (PDF, tableaux de bord) peuvent être utilisées par des **tiers** (conseiller, microfinance). Les **sous-traitants** (SMS, hébergement, paiement) sont en jeu.

**Pourquoi c’est gênant.**  
Sans **transparence** (politique de confidentialité, finalités, durées) et sans **cadre contractuel** (DPA), une **institution** hésite à **recommander** l’outil ; un **utilisateur** ne sait pas **qui** traite quoi.

**Objectif après amélioration.**  
**Registre** des traitements (interne) ; **mentions** claires pour l’utilisateur ; **procédures** d’accès / rectification / suppression ; **DPA** avec les sous-traitants ; alignement des **exports** avec le **domaine 1** (période, complétude des données — pas seulement la sécu).

**Exemple.**  
Une coopérative souhaite **payer** l’abonnement de ses adhérents : il faut savoir **qui** est **responsable** du traitement des **factures** et des **coordonnées**.

**Intérêt pour la microfinance / institution.**  
**Lisibilité** du **rôle** de chacun ; possibilité d’**inscrire** l’outil dans une **politique** conformité du partenaire.

---

#### Pistes de solution (D6 — à ne pas oublier)

**Juridique / produit**  
- **Politique de confidentialité** et **CGU** : finalité, base légale, durée, droits, contact.  
- **DPA** avec hébergeur, SMS, FedaPay ; liste des **sous-traitants** accessible.  
- Parcours **« demander la suppression du compte »** (même traitement manuel au début).

**Lien métier**  
- Rappel sur les **PDF** : période, avertissement **données insuffisantes**, cohérence avec les **indicateurs** (voir synthèse domaine 1).

**Formulation courte (mémoire / oral)**  
*« La confiance institutionnelle combine sécurité technique et cadre transparent : contrats, droits des personnes, et synthèses interprétables. »*

**Priorisation**  
- **V1** : pages légales + registre interne + DPA hébergeur.  
- **Suite** : portail conformité, export de données personnelles structuré.

---

## E. Récapitulatif en une lecture (sans tableau)

**Auth** : PIN simple → **compenser** par rate limiting, jetons, éducation (voir **pistes D1**).

**Logs** : pas de **secrets** en production (voir **pistes D2**).

**Cloisonnement** : discipline **`auth()->user()->id`** et tests (voir **pistes D3**).

**Fichiers** : validation, stockage **privé**, accès **contrôlé** (voir **pistes D4**).

**Prod** : HTTPS, secrets, sauvegardes (voir **pistes D5**).

**Institution** : transparence, DPA, droits, lien avec la **qualité** des synthèses (voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0 (à faire en premier)** — ce sans quoi la confiance est fragile :  
pas de **fuite** de secrets (logs, `.env`) ; **cloisonnement** vérifié sur les routes critiques ; **HTTPS** et secrets en **production**.

**Priorité 1 (ensuite)** — ce qui améliore la **résilience** :  
**rate limiting** sur la connexion ; politique des **jetons** ; **fichiers** sécurisés dès l’upload ; **pages** légales et **DPA** hébergeur.

**Priorité 2 (amélioration continue)** — ce qui affine le produit institutionnel :  
changement de PIN, monitoring, pentest, export RGPD structuré, WAF.

---

## G. Ce que cette synthèse ne remplace pas

Elle ne remplace pas un **audit de sécurité** (pentest, revue OWASP complète), ni un **conseil juridique** sur le RGPD ou le droit local applicable au Bénin / à l’Afrique de l’Ouest. Le volet **parcours & écrans** est traité dans `SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md` ; les **tests** automatisés peuvent faire l’objet d’un complément ultérieur.

**Implémentation dans le dépôt (sprint S1)** — Pour le **tableau de synthèse** des mesures réellement en place (rate limiting, logs, justificatifs, pages légales, checklist production), voir **`docs/SPRINT-S1-SECURITE-DONNEES.md`**.

---

## H. Texte court pour l’oral (environ trente secondes)

*« Au-delà des indicateurs, AgroFinance+ doit protéger les comptes et les données personnelles : authentification par téléphone et PIN, jetons API, OTP à l’inscription, et filtrage des données par exploitant. Les risques principaux sont le PIN court sans limitation de tentatives, les journaux qui pourraient contenir des secrets, et les pièges d’identifiant entre téléphone et identifiant base de données. La production exige HTTPS, secrets maîtrisés et sauvegardes ; les partenaires attendent en plus transparence RGPD et contrats avec les sous-traitants. Les pistes prioritaires sont le rate limiting, la politique de logs, la revue du cloisonnement et le cadre institutionnel minimal. »*

---

*Document à faire évoluer avec l’audit complémentaire du code, les choix d’hébergement définitifs et le volet produit & parcours.*
