# Documentation API — AgroFinance+

**Base URL :** `http://localhost/agrofinanceplus/public/api`  
**Auth :** `Authorization: Bearer <token>` (Sanctum)  
**Content-Type :** `application/json`

---

## Authentification

### POST /auth/inscription
Crée un utilisateur et envoie un OTP par SMS.

**Body**
```json
{
  "nom": "Amoussou",
  "prenom": "Kofi",
  "telephone": "+22997000000",
  "type_exploitation": "cultures_vivrieres"
}
```
`type_exploitation` : `cultures_vivrieres | elevage | maraichage | transformation | mixte`

**Réponses**
| Code | Description |
|---|---|
| 200 | OTP envoyé |
| 422 | Validation échouée / numéro déjà utilisé |

---

### POST /auth/verification-otp
Vérifie le code OTP reçu par SMS.

**Body**
```json
{ "telephone": "+22997000000", "code": "123456" }
```
**Réponse 200**
```json
{ "succes": true, "otp_token": "token_creation_pin_xxx" }
```

---

### POST /auth/creer-pin
Crée le code PIN après vérification OTP.

**Body**
```json
{ "telephone": "+22997000000", "pin": "1234", "otp_token": "token_creation_pin_xxx" }
```
**Réponse 201** : `{ "succes": true, "message": "PIN créé." }`

---

### POST /auth/connexion
Connexion et obtention du token API.

**Body**
```json
{ "telephone": "+22997000000", "pin": "1234" }
```
**Réponse 200**
```json
{ "succes": true, "token": "Bearer_token_ici", "user": { "id": 1, "nom": "Amoussou", "prenom": "Kofi" } }
```
| Code | Description |
|---|---|
| 200 | Connexion réussie |
| 401 | PIN incorrect |
| 429 | Trop de tentatives (rate limit 10/5min) |

---

### POST /auth/deconnexion 🔒
Révoque le token actuel.

**Réponse 200** : `{ "succes": true }`

---

## Exploitations 🔒 (Auth requise)

### GET /exploitations
Liste les exploitations de l'utilisateur.

**Réponse 200**
```json
{
  "succes": true,
  "data": [{ "id": 1, "nom": "Ferme Nord", "type": "cultures_vivrieres" }]
}
```

### POST /exploitations
Crée une exploitation (limite selon plan abonnement).

**Body**
```json
{
  "nom": "Ferme Nord",
  "type": "cultures_vivrieres",
  "superficie": 2.5,
  "localisation": "Parakou"
}
```
| Code | Description |
|---|---|
| 201 | Exploitation créée |
| 402 | Limite du plan atteinte |

### PUT /exploitations/{id}
Met à jour une exploitation.

### DELETE /exploitations/{id}
Supprime une exploitation.

---

## Activités 🔒

### GET /activites
Liste toutes les activités de l'utilisateur.

### POST /activites
Crée une activité liée à une exploitation.

**Body**
```json
{
  "exploitation_id": 1,
  "nom": "Campagne maïs 2026",
  "type": "cultures_vivrieres",
  "date_debut": "2026-01-15",
  "date_fin": "2026-07-30",
  "budget_previsionnel": 500000
}
```
**Réponse 201** : `{ "succes": true, "data": { ... } }`

### GET /activites/{id}
Détail d'une activité + indicateurs FSA + alerte budget.

### PUT /activites/{id}
Mise à jour partielle d'une activité.

### POST /activites/{id}/cloturer
Clôture une campagne (`statut` → `termine`). Atomique via `lockForUpdate`.

**Réponse 200** : `{ "succes": true, "message": "Activite cloturee." }`

### POST /activites/{id}/abandonner
Marque une campagne comme abandonnée.

---

## Transactions 🔒

### GET /transactions
Liste les transactions (filtrables par `activite_id`, `type`, `date_debut`, `date_fin`).

### POST /transactions
Saisie d'une ou plusieurs transactions (batch atomique).

**Body (saisie unique)**
```json
{
  "activite_id": 1,
  "type": "depense",
  "nature": "variable",
  "categorie": "semences",
  "montant": 25000,
  "date_transaction": "2026-02-10",
  "description": "Achat semences certifiées"
}
```

**Body (batch)**
```json
{
  "transactions": [
    { "activite_id": 1, "type": "recette", "montant": 80000, "date_transaction": "2026-05-01" },
    { "activite_id": 1, "type": "depense", "nature": "fixe", "categorie": "salaires", "montant": 15000, "date_transaction": "2026-05-01" }
  ]
}
```

