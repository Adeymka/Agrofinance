# Sprint S5 — Produit & parcours

**Statut : terminé** (P0–P1 du périmètre ci-dessous ; pas d’audit UX systématique S6 ni refonte centre d’aide S7).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-PRODUIT-PARCOURS.md`

Ce document est la **source de vérité** pour les changements **parcours**, **messages de blocage**, **synchro hors ligne** et **textes produit** livrés dans ce sprint.

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| P0 — Messages abonnement / exploitation | **Oui** — distinction **sans formule jamais souscrite** vs **période terminée** (`AbonnementService::aHistoriqueAbonnement`, `VerifierAbonnement`) ; flash web et JSON API alignés (`ABONNEMENT_REQUIS` / `ABONNEMENT_EXPIRE`). |
| P0 — Synchro offline visible | **Oui** — bannière enrichie (compte des lignes, message session / réseau, bouton **Synchroniser**) ; **parité** desktop : `meta api-base` + même bloc `#afPendingSyncBanner` dans `app-desktop`. |
| P0 — Page `/offline` | **Oui** — rappel des saisies locales ; `manifest` via `asset()`. |
| P1 — Partage PDF | **Oui** — court texte d’avertissement (mobile + desktop) sur la page rapports. |
| P1 — Doc API | **Oui** — `docs/API_CLIENT.md` : codes **403**, règles synchro hors ligne. |

---

## 2. Thèmes D1–D6 — état

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** | **Partiel** | Messages middleware abonnement clarifiés ; parcours OTP/PIN inchangé (déjà couverts S1/S4). |
| **D2** | **Partiel** | Même file offline et bannière sur mobile et desktop authentifiés. |
| **D3** | **Inchangé** | Dashboard déjà redirigé avec message si pas d’exploitation (S3). |
| **D4** | **Fait** | `offline-transactions.js` : hints 401 / réseau, `__AF_syncPendingQueue`, pas d’exposition des montants dans la bannière. |
| **D5** | **Partiel** | Texte **partage** sur la liste des rapports ; déconnexion inchangée. |
| **D6** | **Partiel** | Page **offline** rassurante ; pas de réécriture massive des articles d’aide (hors scope S7). |

---

## 3. Fichiers principaux

| Zone | Fichiers |
|------|----------|
| Métier | `app/Services/AbonnementService.php`, `app/Http/Middleware/VerifierAbonnement.php` |
| UI | `resources/views/layouts/app-mobile.blade.php`, `resources/views/layouts/app-desktop.blade.php` |
| JS | `resources/js/offline-transactions.js` |
| Rapports / offline | `resources/views/rapports/index.blade.php`, `resources/views/public/offline.blade.php` |
| Tests | `tests/Feature/VerifierAbonnementTest.php` |
| Doc | `docs/API_CLIENT.md`, `README.md` (ligne middleware) |

---

## 4. Critères de done (sprint)

- Aucun accès métier **sans abonnement** ne se termine sans **message explicite** (web + API).
- La synchro hors ligne indique **l’état** (attente, session, réseau) et permet un **nouvel essai** sans recharger la page.
- Les **rapports** rappellent la **confidentialité** du lien de partage.
- La documentation **API** reflète les **codes** et le comportement **PWA**.

---

## 5. Recette manuelle (numérotée)

1. **Compte neuf sans abonnement** (ou après essai non renouvelé sans ligne active) — Se connecter, tenter une route métier (ex. dashboard) : redirection ou **403** API avec message adapté ; en API JSON, `code` = `ABONNEMENT_REQUIS` si aucun historique en base, sinon `ABONNEMENT_EXPIRE`.
2. **Mobile authentifié** — Créer une transaction hors ligne (ou simuler file IndexedDB) : la bannière bleue affiche le **nombre** de lignes ; **Synchroniser** relance l’envoi.
3. **Session API expirée** avec file non vide — Message jaune invitant à **se reconnecter** ; après nouvelle session (token meta), synchronisation OK.
4. **Desktop** — Même bannière sous les flash, avec **meta** `api-base` présent.
5. **Rapports** — Lire le paragraphe **Partage** sous le titre (mobile et desktop).
6. **Page `/offline`** — Texte sur les saisies conservées sur l’appareil ; manifest chargé via sous-dossier (`asset`).

**API (Postman)** — `GET /api/v1/dashboard` sans abonnement : **403** + `ABONNEMENT_REQUIS` ou `ABONNEMENT_EXPIRE` selon l’historique (voir tests automatisés).

---

## 6. Hors périmètre (volontaire)

- Refonte **aide** / articles « premiers pas » (S7).
- **Accessibilité** poussée et harmonisation erreurs API sur tous les endpoints (S6 / travaux transverses).
- Onboarding guidé pas à pas écran complet.

---

*Document figé pour clôture sprint S5 — à actualiser si les règles d’abonnement ou le contrat offline évoluent.*
