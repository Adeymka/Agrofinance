# Synthèse pour soutenance — Métier et indicateurs financiers agricoles

**Projet :** AgroFinance+ — application pour suivre l’argent qui entre et sort d’une exploitation agricole (cultures, élevage, etc.), sur téléphone et ordinateur.

**Contenu de ce document :** ce que l’application calcule aujourd’hui, les mots techniques expliqués simplement, les problèmes possibles avec des exemples en francs CFA, ce qu’on pourrait améliorer, et l’ordre des priorités.  
**Pour aller plus loin :** voir le **domaine 2** (`SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md`), le **domaine 3** (`SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`), le **domaine 4** (`SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`), le **domaine 5** (`SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md`), le **domaine 6** (`SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md` — plans, FedaPay, droits) et le **domaine 7** (`SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md` — vision, marché, partenariats). **Tests** automatiques en complément.

---

## A. Lexique — comprendre les termes utilisés

**Exploitation**  
L’ensemble qu’on gère : par exemple « la ferme de Koffi » ou « mon activité maraîchère ». Elle peut contenir une ou plusieurs **campagnes**.

**Campagne (activité)**  
Une période de production suivie à part : par exemple « campagne maïs 2025 » ou « bande de poulets janvier ». Dans le logiciel, chaque campagne a ses propres recettes et dépenses.

**Transaction**  
Une ligne dans le carnet : une **recette** (argent qui rentre, vente de récolte, prime…) ou une **dépense** (achat d’engrais, salaire, essence…).

**Recette**  
Tout ce qui **augmente** l’argent disponible pour la campagne.

**Dépense**  
Tout ce qui **diminue** l’argent disponible.

**Nature « variable » ou « fixe » (pour une dépense)**  
- **Variable** : une dépense qui change beaucoup selon le volume produit (exemple : semences, engrais, aliment du bétail quand on produit plus).  
- **Fixe** : une dépense qui reste là même si on produit un peu plus ou un peu moins (exemple : assurance annuelle, loyer d’un hangar, abonnement).  
Cette distinction sert surtout à calculer la **marge brute** (voir plus bas). Si on se trompe entre fixe et variable, certains indicateurs bougent **sans** que l’argent réel ait changé.

**Référentiel de catégories**  
Liste proposée par l’application : « engrais minéraux », « semences », etc. Quand on choisit dedans, le logiciel sait **exactement** de quoi il s’agit.

**Saisie libre**  
Quand l’utilisateur écrit lui-même le libellé (exemple : « engrais chez le voisin »). Pratique sur le terrain, mais le programme ne reconnaît pas toujours ce texte comme une catégorie officielle.

**Produit brut (souvent noté PB dans le code)**  
Somme de **toutes les recettes** sur la période considérée. En clair : **tout ce que la campagne a encaissé** (ou enregistré comme vente).

**Coûts variables (CV)**  
Somme des dépenses classées en **variable**.

**Coûts fixes (CF)**  
Somme des dépenses classées en **fixe**.

**Coûts totaux (CT)**  
**CV + CF** : tout ce qui est sorti en dépenses, selon la saisie.

**Charges intermédiaires (CI)** — notion technique dans l’application  
Ici, ce n’est pas « toutes les dépenses », mais seulement celles qui correspondent à une **liste de types** définis dans le programme (semences, certains engrais, vaccins, carburant lié à la production, etc.).  
Si une dépense est saisie en **texte libre**, elle peut **ne pas** entrer dans ce total **CI**, même si elle est bien comptée dans les **coûts totaux**.

**Valeur ajoutée brute (VAB)**  
Dans l’application : **PB moins CI**. Idée simplifiée : ce qu’il reste du chiffre « ventes » une fois qu’on a retiré une partie des intrants typés. Si **CI** est incomplet (saisie libre), **VAB** peut être **trop optimiste**.

**Marge brute (MB)**  
**PB moins les coûts variables uniquement**. Ça répond à une question du type : « Est-ce que ce que je vends couvre déjà les dépenses qui bougent avec la production ? »

**Résultat « net d’exploitation » simplifié (RNE dans le code)**  
**PB moins tous les coûts (variables + fixes)**. C’est une façon de dire : « Après toutes les dépenses saisies, il reste combien ? » Un résultat **positif** = la campagne, sur le papier, **gagne** de l’argent ; **négatif** = elle **perd** sur la période.

