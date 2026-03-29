# Synthèse pour soutenance — Produit et parcours utilisateur

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** comment l’**exploitant** (surtout sur le terrain) **navigue** dans l’application : entrée dans le service, **double interface** mobile / desktop, **tableau de bord**, **saisie**, **rapports**, **aide** et **gestion des erreurs**. Des **exemples concrets**, des **pistes de solution** et des **priorités** complètent chaque thème.  
**Liens avec les autres volets :** le **domaine 1** (`SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`) précise **ce que signifient** les chiffres ; le **domaine 2** (`SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`) traite **protection** et **données** ; le **domaine 4** (`SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`) **lisibilité**, **tactile** et **accessibilité** ; le **domaine 5** (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`) **stack** et **déploiement** ; le **domaine 6** (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`) **abonnement** et **paiement** ; le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`) **vision** et **segments** utilisateurs. Ici, l’angle est : **par où passe-t-on** et **que voit-on** à l’écran ?

---

## A. Lexique — comprendre les termes utilisés

**Parcours utilisateur**  
Enchaînement d’**écrans** et d’**actions** pour atteindre un objectif (ex. : **saisir une dépense**, **voir le tableau de bord**).

**Onboarding**  
Première utilisation : **inscription**, **vérification du téléphone**, **création du PIN**, puis souvent **création d’une exploitation** et d’**activités** (campagnes).

**Middleware `subscribed`**  
Sur le **web**, une partie des routes (tableau de bord, exploitations, transactions, rapports) n’est accessible que si l’utilisateur a un **abonnement actif** (ou essai valide). Sans cela, le parcours **métier** est **bloqué** après connexion.

**Détection de plateforme (`DetectPlatform`)**  
Middleware qui choisit le **layout** : **`layouts.app-mobile`** ou **`layouts.app-desktop`** selon l’appareil (ou le paramètre de test `?platform=`), pour **adapter** la présentation sans dupliquer toute la logique métier.

**PWA (Progressive Web App)**  
Application web **installable** sur le téléphone ; **manifeste** et **service worker** pour le mode **hors ligne** partiel (page `/offline`, stratégies de cache).

**Saisie hors ligne (mobile)**  
File locale (**IndexedDB**) pour enregistrer des transactions **sans réseau**, puis **synchronisation** vers l’API (`POST /api/transactions` avec `client_uuid` pour l’idempotence).

**Centre d’aide**  
Pages publiques sous le préfixe **`/aide`** : articles par **catégorie**, recherche.

**Partage de rapport**  
Lien **public** (sans connexion) du type **`/partage/{token}`** pour consulter un rapport généré.

**Exploitant terrain**  
Utilisateur principal du produit : souvent sur **smartphone**, connexion **irrégulière**, besoin de **formulations simples** et de **peu d’étapes**.

---

## B. Rôle de ce document pour ta soutenance

Tu montres que le projet n’est pas seulement un **moteur de calcul** ou une **stack sécurisée**, mais un **produit** pensé pour des **contextes réels** (mobile, interruption réseau, premiers pas). Tu peux articuler oralement : **métier** (domaine 1) → **sécurité** (domaine 2) → **parcours** (ce document).

---

## C. Ce que le programme propose aujourd’hui (résumé en français)

**Web authentifié** : après **connexion** (`/connexion`), l’utilisateur avec **abonnement actif** accède au **tableau de bord** (`/dashboard`), aux **exploitations**, **activités** (campagnes), **transactions** (liste, création, modification), **rapports** (génération, téléchargement). **Profil** et **abonnement** restent accessibles même sans module complet selon les règles de middleware.  
**Première visite** : si **aucune exploitation**, le tableau de bord **redirige** vers la création d’exploitation avec un **message informatif**.  
**Présentation** : **mobile** (dock, cartes, thème glass) vs **desktop** (sidebar, fond carrousel) via **`DetectPlatform`**.  
**API** : la même donnée peut alimenter la **PWA** ; le **dashboard** web peut exposer un **jeton de session API** pour les appels JavaScript.  
**Hors ligne** : scripts **`offline-transactions.js`** (importés depuis `app.js`) pour file d’attente et bannière d’état ; **service worker** en **network only** sur `/api/*`.  
**Public** : accueil, contact, **aide**, page **`/offline`**, **partage** de rapport par token.

Ce résumé est **volontairement fonctionnel** ; le détail écran par écran peut évoluer avec les sprints.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

Chaque partie suit le même schéma : **ce qui se passe aujourd’hui**, **ce qu’on voudrait**, **un exemple concret**, puis **pistes de solution** détaillées.

---

### D1. Parcours d’entrée (inscription, OTP, PIN, abonnement)

**Situation actuelle.**  
Le parcours type est : **`/inscription`** → **`/verification-otp`** → **`/creer-pin`** → **`/connexion`**. Les routes **métier** sous **`subscribed`** exigent un **abonnement** : un utilisateur **connecté** mais **sans** formule active peut se retrouver **bloqué** avant le tableau de bord ou la saisie.

**Pourquoi c’est gênant.**  
Un exploitant peut croire que « l’appli ne marche pas » alors qu’il manque une **étape** (abonnement, essai expiré) ou une **exploitation** créée. Trop d’**écrans** sans **rappel du but** fatiguent sur le terrain.

**Objectif après amélioration.**  
**Fil d’Ariane** ou **titres** explicites ; message **clair** quand l’accès métier est bloqué par l’**abonnement** ; **guidage** jusqu’à la **première campagne** et la **première transaction**.

**Exemple.**  
Awa termine le PIN, se connecte, clique sur « Tableau de bord » : **redirection** vers **paiement** ou message d’**abonnement** — sans phrase du type *« Pour voir vos chiffres, choisissez une formule ou un essai »*, elle abandonne.

**Intérêt pour un agent de microfinance / institution.**  
Si l’institution **sponsorise** l’abonnement, le parcours doit **expliquer** le rôle du **plan** (historique, PDF, etc.) sans jargon technique.

---

#### Pistes de solution (D1 — à ne pas oublier)

**Produit**  
- **Écran intermédiaire** après connexion : *« Prochaine étape : activer votre accès »* avec **lien abonnement** et rappel des **droits** (gratuit / essai / payant).  
- **Checklist** « Premiers pas » : exploitation créée → au moins une **campagne** → première **transaction**.  
- **Paramètre** `?platform=mobile` pour **tests** de parcours (déjà possible côté middleware) documenté pour l’équipe.

**Contenu**  
- **Centre d’aide** : article *« Après l’inscription »* avec les **étapes** dans l’ordre.

**Formulation courte (mémoire / oral)**  
*« L’entrée dans le produit aligne inscription technique et accès métier : l’utilisateur doit toujours savoir quelle étape manque et pourquoi. »*

**Priorisation**  
- **V1** : messages **bloquants** explicites (abonnement, exploitation manquante).  
- **Suite** : onboarding **guidé** (tutoriel léger), personnalisation par **partenaire**.

---

### D2. Mobile versus desktop (même logique, deux ergonomies)

**Situation actuelle.**  
Le middleware **`DetectPlatform`** bascule entre **`app-mobile`** et **`app-desktop`**. Le **design mobile** vise cartes, **bottom dock**, **glass** sombre ; le **desktop** : **sidebar**, carrousel de fond. Les **routes** sont les **mêmes** ; le **contenu** des vues peut diverger avec **`@if($platform === 'mobile')`**.

**Pourquoi c’est gênant.**  
Si une **fonction** existe seulement sur un **layout** (oubli de branche mobile), l’exploitant **ne la voit pas**. Si les **libellés** diffèrent trop entre les deux, la **formation** et le **support** se compliquent.

**Objectif après amélioration.**  
**Parité fonctionnelle** sur les **actions critiques** (saisie, dashboard, liste transactions) ; **même vocabulaire** ; **tests** systématiques des deux **plateformes** avant release.

**Exemple.**  
Sur desktop, un **raccourci** vers les **rapports** est visible dans la **sidebar** ; sur mobile, l’onglet du **dock** ne pointe pas vers la même **priorité** : l’utilisateur **mobile** croit que les PDF **n’existent pas**.

**Intérêt pour la microfinance / institution.**  
Démonstration **terrain** sur **téléphone** ; le **conseiller** peut suivre le **même** vocabulaire sur **ordinateur**.

---

#### Pistes de solution (D2 — à ne pas oublier)

**Qualité**  
- **Grille de test** : chaque **user story** validée en **mobile** et **desktop**.  
- **Inventaire** des vues avec **dual-render** pour éviter les **trous**.

**Design**  
- **Tokens CSS** communs (`--af-*`) déjà dans le projet : **réutiliser** pour ne pas **casser** la cohérence entre layouts.

**Formulation courte (mémoire / oral)**  
*« Une seule application, deux ergonomies : la fonction métier doit être accessible des deux côtés avec le même langage. »*

**Priorisation**  
- **V1** : audit des **écrans** souscrits (dashboard, transactions, rapports).  
- **Suite** : raffinements **accessibilité** (voir domaine UX dédié si tu en fais un cinquième volet).

---

### D3. Tableau de bord et lecture des synthèses

**Situation actuelle.**  
Le **dashboard** charge une **exploitation** (sélection par `exploitation_id` ou **première** disponible), calcule les **indicateurs** consolidés et **par activité**, affiche **cartes de campagnes**, **graphique**, **alertes budget**, **dernières transactions**. Le **domaine 1** a déjà listé les **risques** : **période** peu visible, **total** qui **masque** une campagne, **statut** consolidé vs **seuil** par campagne.

**Pourquoi c’est gênant.**  
Sans **repères** visibles (dates, nombre de campagnes, avertissement **peu de données**), l’exploitant **interprète** mal l’écran — même si les **chiffres** sont justes.

**Objectif après amélioration.**  
Appliquer les **pistes du domaine 1** (bandeau **période**, détail **avant** total, **pistes D5** statut/seuil, **pistes D6** données insuffisantes) **directement** sur le **dashboard** web et mobile.

**Exemple.**  
Le **bandeau** indique *« Période : du … au … »* et *« 2 campagnes en cours »* ; une **carte** orange signale une campagne **négative** même si le **total** exploitation est vert.

**Intérêt pour la microfinance / institution.**  
**Capture d’écran** ou **PDF** **alignés** sur ce que le **conseiller** peut **expliquer** oralement.

---

#### Pistes de solution (D3 — à ne pas oublier)

**Interface**  
- **Réutiliser** les formulations déjà rédigées dans **`SYNTHESE-SOUTENANCE-METIER-INDICATEURS.md`** (sections **D1**, **D4**, **D5**, **D6**).  
- **Cartes campagnes** : **RNE** ou **marge** **par ligne**, **couleur** par campagne, **lien** « Voir le détail ».

**Technique**  
- Les **données** sont déjà dans **`DashboardController`** / **`DashboardService`** : l’enjeu est surtout **affichage** et **textes**.

**Formulation courte (mémoire / oral)**  
*« Le tableau de bord est le contrat de confiance avec l’utilisateur : période, périmètre et prudence doivent être lisibles sans ouvrir l’aide. »*

**Priorisation**  
- **P0** avec le domaine 1 : **période** + **avertissement données faibles**.  
- **P1** : renforcement **multi-campagnes** et **notes D5** sur le consolidé.

---

### D4. Saisie des transactions (web, catégories, hors ligne)

**Situation actuelle.**  
Routes web **`/transactions/nouvelle`**, **`/transactions`**, édition. Le **domaine 1** prévoit d’**unifier** référentiel et **saisie libre** avec **clarification** pour les **nouveaux libellés**. Côté **client**, le projet inclut une **couche hors ligne** (**IndexedDB**, synchro API, **`client_uuid`**).

**Pourquoi c’est gênant.**  
Saisie **longue** ou **liste** de catégories **illisible** sur petit écran → **erreurs** ou **abandon**. Hors ligne : si la **bannière** de synchro est **peu visible**, l’utilisateur croit que **tout est enregistré** sur le serveur alors que des lignes sont **en attente**.

**Objectif après amélioration.**  
**Formulaire** **court**, catégories **groupées** par rubrique sur mobile (cf. règles projet) ; **état de synchro** **explicite** (en attente / envoyé / erreur) ; messages **422** (campagne **clôturée**, validation) **traduits** en français clair.

**Exemple.**  
Hassan saisit **trois dépenses** sans réseau : l’écran affiche *« 3 opérations en attente d’envoi »* et un bouton **Synchroniser** ; après retour **4G**, il voit *« Tout est à jour »* ou *« 1 échec : … »*.

**Intérêt pour la microfinance / institution.**  
Moins de **données manquantes** dans le dossier ; **traçabilité** de ce qui est **local** vs **serveur**.

---

#### Pistes de solution (D4 — à ne pas oublier)

**UX saisie**  
- **Wizard** ou **étapes** (type → montant → catégorie → date) sur **mobile**.  
- **Harmonisation** des **libellés** de catégories (casse, groupes).  
- Intégrer la **décision domaine 1** (question **Oui/Non** production campagne) quand le **référentiel** ne matche pas.

**Hors ligne**  
- **Bannière** persistante tant qu’il reste des **pending** ; **retry** manuel et automatique ; message si **401** (token : se **reconnecter**).

**Formulation courte (mémoire / oral)**  
*« La saisie est le cœur terrain : elle doit être rapide, compréhensible, et honnête sur l’état de synchronisation. »*

**Priorisation**  
- **V1** : messages **synchro** + **erreurs API** clairs.  
- **Suite** : autocomplete **référentiel** (API interne évoquée domaine 1).

---

### D5. Rapports PDF, partage et déconnexion

**Situation actuelle.**  
**Rapports** : liste, **génération**, **téléchargement** ; **partage** public par **`/partage/{token}`**. **Déconnexion** : route **`POST /deconnexion`**. Les **PDF** stockés sous **`storage/app/rapports/`** (hors `public` direct).

**Pourquoi c’est gênant.**  
Un **PDF** sans **période** ni **avertissements** (domaine 1) **déçoit** le lecteur institutionnel. Un **lien de partage** **mal expliqué** peut inquiéter (« qui peut voir ? »). **Déconnexion** peu visible sur **mobile** → **compte** laissé ouvert.

**Objectif après amélioration.**  
**En-tête PDF** aligné sur le **dashboard** (dates, périmètre, nombre de campagnes, mention **données partielles** si pertinent). **Explication** du **partage** : *« Lien lisible par toute personne qui le reçoit »*. **Déconnexion** accessible depuis le **profil** / **menu** mobile.

**Exemple.**  
Le PDF commence par *« Exploitation X — du 01/01/2025 au 31/03/2026 — 2 campagnes en cours »* et un encadré *« Indicateurs indicatifs : moins de 5 transactions sur la période »* si seuil dépassé.

**Intérêt pour la microfinance / institution.**  
**Pièce** joignable au **dossier** sans **contre-interrogation** sur la **période**.

---

#### Pistes de solution (D5 — à ne pas oublier)

**PDF**  
- **Réutiliser** les **pistes D1** et **D6** du document **métier** pour le **contenu** des rapports.  
- **Mention** du **partage** : date de **génération**, **validité** optionnelle du lien (évolution produit).

**Navigation**  
- **Menu** mobile : **Profil**, **Déconnexion**, **Aide** regroupés.

**Formulation courte (mémoire / oral)**  
*« Les exports prolongent le tableau de bord : mêmes repères, même prudence, et transparence sur le partage. »*

**Priorisation**  
- **V1** : **période** + **texte** partage dans l’UI et en tête de PDF.  
- **Suite** : **expiration** des tokens de partage, **watermark** institutionnel.

---

### D6. Aide en ligne, pages publiques et mode hors ligne

**Situation actuelle.**  
**Centre d’aide** sous **`/aide`** avec **catégories** et **articles** (contenu seedable). Pages **marketing** : accueil, contact, à propos. Route **`/offline`** pour le **fallback** PWA. **Service worker** : pas de cache **API**.

**Pourquoi c’est gênant.**  
Si les **articles** ne **reflètent** pas les **écrans** réels (ancienne UI, vocabulaire FSA alors que le produit dit « indicateurs financiers agricoles »), l’**exploitant** **perd confiance**. La page **offline** doit **rassurer** (*« vos saisies en attente »*) et **orienter**.

**Objectif après amélioration.**  
**Aligner** aide et **parcours** (même **noms** de menus) ; **rappeler** la synchro hors ligne ; **SEO** et **lisibilité** simples pour le **terrain**.

**Exemple.**  
Article *« Saisir une dépense »* avec **captures** ou **schémas** **mobile** ; lien vers la **route** exacte **`/transactions/nouvelle`**.

**Intérêt pour la microfinance / institution.**  
**URL d’aide** **partageable** lors d’**ateliers** ; image **soignée** du **dispositif**.

---

#### Pistes de solution (D6 — à ne pas oublier)

**Contenu**  
- **Revue** des articles après chaque **grosse** refonte UI.  
- **Commandes** de seed documentées (`help:seed-*`) pour **reproducibilité**.

**PWA**  
- **Tester** `/offline` après **déploiement** (chemins **`asset()`** sous sous-dossier XAMPP / prod).

**Formulation courte (mémoire / oral)**  
*« L’aide et le mode dégradé sont partie intégrante du parcours : ils doivent dire la même chose que l’application. »*

**Priorisation**  
- **V1** : 3 à 5 **articles** « **premiers pas** » à jour.  
- **Suite** : **vidéos** courtes ou **fiches PDF** pédagogiques.

---

## E. Récapitulatif en une lecture (sans tableau)

**Entrée** : inscription → OTP → PIN → **abonnement** + **exploitation** — chaque **blocage** doit **s’expliquer** (voir **pistes D1**).

**Mobile / desktop** : **parité** et **même langage** (voir **pistes D2**).

**Dashboard** : **transparence** **métier** (période, campagnes, prudence) — **caler** sur le **domaine 1** (voir **pistes D3**).

**Saisie** : **rapide**, **synchro visible**, **erreurs** claires (voir **pistes D4**).

**Rapports & partage** : **PDF** et **liens** **honnêtes** ; **déconnexion** **trouvable** (voir **pistes D5**).

**Aide & offline** : **cohérence** avec le produit (voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0 (à faire en premier)** — ce sans quoi le terrain **décroche** :  
messages quand **abonnement** ou **exploitation** **manque** ; **période** et **prudence** sur le **dashboard** et les **PDF** (recoupement **domaine 1**) ; **état** de la **synchro** hors ligne **visible**.

**Priorité 1 (ensuite)** — ce qui améliore la **qualité d’usage** :  
**parité** mobile / desktop sur les **parcours critiques** ; **erreurs API** **compréhensibles** ; **en-têtes** PDF et **texte** sur le **partage** ; **aide** « premiers pas » **à jour**.

**Priorité 2 (amélioration continue)** :  
onboarding **guidé**, **expiration** des liens de partage, **articles** avancés, **accessibilité** (focus, contrastes, lecteurs d’écran).

---

## G. Ce que cette synthèse ne remplace pas

Elle ne remplace pas une **étude UX** complète (tests utilisateurs, eye-tracking), ni un **inventaire** **exhaustif** de **chaque** composant Blade. Elle se **connecte** aux documents **métier** et **sécurité** sans les **dupliquer**. Le détail **lisibilité / accessibilité** et **démo institutionnelle** est dans `SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`. Les **tests automatisés** (E2E) peuvent faire l’objet d’un **complément** ultérieur.

**Implémentation livrée (sprint S5) :** le compte rendu **`docs/SPRINT-S5-PRODUIT-PARCOURS.md`** décrit les mesures présentes dans le dépôt (messages d’abonnement distincts, bannière de synchro hors ligne mobile/desktop, textes partage rapports et page offline, codes API **403**).

---

## H. Texte court pour l’oral (environ trente secondes)

*« AgroFinance+ adapte le parcours au terrain : inscription avec OTP et PIN, puis accès conditionné par l’abonnement et la création d’exploitation. L’interface change entre mobile et bureau tout en ciblant les mêmes fonctions — tableau de bord, saisie des transactions, rapports PDF et partage. La saisie peut fonctionner hors ligne avec synchronisation ; l’utilisateur doit voir clairement ce qui est en attente et ce qui est sur le serveur. Les priorités produit sont d’abord la clarté des blocages et des périodes affichées, puis la parité mobile-desktop et l’alignement de l’aide avec les écrans réels, en cohérence avec nos synthèses métier et sécurité. »*

---

*Document à faire évoluer avec les maquettes finales, les retours utilisateurs pilotes et l’audit des vues Blade.*
