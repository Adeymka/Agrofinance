# Synthèse pour soutenance — UX, UI et accessibilité mobile

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** comment l’interface **sert** l’**exploitant terrain** (lisibilité, gestes, feedback), comment le **PO / design** **arbitre** la cohérence visuelle, et ce qu’une **institution** peut **attendre** en démo ou en partenariat. Des **exemples concrets**, des **pistes de solution** et des **priorités** complètent chaque thème.  
**Liens avec les autres volets :** le **domaine 3** (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`) décrit **les parcours** et **les écrans** ; le **domaine 1** définit **la signification** des chiffres et des couleurs métier (statuts) ; le **domaine 5** (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`) l’**infra** et les **performances** côté serveur ; le **domaine 6** (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`) le **parcours** **paiement** et les **droits** liés au **plan** ; le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`) la **cohérence** **du** **message** **public** **avec** **l’**expérience** **réelle**. Ici, l’angle est : **l’interface est-elle perceptible, utilisable et compréhensible** — surtout sur **mobile** ?

---

## A. Lexique — comprendre les termes utilisés

**UX (expérience utilisateur)**  
Qualité globale du **ressenti** et de l’**efficacité** pour accomplir une tâche (saisir une dépense, lire un indicateur), au-delà du seul « beau graphisme ».

**UI (interface utilisateur)**  
**Apparence** et **comportement** des éléments à l’écran : boutons, cartes, couleurs, typographie.

**Accessibilité (a11y)**  
Désigné souvent par **a11y** : capacité pour des personnes **diverses** (vision, motricité, cognition) d’**utiliser** le service. Les référentiels courants incluent les **WCAG** (Web Content Accessibility Guidelines) ; les exigences **nationales** (ex. RGAA en France) peuvent s’appliquer selon les **marchés** ou **financeurs**.

**Contraste**  
Écart de luminosité entre **texte** et **fond** ; un contraste **insuffisant** rend la lecture **difficile** (soleil, vieux écran, fatigue visuelle).

**Zone tactile (touch target)**  
Surface **cliquable** ; les guides recommandent souvent **au moins 44 × 44 px** pour le pouce (déjà présent en intention dans le projet via le token **`--af-touch-min`** dans `resources/css/app.css`).

**Design tokens**  
Variables CSS partagées (ex. **`--af-*`**) : couleurs, rayons, flous, textes — pour **garder une cohérence** entre écrans **sans** multiplier les valeurs en dur.

**Glassmorphisme**  
Effet **verre** (flou, transparence) sur fond sombre — **identité** forte du mobile AgroFinance+ ; risque si le **contraste** du **texte** sur ces surfaces est **trop faible**.

**Hiérarchie visuelle**  
Ce que l’œil voit **en premier** (titres, montants, alertes) vs **secondaire** (légendes, méta).

**Progressive disclosure**  
Révéler l’**information par étapes** (résumé puis détail) pour **limiter la charge cognitive** sur petit écran.

**Feedback utilisateur**  
Confirmation qu’une **action** a été reçue : chargement, succès, erreur — **sans** seulement la **couleur**.

**Daltonisme**  
Difficulté à **distinguer** certaines couleurs : un **statut vert / orange / rouge** ne doit **pas** être la **seule** information (voir aussi **domaine 1** sur les statuts).

**Exploitant terrain**  
Utilisateur principal **mobile**, souvent **debout**, **une main**, **luminosité variable**.

**PO / design produit**  
Arbitre des **priorités** d’interface, de la **grille** et de la **cohérence** des composants.

**Institution (partenaire)**  
Juge souvent le produit via une **démo** ou des **captures** ; peut exiger une **démarche** d’inclusion ou de **conformité** selon les appels à projets.

---

## B. Rôle de ce document pour ta soutenance

Tu montres que **l’interface** n’est pas un **habillage** mais un **facteur de réussite** pour l’adoption (terrain, inclusion, crédibilité partenaire). Tu peux **relier** trois **lunettes** : **exploitant** (besoins réels), **PO / design** (cadre et livrables), **institution** (image et exigences).