**Ratio de rentabilité (RF)**  
Pourcentage : on compare le **RNE** aux **coûts totaux**. Sert à donner une idée d’« efficacité » relative ; à manipuler avec prudence si peu de lignes sont saisies.

**Seuil de rentabilité (SR)**  
Montant de **recettes** à partir duquel, avec la structure de coûts actuelle, on « équilibre » les charges fixes (image simplifiée : « il faudrait vendre au moins tant pour que les fixes soient couverts »). Dans le programme, il est calculé **par campagne** ; au niveau **total exploitation** (plusieurs campagnes mélangées), ce seuil **n’est pas recalculé** de la même façon — d’où un risque de confusion si on ne l’explique pas.

**Statut vert, orange, rouge**  
Code couleur pour résumer la situation : en général **vert** = situation favorable sur les critères du logiciel, **orange** = à surveiller, **rouge** = difficulté. La **règle exacte** dépend du **RNE**, de la **marge brute**, du **produit brut** et parfois du **seuil de rentabilité** au niveau campagne.

**Consolidation**  
Quand on **additionne** plusieurs campagnes **en cours** pour afficher un **total** pour toute l’exploitation.

**Plancher d’historique (lié à l’abonnement)**  
Selon la formule d’abonnement, l’application peut **ne pas remonter** au-delà d’une certaine date dans le passé. Les chiffres du tableau de bord ne portent donc pas forcément sur « toute la vie de la ferme », mais sur **la période autorisée**.

**Slug**  
Nom technique interne pour une catégorie (exemple : `engrais_mineraux`). L’utilisateur ne le voit pas ; c’est pour le programme.

**Microfinance (MF)**  
Institution ou agent qui prête ou accompagne avec des critères de remboursement ; ils lisent souvent des **synthèses** ou des **PDF** plutôt que de saisir eux-mêmes.

---

## B. Rôle de ce document pour ta soutenance

Tu peux t’en servir pour montrer que ton travail ne se limite pas à « faire une application », mais qu’il y a une **réflexion métier** : comment les chiffres sont construits, où un utilisateur peut se tromper, et comment rendre l’outil **plus clair** pour un agriculteur et pour un lecteur institutionnel.

---

## C. Ce que le programme calcule aujourd’hui (résumé en français)

Le calcul principal se fait dans le service `FinancialIndicatorsService`, **pour une campagne donnée**, sur une période (dates + règle d’abonnement).

D’abord, le programme additionne les **recettes** → cela donne le **produit brut (PB)**.  
Ensuite, il sépare les **dépenses** en **variables** et **fixes**, ce qui donne **CV**, **CF**, puis **CT = CV + CF**.  
Il calcule aussi **CI** en ne gardant que les dépenses dont la catégorie figure dans une **liste fixe** du code.  
À partir de là : **VAB = PB − CI**, **MB = PB − CV**, **RNE = PB − CT**.  
Un **statut couleur** est déduit à partir de ces nombres (et du seuil de rentabilité quand il existe au niveau campagne).

Pour une **exploitation** avec plusieurs campagnes **en cours**, les totaux affichés en consolidation sont la **somme** des indicateurs de chaque campagne.

---

## D. Thèmes de comparaison : situation actuelle, objectif, exemple

Chaque partie suit le même schéma : **ce qui se passe aujourd’hui**, **ce qu’on voudrait**, **un exemple concret**.

---

### D1. La période couverte par les chiffres

**Situation actuelle.**  
Sur le tableau de bord, l’utilisateur voit des totaux, mais **sans toujours une phrase très visible** du type : « Ces chiffres vont du 1er janvier 2025 au 28 mars 2026 » ou « Ils commencent à la date autorisée par votre abonnement ».

**Pourquoi c’est gênant.**  
Un agriculteur peut croire voir **uniquement** « ma campagne maïs », alors que le système peut inclure **plusieurs mois** ou **plusieurs campagnes actives** selon les réglages. Il compare alors avec son carnet et trouve que « l’appli ne correspond pas ».

**Objectif après amélioration.**  
Sur chaque écran de synthèse et sur chaque document PDF, afficher **clairement** les **dates de début et de fin** (ou la règle « depuis telle date selon l’abonnement »).

**Exemple.**  
Koffi a saisi des ventes de janvier à mars. Sans phrase sur la période, il ne sait pas si le **2 000 000 FCFA** affichés sont **uniquement mars** ou **les trois mois**. Avec la phrase explicite, il peut **vérifier** comme avec son cahier.

