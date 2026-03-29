# Synthèse pour soutenance — Architecture et infrastructure

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** comment l’application est **structurée** (Laravel, API, services), comment elle est **exploitée** en **développement** vs **production**, et quelles **questions** se posent pour un **CTO**, un **ops** ou une **institution** (données, disponibilité). Des **exemples concrets**, des **pistes de solution** et des **priorités** complètent chaque thème.  
**Liens avec les autres volets :** le **domaine 2** (`SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`) traite **secrets** et **RGPD** ; le **domaine 3** (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`) les **parcours** ; le **domaine 4** (`SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`) l’**interface** ; le **domaine 6** (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`) les **callbacks** **FedaPay** et la **cohérence** **tarifaire** **avec** **l’URL** **publique** ; le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`) **due diligence** et **pérennité** côté **partenaires**. Ici, l’angle est : **stack**, **déploiement**, **files d’attente**, **stockage** et **continuité de service**.

---

## A. Lexique — comprendre les termes utilisés

**Architecture logicielle**  
Organisation du **code** : frameworks, **couches** (HTTP → contrôleurs → services → modèles), **séparation** web / API.

**Monolithe modulaire**  
Une **seule** application déployable qui **contient** à la fois l’interface **web** Blade et l’**API** JSON — typique de **Laravel** pour ce projet.

**API versionnée**  
Préfixe d’URL (ex. **`/api/v1`**) permettant d’**évoluer** les contrats sans **casser** immédiatement les clients existants.

**Queue (file d’attente)**  
Tâches **asynchrones** (génération PDF, e-mails lourds) traitées par un **worker** séparé du **thread** HTTP. Dans Laravel, connexion souvent **`database`** ou **`redis`** (`config/queue.php`).

**Worker**  
Processus longue durée **`php artisan queue:work`** ; en production, souvent supervisé par **Supervisor** ou **systemd**.

**Supervisor**  
Outil Linux qui **relance** un processus s’il **crashe** et gère **plusieurs** workers.

**XAMPP**  
Environnement **Apache + PHP + MySQL** courant en **développement local** sous Windows ; l’application peut être servie sous un **sous-dossier** (ex. `/agrofinanceplus/public`), ce qui impacte **`APP_URL`** et les **assets**.

**Déploiement**  
Mise en **production** : copie du code, **`composer install`**, **`php artisan migrate`**, **cache** de config, **permissions** `storage/` et `bootstrap/cache/`, **HTTPS**.

**Health check**  
Point HTTP **léger** (ex. **`/up`** en Laravel 11+) pour vérifier que l’**application** **répond** — utile au **load balancer** ou au **monitoring**.

**Sauvegarde (backup)**  
Copie **planifiée** de la **base** et des **fichiers** (`storage/`) vers un **stockage** externe.

**Restauration**  
Test **réel** de **reprise** à partir d’une **sauvegarde** — sans quoi la **backup** est une **illusion**.

**Infrastructure as Code (IaC)**  
Décrire les **serveurs** en **fichiers** (Terraform, Ansible) — option **mature** pour **reproductibilité**.

**SLA (Service Level Agreement)**  
Engagement de **disponibilité** (ex. 99,9 %) — souvent côté **hébergeur** ou **contrat** client.

**CTO**  
Arbitre **technique** long terme : **dette**, **scalabilité**, **risques**.

**Ops**  
Exploitation : **serveurs**, **cron**, **logs**, **disque**, **mises à jour**.

**Institution**  
Peut exiger **localisation** des **données**, **audit** d’**hébergement** ou **clause** de **reprise** en fin de contrat.

---

## B. Rôle de ce document pour ta soutenance

Tu montres que le **produit** repose sur des **choix** **explicites** (Laravel, API v1, files d’attente) et que le **passage en production** n’est pas une **copie** de XAMPP : il y a une **réflexion** **ops** et, pour les **partenaires**, une **traçabilité** **hébergement** / **continuité**.

---

## C. Ce que le programme propose aujourd’hui (résumé en français)

**Framework** : **Laravel** (routes **`web.php`** et **`api.php`**), middleware **Sanctum**, **stateful API** pour les usages qui le nécessitent, détection **plateforme** web, alias **`subscribed`**.  
**API** : préfixe **`/api/v1`** pour les routes JSON ; gestion d’**exceptions** adaptée aux URLs **XAMPP** où le segment **api** n’est pas toujours au premier niveau (`bootstrap/app.php`).  
**Santé** : route **`/up`** (health Laravel).  
**Files d’attente** : défaut **`QUEUE_CONNECTION`** typiquement **`database`** (table **`jobs`**) — les **jobs** doivent être **consommés** par un **worker** en prod.  
**Stockage** : **rapports PDF** sous **`storage/app/rapports/`** (disque local), pas exposé directement en `public`.  
**Documentation** : **README**, **Postman**, **AGENTS.md**, **HISTORIQUE_AMELIORATIONS.md** — mention **Supervisor** pour **`queue:work`**.

Ce résumé est **descriptif** ; l’**infra** réelle (fournisseur cloud, VPS) est **à** **compléter** selon le **déploiement** choisi.

**Implémentation et checklist déploiement dans le dépôt :** le compte rendu de sprint **`docs/SPRINT-S2-ARCHITECTURE-INFRA.md`** décrit l’état des lieux (routes, queues, stockage, `/up`) et une **checklist** reproductible pour passer en production.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

Chaque partie suit le même schéma : **ce qui se passe aujourd’hui**, **ce qu’on voudrait**, **un exemple concret**, puis **pistes de solution** détaillées.

---

### D1. Structure applicative (monolithe, services, API v1)

**Situation actuelle.**  
Le code est **organisé** en **contrôleurs** web et API, **services** métier **injectés**, **jobs** pour les PDF. L’**API** est **versionnée** **`v1`**, ce qui **limite** le **couplage** avec les **clients** mobiles / PWA.

**Pourquoi c’est gênant si on ne le documente pas.**  
Un **jury** ou un **investisseur** peut croire à un **prototype** **sans** **structure** ; en réalité la **base** est **prête** pour **tests** et **évolution**.

**Objectif après amélioration.**  
**Schéma** simple (1 slide) : **navigateur** → **Laravel** → **MySQL** → **storage** ; **worker** → **queue** ; **PWA** → **API v1**. **Maintenir** la **discipline** « **logique métier dans les services** ».

**Exemple.**  
Une **nouvelle** fonction « **export Excel** » : si elle est **codée** dans un **contrôleur** de **400** lignes **sans** **service**, la **dette** **augmente** ; le **pattern** actuel (services, jobs) doit **rester** la **règle**.

**Intérêt pour un CTO.**  
**Réduction** du **coût** de **changement** et **embauche** de **développeurs** sur le **même** **modèle**.

---

#### Pistes de solution (D1 — à ne pas oublier)

**Documentation**  
- **Diagramme** **architecture** dans le **mémoire** ou **annexe**.  
- **Règle** d’équipe : **nouvelle** règle métier → **service** **dédié** ou **extension** d’un **existant**.

**Formulation courte (mémoire / oral)**  
*« Un monolithe Laravel bien découpé en services supporte le web et l’API v1 sans duplication de logique métier. »*

**Priorisation**  
- **V1** : **schéma** à jour dans la **doc** du dépôt.  
- **Suite** : **modules** **DDD** ou **packages** seulement si la **taille** l’exige.

---

### D2. Développement local (XAMPP) vs production

**Situation actuelle.**  
En **local**, l’URL peut inclure **`/agrofinanceplus/public`** ; les **assets** et le **manifest PWA** doivent utiliser **`asset()`** et **APP_URL** **cohérents**. En **production**, **HTTPS**, **document root** sur **`public/`**, **pas** de **même** chemin.

**Pourquoi c’est gênant.**  
**Erreurs** **classiques** : **liens** **cassés**, **API** appelée avec **mauvaise** **base URL**, **callback** **FedaPay** **incorrect** — **déjà** **partiellement** **documenté** dans **`.env.example`** et **Postman**.

**Objectif après amélioration.**  
**Checklist** **déploiement** **répétable** ; **environnements** **séparés** (`.env` **dev** / **staging** / **prod** **jamais** **mélangés**).

**Exemple.**  
Le **callback** **paiement** pointe encore vers **`http://localhost/...`** en **prod** → **aucun** **abonnement** **activé** après **paiement** **réel**.