---

## C. Ce que le programme propose aujourd’hui (résumé en français)

**Identité visuelle** : thème **sombre** avec **glass** ; **Space Grotesk** pour titres et grands chiffres, **Inter** pour navigation et formulaires (`resources/css/app.css`). **Tokens** `--af-*` (couleurs texte, bordures, statuts vert/orange/rouge, **minimum tactile** `--af-touch-min: 44px`).  
**Layouts** : **`app-mobile`** (header glass, **bottom dock**) vs **`app-desktop`** (sidebar, carrousel de fond) — **`DetectPlatform`**.  
**Métier** : les **statuts** couleur sont **doublés** en principe par des **indicateurs** (badges, bordures) dans le système de tokens — à **vérifier** écran par écran pour les **messages d’erreur** seuls.  
**PWA** : installation possible ; **offline** partiel.

Ce résumé ne remplace pas un **audit WCAG** ni un **test utilisateurs** sur le terrain.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

Chaque partie suit le même schéma : **ce qui se passe aujourd’hui**, **ce qu’on voudrait**, **un exemple concret**, puis **pistes de solution** détaillées.

---

### D1. Lisibilité, contraste et thème glass (terrain, soleil)

**Situation actuelle.**  
L’UI mobile repose sur un **fond sombre**, des **surfaces vitrées** et des **textes** en blanc **à opacités variables** (`--af-text-primary`, `--af-text-secondary`, `--af-text-muted`, etc.). C’est **cohérent** avec l’identité produit, mais certains niveaux **très faibles** (légendes, méta) peuvent devenir **pénibles** en **plein soleil** ou sur écran **bas de gamme**.

**Pourquoi c’est gênant.**  
Un exploitant qui **ne lit pas** un montant ou une **alerte** au premier coup d’œil **décroche** ou **se trompe** — indépendamment de la **justesse** du calcul métier.

**Objectif après amélioration.**  
**Garantir** un **contraste** suffisant sur les **textes critiques** (montants, libellés d’action, erreurs) ; **accepter** des niveaux plus **légers** uniquement pour le **secondaire** ; **tester** en conditions réelles (extérieur, luminosité).

**Exemple.**  
Sous le soleil à **11 h**, la légende *« Mis à jour il y a 2 j »* en **texte très atténué** devient **illisible** ; le **total** en **grande taille** reste lisible — l’utilisateur **ne sait pas** si les données sont **fraîches**.

**Intérêt pour une institution.**  
Une **capture** ou une **vidéo** de démo **illISIBLE** fait **douter** du **professionnalisme** du dispositif.

---

#### Pistes de solution (D1 — à ne pas oublier)

**Design**  
- **Règle interne** : **corps principal** et **montants** au-dessus d’un **seuil** de contraste (outil **Figma** ou **WebAIM**).  
- **Variante** « **haute lisibilité** » (option utilisateur ou réglage système `prefers-contrast`) : **renforcer** les opacités ou **réduire** le flou sur les cartes critiques.  
- **Éviter** le **gris très clair** sur **verre** pour le **seul** canal d’information importante.

**Tests**  
- **Session** en **extérieur** avec **2–3** téléphones représentatifs.  
- **Screenshot** avant/après pour le **mémoire**.

**Formulation courte (mémoire / oral)**  
*« Le glassmorphisme sert l’identité ; la lisibilité des chiffres et des actions prime sur l’effet esthétique sur les écrans critiques. »*

**Priorisation**  
- **V1** : audit des **titres**, **KPI**, **boutons primaires**, **erreurs**.  
- **Suite** : mode **contraste élevé** global.

---

### D2. Zones tactiles, dock et « une main »

**Situation actuelle.**  
Le projet fixe **`--af-touch-min: 44px`** dans les tokens — **bon alignement** avec les **recommandations** courantes. Le **dock** mobile regroupe les **accès** principaux en bas d’écran (zone **naturelle** du pouce).