**Intérêt pour un agent de microfinance.**  
Il peut **reprendre les mêmes dates** dans son propre fichier et **justifier** son analyse devant sa hiérarchie.

---

#### Pistes de solution (D1 — à ne pas oublier)

**Affichage sur les écrans de synthèse**  
- **Bandeau ou sous-titre fixe** au-dessus des totaux : *Période : du [date début] au [date fin]* (dates réelles utilisées par le calcul).  
- Si un **filtre de dates** existe ou est ajouté : répéter la **période active** à côté des chiffres (éviter un total « flottant » sans repère).  
- Au niveau **exploitation consolidée** : rappeler qu’il s’agit de la **somme des campagnes en cours** sur cette période, pour éviter la confusion avec « une seule campagne ».

**Plancher d’historique (abonnement)**  
- Lorsque le plan **limite** l’historique : afficher explicitement *« Données à partir du [date] (formule d’abonnement) »* en plus ou à la place d’une date de début « naturelle », pour que l’utilisateur comprenne pourquoi le début ne remonte pas plus loin.

**Exports PDF et rapports**  
- **En-tête ou pied de page** : mêmes **dates de début et de fin** (et mention abonnement si applicable).  
- Aligner le **libellé de période** avec celui du tableau de bord pour éviter deux interprétations différentes.

**Mobile (PWA)**  
- Même **règle de visibilité** : une ligne lisible **sous le titre** ou au-dessus des cartes indicateurs, sans obliger l’utilisateur à deviner la fenêtre temporelle.

**Formulation courte (mémoire / oral)**  
*« Toute synthèse chiffrée doit indiquer la période couverte (début, fin) et, si besoin, la limite liée à l’abonnement ; les PDF reprennent les mêmes informations pour permettre le contrôle avec un carnet ou un dossier de crédit. »*

**Priorisation**  
- **V1 indispensable** : tableau de bord **web** + **PDF** avec période explicite.  
- **Suite** : parité **mobile**, raffinement si **filtres** ou **vues multiples** (par campagne vs exploitation) multiplient les contextes.

---

### D2. Saisie au référentiel versus saisie libre (impact sur les charges intermédiaires et la VAB)

**Situation actuelle.**  
Les **charges intermédiaires (CI)** ne comptent que les dépenses dont la catégorie est dans la **liste du programme**. Une dépense saisie en **texte libre** (par exemple « engrais acheté au marché ») est bien comptée dans les **coûts totaux** et donc dans le **RNE**, mais peut **ne pas** entrer dans **CI**.

**Pourquoi c’est gênant.**  
Le **RNE** peut être **cohérent** avec « tout l’argent sorti », alors que la **VAB** (qui utilise **CI**) peut sembler **trop haute**, comme si les intrants étaient sous-estimés.

**Exemple chiffré.**  
Recettes : **2 000 000 FCFA**. Une dépense **500 000 FCFA** pour des engrais, saisie en **libre**. Les **coûts totaux** incluent bien 500 000 → le **RNE** est correct au regard du cash. Mais si « engrais » n’est pas reconnu comme catégorie **CI**, la ligne **CI** est trop faible et la **VAB** est **gonflée artificiellement**.

---

#### Décision consolidée (référentiel, saisie libre, CI, VAB)

**Ce qu’il faut retenir du problème**

- **VAB = PB − CI** : les **charges intermédiaires (CI)** doivent être **définies de façon reproductible** ; on ne peut pas les déduire de façon fiable à partir **seulement** d’une phrase libre sans règle ni rattachement.
- Une **saisie libre isolée**, sans **information structurée** en coulisse, **dégrade** le total **CI** (et donc la **VAB**) lorsque le texte ne correspond à aucune règle du moteur.
- Les utilisateurs ne maîtrisent pas forcément les termes « intrant » ou « charges intermédiaires » : l’interface doit privilégier un **français simple** ; une **question de clarification** peut s’appuyer sur la **production de la campagne** (l’unité de suivi sous l’exploitation), sans jargon comptable à l’écran.

**Décision proposée (cible produit)**

1. **Une seule logique de saisie côté utilisateur**  
   Le **référentiel** sert d’**aide** : suggestions, liste, clic qui **remplit le même champ** que la saisie manuelle (**un seul parcours**, pas deux mondes séparés). La **saisie libre** reste **toujours** possible.