**Intérêt pour l’ops.**  
**Moins** de **tickets** « **ça marche chez moi** ».

---

#### Pistes de solution (D2 — à ne pas oublier)

**Checklist**  
- `APP_URL`, `ASSET_URL`, URLs **callback** **FedaPay** **web** vs **API**.  
- `php artisan config:cache` **après** **changement** **`.env`**.  
- **Permissions** : `storage/` **écrivable**, `bootstrap/cache/` idem.

**Formulation courte (mémoire / oral)**  
*« Le développement sous XAMPP est un outil ; la production exige sa propre configuration et des URLs publiques valides. »*

**Priorisation**  
- **P0** avant **mise en ligne** : **HTTPS** + **APP_URL** + **callbacks**.  
- **P1** : **environnement** **staging** **miroir**.

---

### D3. Files d’attente, jobs et Supervisor

**Situation actuelle.**  
Laravel est **configuré** pour des **queues** (souvent **`database`**). Les **jobs** (ex. **`GenerateRapportPdfJob`**) **nécessitent** un **worker** **actif**. La **documentation** mentionne **Supervisor** pour la **production**.

**Pourquoi c’est gênant.**  
Sans **worker**, les **jobs** **restent** dans **`jobs`** **indéfiniment** — l’utilisateur **croit** que le **PDF** est **« en cours »** **pour toujours**.