**Pourquoi c’est gênant.**  
Si un **composant** ou une **vue** **contourne** les tokens (lien **texte** trop petit, **icône** seule sans zone étendue), la **saisie** devient **frustrante** en **routier**.

**Objectif après amélioration.**  
**Appliquer** systématiquement la **taille minimale** aux **cibles** réelles (y compris **hit area** plus large que l’icône visible). **Vérifier** les formulaires longs : **pas** de double **tap** accidentel sur « Valider ».

**Exemple.**  
Une **croix** de fermeture de **16 px** dans un coin : **difficile** à toucher ; l’utilisateur **tape** trois fois et **ferme** une autre fenêtre.

**Intérêt pour une institution.**  
Moins d’**appels support** « ça ne marche pas » — souvent un **problème de cible**, pas de **serveur**.

---

#### Pistes de solution (D2 — à ne pas oublier)

**Checklist de revue**  
- **Minimum** 44 × 44 px **ou** zone **invisible** étendue (`padding` + `min-height/width`).  
- **Espacement** entre deux actions **destructives** et **primaires** (éviter les erreurs).

**Documentation**  
- **Page interne** ou **Storybook** : **tailles** des boutons **primary** / **secondary** / **ghost**.

**Formulation courte (mémoire / oral)**  
*« Le terrain impose des gestes fiables : le dock et les tokens tactiles ne sont utiles que s’ils sont appliqués partout sans exception. »*

**Priorisation**  
- **V1** : passe sur **saisie transaction**, **connexion**, **dock**.  
- **Suite** : audit **toutes** les modales / **drawers**.

---

### D3. Formulaires, erreurs et couleur seule

**Situation actuelle.**  
Les **statuts métier** (vert / orange / rouge) sont **pensés** avec **fonds** et **bordures** dans les tokens — **meilleure** base que la **seule** couleur de texte. Les **messages de validation** Laravel / API peuvent encore **arriver** comme **texte brut** — à **uniformiser** côté UI.

**Pourquoi c’est gênant.**  
Si une **erreur** est **uniquement** en **rouge** sans **icône** ni **texte explicite**, un utilisateur **daltonien** ou stressé peut **la manquer**. Même problème pour le **code couleur** du **dashboard** (voir **domaine 1** et **D5** statut).

**Objectif après amélioration.**  
**Toujours** associer **couleur** + **texte** + **icône** (ou **mot-clé**) pour les **erreurs** et les **alertes** ; **résumé** des **champs en échec** en haut de formulaire long (**pattern** « résumé d’erreurs »).

**Exemple.**  
Le champ **montant** est refusé : bordure **rouge** sans message → l’utilisateur **croît** à un **bug** ; avec *« Montant requis »* **et** icône, il **corrige**.

**Intérêt pour une institution.**  
Alignement avec les **bonnes pratiques** d’**accessibilité** et **image** inclusive.

---

#### Pistes de solution (D3 — à ne pas oublier)

**Composants**  
- **Alert** réutilisable : **rôle** `alert` / `aria-live` pour les **retours** dynamiques.  
- **Liste** des erreurs **cliquables** qui **focus** le champ concerné.

**Formulation courte (mémoire / oral)**  
*« La couleur renforce le message ; elle ne le remplace pas. »*

**Priorisation**  
- **V1** : **création** et **édition** transaction, **connexion**.  
- **Suite** : **internationalisation** des messages techniques.

---

### D4. Charge cognitive et hiérarchie (dashboard, listes)

**Situation actuelle.**  
Le **tableau de bord** agrège **indicateurs**, **graphiques**, **cartes de campagnes**, **alertes**, **dernières transactions** — **riche** pour un **power user**, **dense** pour un **nouveau** utilisateur.

**Pourquoi c’est gênant.**  
Trop d’**éléments** **sans** ordre de **priorité** clair → **fatigue** et **mauvaise** interprétation (voir aussi **domaine 1** : période, multi-campagnes).

