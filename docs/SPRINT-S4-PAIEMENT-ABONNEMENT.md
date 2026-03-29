# Sprint S4 — Paiement & abonnement

**Statut : terminé** (P0–P1 couverts dans le périmètre ci-dessous ; pas de refonte parcours large S5 ni a11y systématique S6).  
**Références :** `PLAN-CORRECTIONS-PAR-SPRINT.md` · `SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`

Ce document est la **source de vérité** pour les changements **abonnement / FedaPay** livrés dans le dépôt et la **recette** associée.

---

## 1. Synthèse exécutive

| Objectif synthèse | Réalisation |
|-------------------|-------------|
| P0 — Cohérence prix / moteur / passerelle (D1) | **Oui** — `config/tarifs_abonnement.php` + `TarifsAbonnement` ; `AbonnementService::montantFacturation` en déduit les montants ; pages **abonnement** et **accueil** utilisent les mêmes libellés. |
| P0 — Callback fiable | **Oui** — cache `fedapay_pending:{id}` enrichi avec `montant_fcfa` ; en cas d’écart avec le montant FedaPay, **avertissement journalisé** (pas de données sensibles dans l’URL au-delà de l’id transaction attendu par FedaPay). |
| P0 — Après paiement clair (D2) | **Oui** — messages flash web reformulés (paiement reçu / déjà enregistré / simulation). |
| P1 — Droits visibles (D4) | **Oui** — tableau **« Ce que chaque formule permet »** sur la page abonnement (web + mobile). |
| P1 — Fin d’abonnement (D6) | **Oui** — bannière si **≤ 7 jours** restants (layouts desktop + mobile), lien vers la page formules. |
| P1 — Mock documenté | **Oui** — `docs/API_CLIENT.md` + ce document ; comportement inchangé (`FEDAPAY_MOCK=true`). |

---

## 2. Thèmes D1–D6 — état

| Thème | Statut | Détail |
|-------|--------|--------|
| **D1** | **Fait** | Source unique `config/tarifs_abonnement.fcfa` ; affichage via `TarifsAbonnement::libelleEspace`. |
| **D2** | **Fait** | Messages après retour paiement / mock plus explicites pour l’exploitant. |
| **D3** | **Fait** | Mock inchangé ; doc API rappelée. |
| **D4** | **Fait** | Tableau plan → PDF / exploitations / historique / dossier crédit sur la page abonnement. |
| **D5** | **Hors sprint** | Facturation institutionnelle / sponsoring : hors produit (synthèse). |
| **D6** | **Partiel** | Bannière J−7 ; pas d’e-mail transactionnel (P2). |

---

## 3. Fichiers principaux

| Zone | Fichiers |
|------|----------|
| Tarifs | `config/tarifs_abonnement.php`, `app/Support/TarifsAbonnement.php` |
| Métier | `app/Services/AbonnementService.php` (montants, cache callback + contrôle montant) |
| Web | `app/Http/Controllers/Web/AbonnementController.php`, `Web/PublicController.php` (accueil) |
| Vues | `resources/views/abonnement/index.blade.php`, `public/accueil.blade.php`, `layouts/app-desktop.blade.php`, `layouts/app-mobile.blade.php` |
| Tests | `tests/Feature/TarifsAbonnementTest.php`, `tests/Feature/Sprint5Test.php` (montant mock) |
| Doc | `docs/API_CLIENT.md`, `docs/SYNTHESE-SOUTENANCE-PAIEMENT-ABONNEMENT.md`, `AGENTS.md` |

---

## 4. Recette manuelle (numérotée)

1. **Accueil public** — Vérifier les **trois prix** payants (Essentielle, Pro, Coopérative) : ils correspondent à `config/tarifs_abonnement.php` (actuellement **5 000 / 10 000 / 16 000** FCFA/mois).
2. **Abonnement connecté** — Même alignement sur les cartes ; le **tableau des droits** est lisible (PDF, exploitations, historique, dossier crédit).
3. **Mock** — Avec `FEDAPAY_MOCK=true`, choisir un plan puis **Confirmer la simulation** : message de succès clair ; accès aux routes « métier » conservé.
4. **API** — `POST /api/v1/abonnement/initier` (Bearer) : `data.montant` = montant FCFA du plan (ex. **5000** pour `mensuel`).
5. **Bannière fin de période** — Compte avec abonnement se terminant dans **≤ 7 jours** : bannière orange sur **dashboard** (desktop et mobile) avec lien « Voir les formules ».

**Résultats attendus** : pas d’écart prix page d’accueil / page abonnement / réponse API ; pas d’erreur serveur sur les parcours ci-dessus.

---

## 5. Hors périmètre (volontaire)

- E-mails ou SMS de relance avant expiration.
- Passerelle autre que FedaPay ; codes promo.
- Page institutionnelle « sponsoring » dans le produit.

---

*Document figé pour clôture sprint S4 — à actualiser si `config/tarifs_abonnement.php` ou les règles de droits évoluent.*