2. **Côté système (argumentaire soutenance)**  
   Chaque dépense enregistre au minimum : un **libellé** (ce que l’utilisateur voit ou a tapé) ; et une **information stable pour le calcul** : soit un **code** issu du référentiel (comme aujourd’hui avec le champ `categorie` / slug interne), soit — pour un **libellé nouveau** non reconnu — une **réponse à une question simple** du type *« Cet achat sert la production de cette campagne ? »* en **Oui / Non**, **mappée en interne** sur la règle **CI** (équivalent : « compte dans le CI » oui/non, sans afficher ces sigles à l’utilisateur). La question est **contextualisée** : elle porte sur la **campagne** en cours (plusieurs campagnes peuvent coexister sous une même exploitation).

3. **Référentiel enrichissable**  
   Si le libellé **n’existait pas** dans le référentiel : on **enregistre** quand même la transaction et on peut **mémoriser** le libellé (carnet personnel, ou file d’enrichissement selon l’ambition produit). La **question Oui / Non** évite de **gonfler le CI au hasard** pour les nouveaux libellés.

4. **Calcul des indicateurs**  
   Le **CI** ne se calcule **pas** en relisant une phrase libre au moment du calcul. Il s’appuie sur des **codes ou indicateurs** posés **à la saisie** (référentiel + clarification pour les nouveautés). La **VAB** reste **cohérente** avec cette règle.

5. **Piste technique (annexe ou backlog)**  
   Une **API interne** du type recherche sur le référentiel (`?q=…`) pour l’**autocomplétion** ; pas de dépendance obligatoire à une **API externe** « dictionnaire agronomique » universelle. Des raffinements (**correspondance floue**, file **admin** de validation des propositions) peuvent venir **après** une première version.

**Formulation courte (à réutiliser dans le mémoire ou l’oral)**  
*Nous unifions référentiel et saisie libre sur le même parcours : le référentiel propose et pré-remplit, la saisie libre reste ouverte. Pour garantir la fiabilité des charges intermédiaires et donc de la VAB, le moteur ne déduit pas le CI à partir du texte seul : il s’appuie sur un code issu du référentiel ou, pour un libellé nouveau, sur une clarification en langage courant (lien direct avec la production de la campagne : oui ou non). Les libellés nouveaux peuvent être mémorisés pour réutilisation sans imposer de jargon technique à l’utilisateur.*

**Priorisation pour une V1 ou une soutenance**  
Une **question simple Oui / Non** pour tout libellé **non reconnu**, plus les **codes référentiel** lorsque l’utilisateur choisit dans la liste, suffisent à **démontrer** la cohérence métier. **Correspondance floue**, **API de recherche** et **file de validation administrateur** sont des **étapes ultérieures** de la feuille de route.

**Intérêt pour la microfinance.**  
Moins de risque de présenter une **valeur ajoutée** qui ne **correspond** pas au **détail des factures** ; le lien entre **saisie**, **CI** et **VAB** devient **explicable** devant un jury ou un partenaire.

---

### D3. Erreurs possibles entre dépenses « fixes » et « variables »

**Situation actuelle.**  
La **marge brute** utilise seulement les **variables**. Le **RNE** utilise **toutes** les dépenses. Si on classe mal une grosse dépense, la **marge brute** change **sans** que le **RNE** change.

**Pourquoi c’est gênant.**  
L’utilisateur peut croire que « l’application triche » alors qu’il a seulement changé une case **fixe / variable**.

**Objectif après amélioration.**  
Courtes **explications** et **exemples** (élevage, cultures, maraîchage) directement sur l’écran de saisie.

**Exemple.**  
Même location de tracteur : classée en **variable**, la **marge brute** est plus basse ; classée en **fixe**, la **marge brute** est plus haute ; le **RNE** reste le même si le montant est le même. Il faut que l’utilisateur comprenne que **deux indicateurs répondent à deux questions différentes**.

---

#### Pistes de solution (D3 — à ne pas oublier)

**Principe**  
L’utilisateur **continue de choisir** **fixe** ou **variable** ; l’amélioration vise la **pédagogie**, pas la **suppression** du choix ni un classement **imposé** par le système sans validation métier.

