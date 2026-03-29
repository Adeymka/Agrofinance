# Synthèse pour soutenance — Paiements et abonnement

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** comment les **formules d’abonnement** **conditionnent** l’**accès** au produit, comment le **paiement** (**FedaPay**) **s’intègre** au **web** et à l’**API**, et quelles **questions** se posent pour le **PO**, l’**exploitant payeur** et une **institution**. Des **exemples concrets**, des **pistes de solution** et des **priorités** complètent chaque thème.  
**Liens avec les autres volets :** le **domaine 1** (`SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`) lie le **plancher d’historique** au **plan** ; le **domaine 3** (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`) le **parcours** **abonnement** ; le **domaine 5** (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`) les **URLs** de **callback** en **production** ; le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`) la **viabilité** **économique** **et** **les** **scénarios** **B2B** **/** **institutionnels**. Ici, l’angle est : **prix**, **droits** et **flux monétaire**.

---

## A. Lexique — comprendre les termes utilisés

**Abonnement**  
Droit d’**utiliser** les **fonctions** **métier** **complètes** pendant une **période** ; en base, **statut** (`actif`, `essai`, `expire`…), **plan**, **dates**, **référence** de paiement.

**Plan métier**  
Valeurs **canoniques** alignées sur le **code** : en pratique **`gratuit`**, **`essentielle`**, **`pro`**, **`cooperative`** (voir **`AbonnementService::normaliserPlan`**). Les **clés de facturation** FedaPay (**`mensuel`**, **`annuel`**, **`cooperative`**) sont **mappées** vers ces plans.

**Middleware `subscribed`**  
Sur le **web**, les **routes** **métier** (tableau de bord, exploitations, transactions, rapports) exigent un **abonnement** **valide** (**actif** ou **essai** **non** **expiré**).

**FedaPay**  
Passerelle de **paiement** ; le projet utilise le **SDK** PHP, **sandbox** ou **production** selon la **configuration**.

**FEDAPAY_MOCK**  
Mode **simulation** : **aucun** appel **réseau** à FedaPay ; **cache** des **intentions** de paiement ; finalisation via **`POST .../abonnement/finaliser-mock`** (développement / démo).

**Callback**  
URL **appelée** **après** le **paiement** (redirection **navigateur**) ; la route **API** est **hors** **middleware** **Sanctum** car l’**utilisateur** **ne** **renvoie** **pas** le **Bearer**.

**Callback URL**  
Doit être **HTTPS** **accessible** **publiquement** en **production** — **alignée** sur **`APP_URL`** (voir **domaine 5**).

**Idempotence (activation)**  
La **référence** **`ref_fedapay`** **unique** **évite** **doubler** un **abonnement** si le **callback** est **rejoué**.

**PO (Product Owner)**  
Arbitre **offre**, **prix affichés** et **cohérence** **marketing** / **moteur**.

**Exploitant payeur**  
Utilisateur qui **règle** ou **choisit** une **formule** ; attend **clarté** **prix** et **accès** **immédiat** après **succès**.

**Institution**  
Peut **sponsoriser** des **abonnements**, **exiger** **factures** ou **conventions** **cadre**.

---

## B. Rôle de ce document pour ta soutenance

Tu montres que **l’argent** **n’est** **pas** **anecdotique** : il **structure** **qui** **accède** **à** **quoi** (**PDF**, **historique**, **multi-exploitations**) et **comment** le **produit** **reste** **maintenable** (**passerelle**, **callbacks**, **mock** **dev**).

---

## C. Ce que le programme fait aujourd’hui (résumé en français)

**Service central** : **`AbonnementService`** — **teste** si l’abonnement est **actif**, **normalise** le **plan**, **calcule** **droits** et **limites**.

**Tarification indicielle (FCFA)** pour l’**initiation** paiement : **1 500** (clé **`mensuel`** → plan **essentielle**), **5 000** (**`annuel`** → **pro**), **8 000** (**`cooperative`**). **Gratuit** / **essai** : **montant** **0** selon contexte.

**Droits liés au plan (extraits)**  
- **PDF** « classiques » : **essentielle**, **pro**, **cooperative** (**pas** **gratuit** seul).  
- **Dossier crédit** PDF : **pro** ou **cooperative** **uniquement**.  
- **Multi-exploitations** : **1** pour **gratuit** / **essentielle** ; **jusqu’à 5** pour **pro** ; **illimité** (**PHP_INT_MAX**) pour **cooperative**.  
- **Historique** : pour **gratuit** / **aucun**, **plancher** **environ** **6 mois** ; **pas** de **plancher** **imposé** **de** **la** **même** **façon** pour les **plans** **payants** **actifs** (**`dateDebutHistorique`** retourne **`null`** sauf gratuit/aucun).

**Paiement** : **`initierPaiementFedaPay`** — si **`FEDAPAY_MOCK=true`**, retour **mock** + **cache** ; sinon **clé** **secrète** **requise**, **transaction** FedaPay, **URL** de **paiement**. **Callback** centralisé (**`traiterCallbackFedaPay`**). **Activation** **`activer`** avec **idempotence** sur **`ref_fedapay`**.

**API** : **`/api/v1/abonnement/...`**, **callback** **documenté** **sans** **token** ; **finaliser-mock** réservé au **mode** **mock**.

Ce résumé est **fidèle** à l’**intention** du **code** ; les **tarifs** **affichés** **sur** **les** **pages** **marketing** doivent **rester** **alignés** **manuellement** sur ces **règles**.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

---

### D1. Grille tarifaire et cohérence offre / moteur

**Situation actuelle.**  
Les **montants** et **mappings** **plan** sont **centralisés** dans **`AbonnementService`** (facturation **`mensuel` / `annuel` / `cooperative`**, **normalisation** vers **plans** **base**).

**Pourquoi c’est gênant si on ne synchronise pas.**  
Une **page** **marketing** qui annonce **« Pro à X FCFA »** **différent** du **service** → **méfiance** et **support** **surchargé**.

**Objectif après amélioration.**  
**Une** **source** **de** **vérité** **affichée** (ou **générée** depuis **constantes** **partagées** **avec** **PHP**).

**Exemple.**  
Brochure **« Essentielle 2 000 »** alors que **`montantFacturation('mensuel')`** vaut **1 500** → **crise** **de** **confiance**.

**Intérêt pour le PO.**  
**Réduction** des **erreurs** **humaines** **entre** **com** et **dev**.

---

#### Pistes de solution (D1 — à ne pas oublier)

- **Fichier** **config** ou **classe** **`TarifsAffichage`** **incluse** **par** **Blade** **et** **tests**.  
- **Revue** **semestrielle** **prix** / **coût** **FedaPay**.

**Formulation courte (mémoire / oral)**  
*« Les prix à l’écran doivent refléter exactement ce que le service d’abonnement envoie à la passerelle. »*

**Priorisation**  
- **V1** : **audit** **textes** **accueil** / **abonnement** **vs** **code**.  
- **Suite** : **génération** **unique** des **montants** **côté** **serveur** **pour** **les** **vues**.

---

### D2. Parcours payeur (web, mobile, après paiement)

**Situation actuelle.**  
**Initiation** **web** **et** **API** ; **redirection** **FedaPay** ; **callback** **sans** **Bearer** ; **mock** **avec** **finalisation** **explicite** **API** en **dev**.

**Pourquoi c’est gênant.**  
Si **l’utilisateur** **paie** **mais** **ne** **revient** **pas** **sur** **le** **bon** **callback**, ou si **l’**UI** **ne** **rafraîchit** **pas** le **statut**, il **croit** **à** **une** **arnaque**.

**Objectif après amélioration.**  
**Écran** **« Paiement reçu »** + **bouton** **« Accéder au tableau de bord »** ; **gestion** **explicite** **échec** / **annulation**.

**Exemple.**  
**Paiement** **OK** **chez** **FedaPay**, **callback** **en** **erreur** **500** → **activation** **manquante** : **procédure** **support** + **idempotence** **côté** **traitement** (déjà **partielle** **via** **`ref_fedapay`**).

**Intérêt pour l’exploitant payeur.**  
**Sérénité** : **« Mon argent** **a** **servi** **à** **quelque** **chose** ».

---

#### Pistes de solution (D2 — à ne pas oublier)

- **Page** **état** **abonnement** **lisible** (**plan**, **fin**, **jours** **restants** — **`infos()`** **existe** **côté** **API**).  
- **Logs** **callback** **sans** **secrets** ; **relance** **manuelle** **documentée** pour **support**.

**Formulation courte (mémoire / oral)**  
*« Le parcours se termine par une confirmation visible, pas seulement par un enregistrement en base. »*

**Priorisation**  
- **V1** : **écran** **succès** / **échec** **clairs** **post-redirection**.  
- **Suite** : **webhook** **FedaPay** **complémentaire** **au** **callback** **navigateur** **si** **disponible**.

---

### D3. Mode mock et environnement de développement

**Situation actuelle.**  
**`FEDAPAY_MOCK=true`** **court-circuite** **FedaPay** ; **message** **API** **explicite** ; **`finaliser-mock`** **protégé** **par** **le** **flag** **mock**.

**Pourquoi c’est gênant si mal communiqué.**  
Un **démonstrateur** **oublie** **de** **finaliser** **le** **mock** → **pas** **d’**abonnement** **actif** → **routes** **`subscribed`** **bloquées**.

**Objectif après amélioration.**  
**Checklist** **démo** : **finaliser-mock** **ou** **parcours** **web** **équivalent** ; **rappel** **dans** **la** **doc** **interne**.

**Exemple.**  
**Sprint** **review** : **« L’appli** **ne** **m’ouvre** **pas** **le** **dashboard** »** — **oubli** **d’**activation** **mock**.

**Intérêt pour le PO.**  
**Démos** **reproductibles** **sans** **clés** **production**.

---

#### Pistes de solution (D3 — à ne pas oublier)

- **Bannière** **admin** **« Mock** **actif** »** en **staging**.  
- **Script** **Postman** **documenté** (**`docs/POSTMAN.md`** **déjà** **présent** **dans** **l’historique** **projet**).

**Formulation courte (mémoire / oral)**  
*« Le mock permet de développer sans argent réel, à condition de finaliser le flux comme en recette. »*

**Priorisation**  
- **V1** : **1** **parcours** **démo** **écrit** **pas** **à** **pas**.  
- **Suite** : **environnement** **sandbox** **FedaPay** **systématique** **avant** **prod**.

---

### D4. Droits fonctionnels (PDF, exploitations, historique)

**Situation actuelle.**  
**Règles** **explicites** dans **`AbonnementService`** (**`peutGenererPDF`**, **`peutGenererDossierCredit`**, **`maxExploitations`**, **`dateDebutHistorique`**).

**Pourquoi c’est gênant.**  
Si **l’UI** **affiche** **un** **bouton** **« PDF »** **pour** **un** **gratuit**, **erreur** **ou** **frustration** ; si **le** **plancher** **d’historique** **n’est** **pas** **expliqué** (**domaine** **1**), **l’utilisateur** **attribue** **l’erreur** **au** **«** **bug** **»**.

**Objectif après amélioration.**  
**Masquer** **ou** **griser** **les** **actions** **interdites** **avec** **explication** **courte** **ou** **lien** **abonnement**.

**Exemple.**  
**Gratuit** : **export** **PDF** **indisponible** — **message** **«** **Inclus** **à** **partir** **de** **Essentielle** **»** **au** **lieu** **d’un** **403** **brut**.

**Intérêt pour l’exploitant payeur.**  
**Comprendre** **ce** **qu’il** **gagne** **en** **payant**.

---

#### Pistes de solution (D4 — à ne pas oublier)

- **Composant** **«** **verrou** **»** **réutilisable** **sur** **rapports**.  
- **Rappel** **du** **plan** **actuel** **dans** **le** **profil** / **abonnement**.

**Formulation courte (mémoire / oral)**  
*« Chaque limitation métier doit avoir un libellé produit, pas seulement un refus technique. »*

**Priorisation**  
- **V1** : **PDF** **et** **multi-exploitations**.  
- **Suite** : **dossier** **crédit** **avec** **mention** **Pro/Coop**.

---

### D5. Institution : facturation, sponsoring, traçabilité

**Situation actuelle.**  
**Paiement** **principalement** **B2C** **via** **FedaPay** ; **pas** **de** **module** **«** **compte** **institution** **»** **décrit** **dans** **l’historique** **comme** **livré**.

**Pourquoi c’est gênant.**  
Une **coopérative** **qui** **veut** **payer** **100** **comptes** **veut** **une** **facture** **unique** **et** **une** **traçabilité** — **souvent** **hors** **produit** **aujourd’hui**.

**Objectif après amélioration.**  
**Process** **manuel** **documenté** **court** **terme** ; **roadmap** **B2B** / **codes** **promo** / **comptes** **rattachés** **si** **marché** **l’exige**.

**Exemple.**  
**Convention** **signée** : **virement** **→** **activation** **manuelle** **des** **comptes** **liste** **CSV** — **procédure** **interne** **et** **RGPD**.

**Intérêt pour l’institution.**  
**Réponse** **honnête** **sur** **ce** **qui** **est** **automatisé** **vs** **relation** **commerciale**.

---

#### Pistes de solution (D5 — à ne pas oublier)

- **Modèle** **de** **facture** **ou** **reçu** **PDF** **post-paiement** **(évolution)**.  
- **Contact** **B2B** **sur** **page** **«** **Institutions** **»** **(site** **public)**.

**Formulation courte (mémoire / oral)**  
*« Le paiement individuel par mobile est la base ; le partenariat institutionnel demande traçabilité et souvent un traitement contractuel en dehors du self-service. »*

**Priorisation**  
- **V1** : **réponse** **type** **mail** **pour** **sponsors**.  
- **Suite** : **features** **B2B** **selon** **traction**.

---

### D6. Expiration, essai et communication

**Situation actuelle.**  
**Abonnements** **avec** **`date_fin`** ; **infos** **avec** **`jours_restants`** ; **statuts** **expirés** **gérés**. **Essai** **distingué** **dans** **`infos`**.

**Pourquoi c’est gênant.**  
**Passage** **à** **l’expiration** **sans** **mail** **ni** **bannière** → **utilisateur** **ne** **comprend** **pas** **pourquoi** **le** **dashboard** **est** **inaccessible**.

**Objectif après amélioration.**  
**Rappel** **J-7** / **J-1** (**email** **ou** **notification** **in-app**) ; **écran** **dédié** **«** **Renouveler** **»**.

**Exemple.**  
**Fin** **d’essai** **jour** **J** : **première** **connexion** **→** **modal** **«** **Votre** **essai** **est** **terminé** **»** **avec** **CTA** **paiement**.

**Intérêt pour le PO.**  
**Conversion** **et** **moins** **d’**abandon** **silencieux**.

---

#### Pistes de solution (D6 — à ne pas oublier)

- **Notifications** **Laravel** **ou** **SMS** **(coût)**.  
- **Bannière** **persistante** **si** **`jours_restants`** **≤** **7**.

**Formulation courte (mémoire / oral)**  
*« L’expiration doit être anticipée dans l’interface, pas découverte au moment du refus d’accès. »*

**Priorisation**  
- **V1** : **bannière** **compte** **connecté**.  
- **Suite** : **e-mails** **transactionnels**.

---

## E. Récapitulatif en une lecture (sans tableau)

**Prix** : **alignement** **marketing** **/ **`AbonnementService`** (voir **pistes D1**).

**Parcours** : **confirmation** **visible** **après** **paiement** (voir **pistes D2**).

**Mock** : **démo** **reproductible** (voir **pistes D3**).

**Droits** : **PDF**, **exploits**, **historique** **expliqués** (voir **pistes D4**).

**Institution** : **B2B** **hors** **self-service** **souvent** (voir **pistes D5**).

**Expiration** : **rappels** **avant** **blocage** (voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0** — **cohérence** **prix** **affichés** / **moteur** ; **callbacks** **HTTPS** **corrects** (**domaine** **5**) ; **comportement** **clair** **quand** **paiement** **OK**.

**Priorité 1** — **UI** **des** **droits** (**masquage** **+** **texte**) ; **bannière** **fin** **d’abonnement** ; **doc** **mock** **démo**.

**Priorité 2** — **e-mails** **transactionnels** ; **offre** **institutionnelle** **structurée** ; **codes** **promo**.

---

## G. Ce que cette synthèse ne remplace pas

Elle ne remplace pas un **contrat** **FedaPay** **signé**, ni une **analyse** **juridique** **des** **conditions** **générales** **de** **vente**, ni un **audit** **comptable**. Les **montants** **et** **durées** **exactes** **à** **la** **date** **de** **soutenance** **doivent** **être** **vérifiés** **dans** **le** **code** **et** **les** **écrans** **réels**.

---

## H. Texte court pour l’oral (environ trente secondes)

*« AgroFinance+ distingue plusieurs plans — gratuit, essentielle, pro, coopérative — avec des droits différents : export PDF, nombre d’exploitations, historique et dossier crédit pour les plans supérieurs. Le paiement passe par FedaPay avec des montants en FCFA définis dans le service d’abonnement ; en développement, un mode mock évite les appels réels. Les routes métier exigent un abonnement actif ; il faut que l’utilisateur comprenne ce qu’il achète et qu’il voie une confirmation après paiement. Les institutions peuvent demander facturation groupée ou sponsoring, souvent traités hors produit aujourd’hui. Les priorités sont l’alignement prix-écran-moteur, la clarté après redirection de paiement, et les rappels avant expiration. »*

---

## I. Les trois acteurs — rappel synthétique

| Acteur | Question centrale |
|--------|-------------------|
| **PO / produit** | L’**offre** et les **prix** **reflètent-ils** **exactement** **les** **règles** **du** **code** ? |
| **Exploitant payeur** | **Combien**, **pour** **quoi**, et **accès** **immédiat** **après** **paiement** ? |
| **Institution** | **Facture**, **sponsoring**, **traçabilité** : **quelle** **réponse** **produit** **vs** **relation** **commerciale** ? |

---

*Document à faire évoluer avec les tarifs définitifs, les captures d’écran du parcours d’abonnement et les conditions FedaPay en production.*