**Catégories disponibles** : `semences`, `engrais_mineraux`, `engrais_organiques`, `pesticides`, `herbicides`, `fongicides`, `vaccins`, `medicaments_veterinaires`, `aliments_animaux`, `eau_abreuvement`, `energie_transformation`, `emballages`, `matieres_premieres`, `produits_chimiques`, `carburant`, `salaires`, `transport`, `location`, `services`, `autres`

### PUT /transactions/{id}
Mise à jour d'une transaction.

### DELETE /transactions/{id}
Suppression d'une transaction.

---

## Indicateurs FSA 🔒

### GET /indicateurs/{activite_id}
Calcule les indicateurs financiers (PB, CV, CF, CT, CI, VAB, MB, RNE, RF, SR, statut).

**Query params** : `?periode_debut=2026-01-01&periode_fin=2026-06-30`

**Réponse 200**
```json
{
  "succes": true,
  "data": {
    "PB": 320000.0,
    "CV": 85000.0,
    "CF": 40000.0,
    "CT": 125000.0,
    "CI": 60000.0,
    "VAB": 260000.0,
    "MB": 235000.0,
    "RNE": 195000.0,
    "RF": 156.0,
    "SR": 47619.0,
    "statut": "vert"
  }
}
```

### GET /indicateurs/{activite_id}/evolution
Evolution mensuelle sur 12 mois (pour graphique).

---

## Rapports PDF 🔒

### GET /rapports
Liste les rapports générés.

### POST /rapports/generer
Génère un rapport PDF de façon asynchrone.

**Body**
```json
{
  "activite_id": 1,
  "type": "campagne",
  "periode_debut": "2026-01-01",
  "periode_fin": "2026-06-30"
}
```
`type` : `campagne | dossier_credit | mensuel | annuel`

**Réponse 201** (Job dispatché)
```json
{
  "succes": true,
  "data": {
    "rapport_id": 5,
    "type": "campagne",
    "lien_token": "abc123...",
    "lien_expire_le": "30/03/2026 10:00",
    "lien_partage": "https://domaine.bj/partage/abc123...",
    "indicateurs": { "PB": 320000, "RNE": 195000, "RF": 156.0, "statut": "vert" }
  }
}
```
| Code | Description |
|---|---|
| 201 | Rapport en cours de génération |
| 402 | Plan sans accès PDF |

### GET /rapports/{id}/telecharger
Télécharge le PDF (binaire, chiffré décrypté à la volée).

**Réponse 200** : `Content-Type: application/pdf`  
**Réponse 425** : PDF pas encore prêt (réessayer dans quelques secondes)

---

## Abonnements 🔒

### GET /abonnement/statut
Retourne le plan actuel, la date d'expiration et les droits.

**Réponse 200**
```json
{
  "plan": "essentielle",
  "plan_metier": "essentielle",
  "statut": "actif",
  "date_fin": "2026-04-27",
  "jours_restants": 30,
  "peut_pdf": true,
  "peut_dossier": false,
  "peut_multi": false,
  "max_exploitations": 1
}
```

### POST /abonnement/initier
Initie un paiement FedaPay.

**Body**
```json
{ "plan": "mensuel", "telephone": "+22997000000" }
```
`plan` : `mensuel | annuel | cooperative`

**Réponse (mode réel)**
```json
{ "succes": true, "data": { "transaction_id": "fp_123", "url_paiement": "https://checkout.fedapay.com/..." } }
```

**Réponse (FEDAPAY_MOCK=true)**
```json
{ "succes": true, "data": { "mock": true, "finaliser_mock": "POST /api/abonnement/finaliser-mock" } }
```

### POST /abonnement/finaliser-mock *(dev seulement)*
Simule un paiement réussi (nécessite `FEDAPAY_MOCK=true`).

### GET /abonnement/callback *(public, FedaPay)*
Callback automatique après paiement. Activé l'abonnement si `status=approved`.

---

## Santé & Métriques

### GET /health *(public)*
Health check (BDD, cache, stockage).

**Réponse 200** : `{ "status": "ok", "checks": { "database": "ok", "cache": "ok", "storage": "ok" } }`

### GET /metrics *(public, token requis)*
Métriques Prometheus.  
Header : `Authorization: Bearer <METRICS_TOKEN>` ou `?token=<METRICS_TOKEN>`

---

## Codes d'erreur courants

| Code | Signification |
|---|---|
| 401 | Non authentifié — vérifier le header `Authorization: Bearer <token>` |
| 402 | Limite plan abonnement atteinte |
| 403 | Accès interdit (ressource d'un autre utilisateur) |
| 404 | Ressource introuvable |
| 422 | Données invalides (voir `errors` dans la réponse) |
| 425 | PDF en cours de génération — réessayer |
| 429 | Rate limit atteint |
| 503 | Service externe indisponible (FedaPay / Vonage) |
