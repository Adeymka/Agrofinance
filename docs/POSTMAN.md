# Postman et clients HTTP — base `/api/v1`

Toutes les routes JSON de l’application sont préfixées par **`/api/v1`** (voir `routes/api.php`).

## Variable d’environnement

| Variable Postman (ex.) | Valeur |
|------------------------|--------|
| `base_url` | URL de l’app **sans** slash final, **avec** `/api/v1` |

**Exemples**

- Local Artisan : `http://127.0.0.1:8000/api/v1`
- XAMPP : `http://localhost/agrofinanceplus/public/api/v1`

Les requêtes utilisent alors : `{{base_url}}/auth/connexion`, `{{base_url}}/dashboard`, etc.

## En-têtes habituels

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{token}}
```

Obtenir `token` via `POST {{base_url}}/auth/connexion` (corps JSON : téléphone, pin).

## FedaPay — URL de callback (dashboard FedaPay)

À enregistrer **côté FedaPay** pour les paiements initiés via **l’API** (mobile, Postman, intégration serveur à serveur) :

```text
{APP_URL}/api/v1/abonnement/callback
```

Remplacez `{APP_URL}` par l’URL publique réelle (ex. `https://api.mondomaine.com`), la même que dans le fichier **`.env`** (`APP_URL`).

Pour un paiement initié depuis le **navigateur** (session web Laravel), le callback est une route **web** distincte :

```text
{APP_URL}/abonnement/callback
```

## Migration depuis `/api` (sans version)

Si d’anciennes collections pointaient vers `/api/...`, préfixer par **`v1`** : `/api/v1/...`.