**Objectif après amélioration.**  
**Worker** **supervisé** ; **alerte** si la **file** **grandit** ; **retry** et **échec** **visible** (logs, **notification**).

**Exemple.**  
**100** **rapports** **demandés** : la **table** **`jobs`** **gonfle** ; le **disque** ou la **latence** **augmentent** — il faut **dimensionner** ou **throttler** côté **produit**.

**Intérêt pour le CTO.**  
**Prévisibilité** de la **charge** et **comportement** sous **pic**.

---

#### Pistes de solution (D3 — à ne pas oublier)

**Ops**  
- Fichier **Supervisor** versionné ou **documenté** (chemin, **user**, **nombre** de **processus**).  
- **Horizon** (Redis) **optionnel** si la **charge** **justifie**.

**Produit**  
- **Statut** du **rapport** : **en file**, **échoué**, **prêt** — **côté** **UI**.

**Formulation courte (mémoire / oral)**  
*« Les files d’attente découplent la réponse HTTP du travail lourd ; sans worker supervisé, la file est une boîte noire. »*

**Priorisation**  
- **P0** en prod : **au moins** **un** **worker** **stable**.  
- **P1** : **monitoring** **taille** **file** + **échecs** **jobs**.

---

### D4. Stockage, base de données et sauvegardes

**Situation actuelle.**  
**MySQL/MariaDB** pour les **données** **relationnelles** ; **fichiers** **PDF** et **uploads** éventuels sous **`storage/`**. **Charset** / **collation** doivent **correspondre** au **serveur** (sinon **erreurs** **migrations** connues sur **anciennes** **MariaDB**).

**Pourquoi c’est gênant.**  
**Perte** **serveur** **sans** **backup** → **perte** **métier** **utilisateurs**. **Disque** **plein** → **écritures** **PDF** **en** **échec**.

**Objectif après amélioration.**  
**Sauvegardes** **automatisées** **DB** + **`storage/`** ; **test** de **restauration** **annuel** au minimum ; **surveillance** **espace** **disque**.

**Exemple.**  
**Ransomware** ou **erreur** **humaine** **`DROP`** : seule la **backup** **hors** **serveur** **sauve** **l’historique** **agricole**.

**Intérêt pour une institution.**  
**Clause** **contractuelle** ou **questionnaire** **due diligence** sur **RPO** / **RTO** (objectifs de **perte** **maximale** et **temps** de **reprise**).

---

#### Pistes de solution (D4 — à ne pas oublier)

**Technique**  
- **Dump** **quotidien** **MySQL** + **rotation** ; **copie** **off-site** (S3, autre **région**).  
- **Quota** **alertes** à **80** % **disque**.

**Formulation courte (mémoire / oral)**  
*« Les données agricoles sont l’actif central : leur persistance repose sur la base et les sauvegardes, pas seulement sur l’application. »*

**Priorisation**  
- **P0** : **backup** **DB** **+** **preuve** de **restore**.  
- **P1** : **backup** **fichiers** **complet**.

---

### D5. Observabilité, logs et `/up`

**Situation actuelle.**  
Laravel **journalise** via **`storage/logs`**. Route **`/up`** pour **health**. Les **exceptions** **API** sont **harmonisées** en **JSON** (auth, 404).

**Pourquoi c’est gênant.**  
**Logs** **illisibles** ou **trop** **verbeux** en **prod** ; **pas** d’**agrégation** → **diagnostic** **lent** en **incident**.

**Objectif après amélioration.**  
**Niveau** **log** **adapté** à **prod** ; **option** **centralisation** (Sentry, **Laravel** **Pulse**, **stack** **ELK**) — **progressive**.

**Exemple.**  
**Pic** **500** sur **`/api/v1/transactions`** : sans **corrélation** **request_id**, le **support** **ne** **sait** **pas** **quel** **client** **impact**.

**Intérêt pour l’ops.**  
**MTTR** (temps de **réparation**) **réduit**.

---

#### Pistes de solution (D5 — à ne pas oublier)

**Court terme**  
- **Rotation** **logs** ; **LOG_LEVEL=error** en **prod** si besoin.  
- **Monitoring** **uptime** **externe** sur **`/up`** et **page** **accueil**.

**Formulation courte (mémoire / oral)**  
*« La santé de l’application se mesure par des endpoints légers et des logs exploitables sans saturer le disque. »*

**Priorisation**  
- **V1** : **uptime** **basique** + **rotation** **logs**.  
- **Suite** : **APM** / **errors** **tracking**.

---

### D6. Institution : localisation, sous-traitants et continuité