**Objectif après amélioration.**  
**Progressive disclosure** : **résumé** en **haut** (3–4 **KPI** + **période**) ; **détails** **dépliables** ; **rappel** du **nombre de campagnes** **consolidées**.

**Exemple.**  
**Premier** écran : **« Résultat net : +200 000 FCFA »** + **période** + **lien** « Voir le détail par campagne » — au lieu de **six** blocs **égaux** sans **titre** principal.

**Intérêt pour une institution.**  
**Démo** en **30 secondes** : le **message** principal est **clair**.

---

#### Pistes de solution (D4 — à ne pas oublier)

**UX**  
- **Niveaux** typographiques **stricts** (Space Grotesk **uniquement** pour **chiffres** et **titres** clés — déjà la direction du projet).  
- **Sections** avec **titres** visibles (pas seulement des **séparateurs** subtils).

**Formulation courte (mémoire / oral)**  
*« Un tableau de bord utile commence par une réponse simple à une question simple avant d’ouvrir le détail. »*

**Priorisation**  
- **V1** : **bloc** « **Vue d’ensemble** » **au-dessus** du scroll long.  
- **Suite** : **personnalisation** (masquer graphique).

---

### D5. Accessibilité technique (clavier, focus, lecteurs d’écran)

**Situation actuelle.**  
Application **Laravel Blade** + **Vite** ; beaucoup de **logique** **visuelle**. **ARIA** et **ordre de tabulation** **non** garantis partout sans **audit**.

**Pourquoi c’est gênant.**  
Utilisateurs **clavier** ou **lecteur d’écran** : **navigation** **difficile** si les **contrôles** **custom** ne sont **pas** **labelisés** ou si le **focus** **disparaît**.

**Objectif après amélioration.**  
**Cible raisonnable** pour une **V1 accessibilité** : **labels** associés aux **champs**, **boutons** **décrits**, **focus visible**, **titres** de page **cohérents** ; **roadmap** pour **WCAG** **AA** sur les **parcours critiques**.

**Exemple.**  
Un **slider** ou **segment** **custom** pour le **type** de transaction : sans **`role`** et **nom**, le **lecteur d’écran** annonce « bouton » **sans contexte**.

**Intérêt pour une institution.**  
Réponse **prête** aux **grilles** de marchés **publics** ou **programmes** avec critère **inclusion**.

---

#### Pistes de solution (D5 — à ne pas oublier)

**Technique**  
- **Audit** rapide avec **axe DevTools** ou **Lighthouse** sur **/connexion**, **/dashboard**, **/transactions/nouvelle**.  
- **Préférer** **éléments natifs** (`button`, `input`) quand possible ; sinon **ARIA** **minimale** documentée.

**Formulation courte (mémoire / oral)**  
*« L’accessibilité avancée est une trajectoire ; la base est sémantique HTML, labels et focus visibles sur les parcours critiques. »*

**Priorisation**  
- **V1** : **formulaires** auth + **saisie**.  
- **Suite** : **audit** complet **WCAG** **AA** + **correctifs** par sprint.

---

### D6. Démonstration institutionnelle et image « inclusive »

**Situation actuelle.**  
Les **partenaires** **jugent** souvent le produit par une **démo** **vidéo** ou **salle** — **projecteur**, **luminosité** différente du **téléphone** seul.

**Pourquoi c’est gênant.**  
Un **thème sombre** **peut** **moins bien** **se** **préter** à une **salle** **claire** ou un **vidéoprojecteur** **faible contraste** : l’interface **paraît** **fadée** alors qu’elle est **correcte** sur **OLED**.

**Objectif après amélioration.**  
**Prévoir** un **mode** **démo** ou **slides** **dédiées** avec **captures** **haute** **contraste** ; **répéter** la **démo** sur **vrai** **téléphone** en **backup**.

**Exemple.**  
Présentation **partenaire** : **vidéo** **full** **screen** du **simulateur** **iPhone** ; **plan** **B** : **passer** le **câble** **HDMI** **direct** **téléphone** **→** **écran**.