**Écran de saisie de dépense (web et mobile)**  
- **Phrase courte** près du choix fixe / variable : par exemple *« La marge brute s’appuie surtout sur les dépenses variables ; le résultat après toutes les dépenses (RNE) additionne fixe et variable. »*  
- **Mini-exemples** (dans un bloc *Aide* repliable ou sous les options) : **variable** — semences, engrais liés au volume produit ; **fixe** — assurance annuelle, loyer d’un hangar. Adapter en **élevage**, **cultures**, **maraîchage** si l’écran le permet (texte unique ou variantes courtes).  
- **Cas ambigus** (location de tracteur, main-d’œuvre, etc.) : une ligne du type *« Selon votre façon de gérer l’exploitation, une même dépense peut être tenue pour fixe ou variable ; l’important est de rester cohérent. »*

**Cohérence**  
- **Même sens** des textes d’aide sur **toutes** les plateformes (éviter deux discours contradictoires entre desktop et mobile).

**Phase ultérieure (optionnelle)**  
- **Valeur par défaut** suggérée selon la **catégorie** de dépense (ex. semences → variable), **modifiable** par l’utilisateur : gain de temps sans retirer le contrôle.

**Documentation**  
- Renvoi au **lexique** (section A) ou au **centre d’aide** pour l’**oral**, le **mémoire** et les **lecteurs institutionnels**.

**À éviter en priorité**  
- Forcer fixe / variable **sans** choix utilisateur (risque métier selon les exploitations).  
- **Pavés de texte** sur le formulaire : privilégier **court sur place** + lien *En savoir plus* vers l’aide.

**Formulation courte (mémoire / oral)**  
*« Le choix fixe ou variable reste à l’utilisateur ; l’application doit expliquer sur l’écran de saisie pourquoi la marge brute et le RNE ne réagissent pas de la même façon, avec des exemples simples et le même message sur web et mobile. »*

**Priorisation**  
- **V1** : textes courts + exemples + aide repliable si besoin.  
- **V2** : défauts par catégorie, enrichissement du centre d’aide.

---

### D4. Plusieurs campagnes actives en même temps

**Situation actuelle.**  
Le total au niveau **exploitation** **additionne** les campagnes **en cours**.

**Pourquoi c’est gênant.**  
Un **total positif** peut **cacher** une campagne qui perd de l’argent.

**Objectif après amélioration.**  
Montrer **chaque campagne** avec ses chiffres, **puis** le total, avec une phrase du type : « Total de N campagnes en cours. »

**Exemple.**  
Campagne A : résultat **+300 000 FCFA**. Campagne B : **−100 000 FCFA**. Total affiché **+200 000 FCFA**. Sans le détail, on peut croire que **tout** va bien.

---

### D5. Seuil de rentabilité et statut au niveau « toute l’exploitation »

**Situation actuelle.**  
Au niveau **d’une campagne**, le **seuil de rentabilité** peut influencer le **statut vert**. Au niveau **consolidé** (plusieurs campagnes), le programme **ne recalcule pas** ce seuil de la même manière : la **règle du vert** n’est donc **pas identique**.

**Pourquoi c’est gênant.**  
Un conseiller peut demander : « Pourquoi le détail dit une chose et le total une autre ? »

**Objectif après amélioration.**  
Une **note** dans l’aide ou en bas de rapport : expliquer que le **total exploitation** se base surtout sur le **résultat agrégé** et **pas** sur un seuil fusionné.

---

#### Pistes de solution (D5 — à ne pas oublier)

**Rappel technique (comportement actuel du moteur)**  
- **Par campagne** : le **vert** exige un **RNE strictement positif** et, **lorsqu’un seuil de rentabilité est calculé** pour cette campagne, un **produit brut au moins égal à ce seuil** ; sinon le statut peut être **orange** ou **rouge** selon la marge.  
- **Au consolidé exploitation** : le **seuil** n’est **pas** fusionné sur la ligne agrégée (**SR non pris en compte** comme à la campagne) ; le **vert** du total repose sur une **règle simplifiée** (résultat agrégé, sans cette double condition).  
- **Tableau de bord global** (toutes exploitations) : **règle de couleur** encore **distincte** du détail campagne (synthèse sur totaux agrégés).  
*(Les libellés à l’écran doivent refléter ces différences sans imposer le vocabulaire du code.)*