**Situation actuelle.**  
Le **lieu** **physique** des **serveurs** dépend du **choix** **d’hébergement** (non **figé** dans le **code**). Les **sous-traitants** **impliqués** peuvent inclure **hébergeur**, **SMS**, **FedaPay** — déjà **évoqués** dans le **domaine 2**.

**Pourquoi c’est gênant.**  
Un **financeur** **public** peut **exiger** **données** **au** **Bénin** ou **UE** ; un **contrat** **sans** **clause** **réversibilité** **inquiète**.

**Objectif après amélioration.**  
**Fiche** **« Hébergement & sous-traitants »** **à** **joindre** aux **annexes** **mémoire** : **pays**, **contacts**, **niveau** **de** **support**, **procédure** **incident** **données** **personnelles**.

**Exemple.**  
**Appel** **à** **projet** **européen** : **question** **« Où** **sont** **hébergées** **les** **données** **personnelles** **?** » — **réponse** **prête** **en** **une** **page**.

**Intérêt pour l’institution.**  
**Conformité** **procédurale** et **image** **de** **sérieux**.

---

#### Pistes de solution (D6 — à ne pas oublier)

**Gouvernance**  
- **DPA** avec **hébergeur** (lien **domaine 2**).  
- **Plan** **simple** **continuité** : **qui** **décide** **du** **failover**, **où** **sont** **les** **backups**.

**Formulation courte (mémoire / oral)**  
*« L’architecture technique devient lisible côté institution lorsqu’on documente où vit l’application et comment on repart après un sinistre. »*

**Priorisation**  
- **V1** : **page** **interne** **à** **l’équipe** + **extrait** **public** **sécurisé** pour **partenaires**.  
- **Suite** : **certification** **hébergeur** **ISO** **si** **exigé**.

---

## E. Récapitulatif en une lecture (sans tableau)

**Structure** : **Laravel** **modulaire**, **API** **v1**, **services** **métier** (voir **pistes D1**).

**Environnements** : **XAMPP** **≠** **prod** — **checklist** **déploiement** (voir **pistes D2**).

**Queues** : **worker** **supervisé**, **jobs** **visibles** **côté** **utilisateur** (voir **pistes D3**).

**Données** : **backup** **DB** **+** **storage**, **test** **restore** (voir **pistes D4**).

**Exploitation** : **logs**, **`/up`**, **monitoring** **léger** (voir **pistes D5**).

**Institution** : **localisation**, **sous-traitants**, **continuité** (voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0 (à faire en premier)** — ce sans quoi la **prod** **ment** :  
**URLs** et **HTTPS** **corrects** ; **worker** **queue** **actif** ; **sauvegardes** **DB** **testées** **une** **fois**.

**Priorité 1 (ensuite)** — ce qui **réduit** **l’angoisse** **ops** :  
**staging** ; **monitoring** **uptime** ; **rotation** **logs** ; **documentation** **Supervisor**.

**Priorité 2 (amélioration continue)** :  
**Redis** pour **queues** / **cache** ; **IaC** ; **multi-région** ; **SLA** **formalisé** avec **hébergeur**.

---

## G. Ce que cette synthèse ne remplace pas

Elle ne remplace pas un **audit** **d’infrastructure** **sur** **site**, ni un **plan** **de** **reprise** **d’activité** **certifié**, ni le **choix** **définitif** **d’un** **cloud** **provider**. Elle **complète** le **domaine 2** en **insistant** sur **l’exploitation** **réelle** des **serveurs** et **files**.

---

## H. Texte court pour l’oral (environ trente secondes)

*« AgroFinance+ est un monolithe Laravel qui sert le web et une API versionnée v1, avec la logique métier dans des services et des jobs pour les tâches lourdes comme les PDF. En production, il faut découpler le développement local XAMPP de la configuration HTTPS et des URLs publiques, faire tourner un worker de file d’attente supervisé, et sauvegarder la base et le stockage avec des restaurations testées. Les institutions posent des questions sur l’hébergement des données et la continuité : il faut documenter sous-traitants et procédures. Les priorités sont un déploiement reproductible, des files traitées, et des sauvegardes fiables avant toute optimisation avancée. »*

---

## I. Les trois acteurs — rappel synthétique

| Acteur | Question centrale |
|--------|-------------------|
| **CTO / lead dev** | La **structure** du code et les **choix** **techniques** **supportent-ils** **l’évolution** **sans** **dette** **cachée** ? |
| **Ops / hébergeur** | Les **serveurs**, **queues**, **logs** et **sauvegardes** **tiennent-ils** **la** **charge** **et** **les** **incidents** ? |
| **Institution** | **Où** **vivent** **les** **données**, **qui** **opère**, **comment** **repart-on** **après** **un** **sinistre** ? |

---

*Document à faire évoluer avec le schéma d’hébergement réellement retenu et les procédures de backup validées en production.*