**Intérêt pour une institution.**  
**Maîtrise** du **support** de **pitch** = **maîtrise** de l’**image** **perçue**.

---

#### Pistes de solution (D6 — à ne pas oublier)

**Communication**  
- **Kit** **démo** : **3–5** **captures** **validées** **contraste** + **phrase** **accroche** **métier** (domaine 1).  
- **Script** **oral** : **30** **s** **produit** + **30** **s** **inclusion** **UI**.

**Formulation courte (mémoire / oral)**  
*« La démo institutionnelle impose de tester le rendu sur le support réel du rendez-vous, pas seulement sur le poste de développement. »*

**Priorisation**  
- **V1** : **checklist** **avant** **chaque** **présentation** **externe**.  
- **Suite** : **thème** **clair** **optionnel** **institutionnel**.

---

## E. Récapitulatif en une lecture (sans tableau)

**Lisibilité** : **contraste** sur textes **critiques** ; **glass** **maîtrisé** (voir **pistes D1**).

**Tactile** : **44 px** et **dock** **appliqués** **partout** (voir **pistes D2**).

**Erreurs** : **pas** **couleur** **seule** (voir **pistes D3**).

**Dashboard** : **hiérarchie** et **progressive disclosure** (voir **pistes D4**).

**A11y technique** : **labels**, **focus**, **audit** **parcours** **critiques** (voir **pistes D5**).

**Institution** : **démo** **sur** **bon** **support** (voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0 (à faire en premier)** — ce sans quoi le **terrain** **décroche** :  
**contraste** des **montants** et **actions** **primaires** ; **zones** **tactiles** **correctes** sur **saisie** et **connexion** ; **messages** d’**erreur** **complets**.

**Priorité 1 (ensuite)** — ce qui améliore la **qualité** **perçue** :  
**hiérarchie** **dashboard** ; **audit** **Lighthouse** / **axe** sur **3** **pages** ; **checklist** **démo** **institutionnelle**.

**Priorité 2 (amélioration continue)** :  
**mode** **contraste** **élevé** ; **WCAG** **AA** **étendu** ; **thème** **clair** ; **tests** **utilisateurs** **terrain** **documentés**.

---

## G. Ce que cette synthèse ne remplace pas

Elle ne remplace pas un **audit** **WCAG** **certifié**, ni des **tests** **utilisateurs** **quantitatifs**, ni une **charte** **design** **figée** dans **Figma**. Elle **complète** le **domaine 3** (parcours) en **centrant** **perception** et **inclusion** ; **les** **règles** **métier** sur les **couleurs** de **statut** restent dans le **domaine 1**.

---

## H. Texte court pour l’oral (environ trente secondes)

*« L’expérience mobile d’AgroFinance+ s’appuie sur un thème sombre glass, des tokens communs et une taille minimale de 44 px pour le tactile. L’enjeu terrain est la lisibilité au soleil et des gestes fiables d’une main ; l’enjeu accessibilité est de ne jamais se fier à la couleur seule pour les erreurs ou les statuts. Le design produit doit arbitrer beauté et contraste, et les institutions jugent souvent le projet sur une démo : il faut donc valider captures et support de projection. Les priorités sont le contraste des textes critiques, les zones tactiles cohérentes, puis un audit léger des parcours clés avant une montée en gamme WCAG. »*

---

## I. Les trois acteurs — rappel synthétique

| Acteur | Question centrale |
|--------|-------------------|
| **Exploitant terrain** | Puis-je **lire** et **agir** vite, **debout**, **au soleil**, **sans** me tromper de bouton ? |
| **PO / design** | Les **tokens** et **patterns** sont-ils **appliqués** partout avec une **hiérarchie** **claire** ? |
| **Institution** | La **démo** et les **captures** **reflètent-elles** un outil **sérieux** et **inclusif** ? |

---

*Document à faire évoluer avec les résultats d’audit Lighthouse/axe, les retours terrain et une éventuelle charte Figma.*