**Interface : rendre la différence visible tout de suite**  
- **Libellés distincts** à côté du code couleur : *« Statut de la campagne »* vs *« Statut du total »* / *« Vue d’ensemble (N campagnes) »*, pour éviter de croire que **la même règle** s’applique partout.  
- **Bandeau ou ligne d’info** sous le **total** ou le **consolidé** : par exemple *« Ce total ne recalcule pas le seuil de rentabilité comme pour une seule campagne. Le détail par campagne indique le seuil et le statut complets. »*  
- **Infobulle (?)** sur la pastille du total : *« Ici, la couleur repose sur le résultat agrégé ; sur chaque campagne, le vert peut aussi dépendre du seuil de rentabilité. »*  
- **Afficher le seuil de rentabilité** principalement au **niveau campagne**, avec une mention du type *« Pour cette campagne »*, pour ancrer visuellement que cet indicateur **ne se transpose pas** tel quel sur la ligne fusionnée.

**PDF et rapports**  
- **Même encadré** ou note de bas de page lorsque le document présente un **total multi-campagnes**.

**Piste plus lourde (hors V1)**  
- Recalculer un **seuil « fusionné »** au niveau exploitation : **définition métier délicate** (mélange d’activités, d’échelles) ; souvent **moins prioritaire** qu’une **explication claire** à l’écran.

**Formulation courte (mémoire / oral)**  
*« Le code couleur et le seuil de rentabilité s’interprètent campagne par campagne ; le total exploitation ou le tableau de bord global utilisent une règle de synthèse différente — l’interface doit le dire en une phrase visible, sans attendre que l’utilisateur ouvre l’aide. »*

**Priorisation**  
- **V1** : libellés + bandeau ou infobulle sur les vues **consolidées** ; SR étiqueté **par campagne**.  
- **Suite** : centre d’aide et PDF ; étude **seuil fusionné** seulement si un besoin institutionnel le impose.

---

### D6. Peu de lignes saisies (dossier incomplet)

**Situation actuelle.**  
Avec très peu de transactions, un **gros chiffre vert** peut donner une fausse impression de santé.

**Objectif après amélioration.**  
Afficher un **avertissement** du genre : « Peu de données : les indicateurs sont indicatifs. »

**Exemple.**  
Une seule **grosse vente** saisie, **aucune dépense** : le résultat semble excellent alors que le dossier est vide.

---

#### Pistes de solution (D6 — à ne pas oublier)

**Principe**  
Les indicateurs restent **calculés** ; l’objectif est d’**avertir** l’utilisateur (et tout lecteur du PDF) que la **fiabilité** du diagnostic est **limitée** tant que le dossier est **trop léger**, **sans bloquer** la saisie ni les écrans.

**Critères possibles de « dossier incomplet »** (à calibrer en produit)  
- **Nombre total de transactions** sur la période ou sur la campagne **en dessous d’un seuil** (ex. moins de 5 lignes).  
- **Déséquilibre manifeste** : **recettes** sans **aucune dépense** (ou l’inverse) sur la campagne ou sur la période affichée.  
- **Une seule** transaction dominante (ex. une vente représente plus de X % du produit brut) **et** peu d’autres lignes — **optionnel**, plus sensible à paramétrer.  
- Au niveau **exploitation consolidée** : réutiliser une logique **agrégée** (ex. avertissement si **toutes** les campagnes actives sont « maigres ») ou **répéter** l’avertissement **par campagne** selon la clarté souhaitée.

**Interface**  
- **Bandeau ou encart** discret mais **visible** sur le **tableau de bord** et sur la **fiche campagne** (web et **mobile**) : *« Peu de données saisies : les indicateurs sont indicatifs. Pensez à enregistrer vos dépenses et recettes. »*  
- **Ne pas** remplacer le code couleur par défaut par une alerte rouge systématique : **prudence** ≠ **alarme fausse** ; l’avertissement est **complémentaire**.

**API / cohérence**  
- Exposer si besoin un indicateur du type **`donnees_indicatives`** (booléen) ou un **code** de complétude pour que **web**, **mobile** et **PDF** affichent le **même message**.

**PDF et rapports**  
- **Rappel** en en-tête ou encadré lorsque les critères sont remplis, pour éviter qu’un tiers institutionnel **photographie** un graphique **sans** le contexte.

**Formulation courte (mémoire / oral)**  
*« Tant que peu de lignes sont saisies, l’application doit signaler que les indicateurs sont indicatifs, avec des règles simples (volume, présence recettes/dépenses) et le même avertissement sur l’écran et les exports. »*

**Priorisation**  
- **V1** : règle minimale (ex. **moins de N** transactions **ou** recettes sans dépenses) + bandeau dashboard et vue campagne.  
- **V2** : affinage des seuils, **check-list** de saisie (« pensez aux charges fixes »), suivi de **complétude** dans le temps.

