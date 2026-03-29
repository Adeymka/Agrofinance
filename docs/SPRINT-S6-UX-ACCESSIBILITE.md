# Sprint S6 — UX & accessibilité

**Statut : terminé** (P0–P1 du périmètre ci-dessous ; pas d’audit WCAG certifié ni thème clair complet).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md`

Ce document est la **source de vérité** pour les changements **lisibilité**, **tactile**, **erreurs**, **hiérarchie** et **a11y de base** livrés dans ce sprint.

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| P0 — Contraste / textes critiques | **Oui** — token **`--af-text-kpi`** ; renfort **`prefers-contrast: more`** sur variables de texte ; montant hero mobile (RNE) avec couleur lisible ; erreurs auth en **`#fecaca`**. **Complément** : option **Lecture plein soleil** (`localStorage` **`af_outdoor_boost`**, classe **`af-outdoor`** sur `<html>`, profil **Affichage**) ; léger **`text-shadow`** sur les gros chiffres du dashboard mobile. |
| P0 — Zones tactiles | **Oui** — **`min-height: var(--af-touch-min)`** sur **`auth-btn`** (desktop), **`auth-mobile-btn`**, **`btn-primary` / `btn-outline`**, saisie transaction (**type**, **nature**, **mode**, **footer**). |
| P0 — Erreurs non limitées à la couleur | **Oui** — icône **⚠** (pseudo-élément) sur **`.auth-error`** / **`.auth-mobile-error`** ; **`role="alert"`** + **`aria-live`** ; résumé **`txm-error-summary`** en tête du formulaire transaction mobile. |
| P1 — Hiérarchie dashboard | **Partiel** — **« Vue d’ensemble »** explicite (mobile : **`h1`** ; desktop : libellé au-dessus de la carte héro). |
| P1 — Audit Lighthouse / axe | **Doc** — pages recommandées pour contrôle manuel : **`/connexion`**, **`/dashboard`** (connecté), **`/transactions/nouvelle`** (mobile). |
| P1 — Démo institutionnelle | **Checklist** — section recette ci-dessous. |

---

## 2. Thèmes D1–D6 — état

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** | **Partiel** | Tokens + contraste forcé ; mode optionnel **plein soleil** côté utilisateur (`af-outdoor`, pas un second thème clair complet). |
| **D2** | **Partiel** | Dock : **focus visible** ; cibles formulaires / CTA renforcées. |
| **D3** | **Partiel** | Auth (y compris **inscription → OTP → PIN**) + transaction mobile ; champs inchangés côté validation métier. |
| **D4** | **Partiel** | Titre de section « Vue d’ensemble » sans refonte des blocs. |
| **D5** | **Partiel** | **`:focus-visible`** global ; labels **`for`/`id`** sur **connexion, inscription, OTP, PIN** ; flashs **`role`/`aria-live`** (layout mobile) ; pas d’audit lecteur d’écran complet. |
| **D6** | **Partiel** | Checklist démo ; pas de kit captures livré dans le dépôt. |

---

## 3. Fichiers principaux

| Zone | Fichiers |
|------|----------|
| Tokens & focus | `resources/css/app.css` (dont **`html.af-outdoor`**, préférence `af_outdoor_boost`) |
| Auth | `resources/views/layouts/app-auth.blade.php`, `resources/views/auth/connexion.blade.php`, `inscription.blade.php`, `otp.blade.php`, `creer-pin.blade.php` |
| Layout mobile / desktop | `app-mobile.blade.php`, `app-desktop.blade.php` (script **plein soleil** en tête de `<head>`), flashs **ARIA** (`app-mobile`) |
| Dashboard | `resources/views/dashboard/index.blade.php` |
| Profil | `resources/views/profil/index.blade.php` (section **Affichage**) |
| Saisie | `resources/views/transactions/create.blade.php` |
| Doc | ce fichier, `docs/PLAN-CORRECTIONS-PAR-SPRINT.md`, `docs/HISTORIQUE_AMELIORATIONS.md`, `docs/SYNTHESE-SOUTENANCE-UX-ACCESSIBILITE.md` |

---

## 4. Critères de done (sprint)

- **Pas de régression** sur les parcours métier (tests automatisés + smoke manuel).
- **Focus** visible au clavier sur les éléments interactifs modifiés.
- **Erreurs** lisibles (texte + repère visuel autre que la seule couleur de fond).

---

## 5. Recette manuelle (numérotée)

1. **Connexion (mobile + desktop)** — Onglet jusqu’aux champs : le **focus** est un **anneau vert** visible ; soumettre avec champs vides : message d’erreur avec **⚠** et texte clair.
2. **Inscription, OTP, création PIN** — Chaque champ a un **libellé associé** (`for`/`id`) ; sur OTP desktop, le champ code a un **libellé visible** ; les messages flash sur **mobile connecté** sont annoncés (**`role` / `aria-live`**).
3. **Dashboard mobile** — Le premier titre de la zone principale est **« Vue d’ensemble »** ; les chiffres du bloc héros restent lisibles.
4. **Nouvelle transaction (mobile)** — Les boutons **Dépense / Recette** et **Suivant** ont une hauteur **au moins 44 px** ; en cas d’erreur serveur, un **encart récapitulatif** apparaît en haut du formulaire.
5. **Dock mobile** — Au clavier, chaque onglet du bas reçoit un **focus** visible.
6. **Lighthouse (optionnel)** — Lancer un audit **Accessibilité** sur **`/connexion`**, **`/dashboard`**, **`/transactions/nouvelle`** (scores indicatifs, pas de seuil contractuel dans ce sprint).
7. **Profil → Affichage « Lecture plein soleil »** — Cocher : rechargement ou navigation : la classe **`af-outdoor`** reste sur `<html>` ; textes secondaires et surfaces glass plus lisibles ; décocher : retour au thème par défaut.

**Démo institutionnelle** — Avant une présentation : tester sur le **support réel** (vidéoprojecteur ou téléphone) ; vérifier zoom navigateur à **100 %** ; prévoir une **capture** sur **dashboard** avec données anonymisées.

---

## 6. Hors périmètre (volontaire)

- Audit **WCAG 2.x** certifié ou **RGAA** complet.
- Thème **clair** complet (le mode **plein soleil** renforce le contraste sur le thème glass existant, sans palette claire).
- **Internationalisation** des messages.
- Refonte **graphique** large des écrans.

---

*Document figé pour clôture sprint S6 — à actualiser si les tokens `--af-*` ou les parcours critiques changent.*