---

## E. Récapitulatif en une lecture (sans tableau)

**Période** : aujourd’hui souvent implicite → à rendre **toujours lisible** pour éviter les mauvaises comparaisons (bandeau dates, abonnement, PDF, mobile — voir **pistes D1**).

**Saisie libre** : peut **casser** la cohérence entre **totaux** et **VAB** si le moteur ne dispose pas d’un **code** ou d’une **clarification** → **parcours unifié** (référentiel + même champ) et **question simple** pour les libellés nouveaux, liée à la **production de la campagne** (voir D2).

**Fixe / variable** : sensible pour la **marge brute** → **pédagogie** sur place (l’utilisateur garde le choix — voir **pistes D3**).

**Plusieurs campagnes** : le **total** peut **masquer** un problème → afficher le **détail** avant le total.

**Seuil de rentabilité** : logique **différente** entre **une campagne** et le **total** → **explication visible** sur l’écran consolidé et en PDF (voir **pistes D5**).

**Peu de données** : risque de **sur-interprétation** → **signal de prudence** (règles + bandeau + PDF — voir **pistes D6**).

---

## F. Ordre des priorités (explication simple)

**Priorité 0 (à faire en premier)** — ce sans quoi on risque des **malentendus graves** :  
afficher clairement la **période** ; rendre **compréhensible** le lien entre **saisie libre** et indicateurs du type **valeur ajoutée / charges intermédiaires** (référentiel unifié + clarification **Oui / Non** pour les libellés non reconnus, sans jargon à l’écran — voir D2).

**Priorité 1 (ensuite)** — ce qui améliore la **qualité d’usage** :  
aide **fixe / variable** (voir **pistes D3**) ; affichage **campagne par campagne** avant le total ; **texte d’aide** sur les **règles de couleur** et le **consolidé** (voir **pistes D5** : libellés, bandeau, seuil au niveau campagne).

**Priorité 2 (amélioration continue)** — ce qui affine le produit :  
avertissement **données insuffisantes** (voir **pistes D6** : seuils, bandeau, API/PDF) ; rappel du **rôle** de l’outil (gestion d’exploitation, **pas** un scoring crédit complet sans autres informations).

---

## G. Ce que cette synthèse ne remplace pas

L’analyse détaillée du code a surtout porté sur le **moteur de calcul** et les **API** associées, pas sur **chaque écran**, **chaque PDF**, ni **tous les tests**. Le volet **sécurité & données** est dans `SYNTHESE-SOUTENANCE-SECURITE-DONNEES.md` ; le **produit & parcours** dans `SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md` ; l’**UX / accessibilité** dans `SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md` ; l’**architecture & infra** dans `SYNTHESE-SOUTENANCE-ARCHITECTURE-INFRA.md` ; les **paiements & abonnement** dans `SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md` ; la **stratégie & positionnement** dans `SYNTHESE-SOUTENANCE-STRATEGIE-POSITIONNEMENT.md`. Un **complément** peut encore détailler les **tests** automatisés au-delà de ces documents.

**Implémentation livrée (sprint S3) :** le compte rendu **`docs/SPRINT-S3-METIER-INDICATEURS.md`** décrit les mesures effectivement présentes dans le dépôt (période, `intrant_production`, `donnees_indicatives`, textes consolidés, PDF/API).

---

## H. Texte court pour l’oral (environ trente secondes)

*« AgroFinance+ additionne les recettes et les dépenses par campagne, puis calcule des indicateurs : produit brut, coûts variables et fixes, marge brute, résultat après toutes les dépenses, et un code couleur. Les formules sont cohérentes dans le programme, mais un agriculteur ou un agent de microfinance peut se tromper si la **période** n’est pas affichée, si la **saisie libre** n’alimente pas de façon claire les **charges intermédiaires** et la **valeur ajoutée**, ou si le **total** de plusieurs campagnes masque une campagne en difficulté. La cible est un **parcours unifié** référentiel + texte libre et une **clarification simple** pour les nouveaux libellés. Les travaux prioritaires portent sur la **transparence** de la période et sur cette **clarté** métier, puis sur la **pédagogie** et le détail par campagne, avant un audit plus large des écrans et des exports. »*

---

*Document à faire évoluer avec les autres volets du mémoire et l’audit complémentaire du code.*
