# Déploiement AgroFinance+ sur VPS — Apache (étape par étape)

Guide pour **Ubuntu Server 22.04 LTS** (ou 24.04). Remplace `ton-domaine.com` et les chemins si besoin. Exécute les commandes **sur le VPS** en SSH (`ssh utilisateur@IP_DU_VPS`).

---

## Étape 1 — Connexion et mise à jour du système

```bash
sudo apt update && sudo apt upgrade -y
```

---

## Étape 2 — Installer Apache, MySQL, PHP et extensions Laravel

```bash
sudo apt install -y apache2 mysql-server git unzip curl
sudo apt install -y php8.2 php8.2-cli libapache2-mod-php8.2 \
  php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
  php8.2-bcmath php8.2-intl
```

*(Si `php8.2` n’existe pas, installe la version proposée par `apt search php` et adapte les paquets.)*

Vérifier PHP :

```bash
php -v
```

---

## Étape 3 — Activer les modules Apache nécessaires

```bash
sudo a2enmod rewrite headers ssl
sudo systemctl restart apache2
```

---

## Étape 4 — Créer la base MySQL

```bash
sudo mysql
```

Dans la console MySQL :

```sql
CREATE DATABASE agrofinanceplus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agro'@'localhost' IDENTIFIED BY 'CHOISIS_UN_MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON agrofinanceplus.* TO 'agro'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Étape 5 — Installer Composer (globalement)

```bash
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer --version
```

---

## Étape 6 — Récupérer le projet sur le serveur

Exemple avec Git (remplace l’URL par ton dépôt) :

```bash
cd /var/www
sudo git clone https://github.com/TON_COMPTE/agrofinanceplus.git
sudo chown -R $USER:www-data agrofinanceplus
cd agrofinanceplus
```

Si tu n’as pas Git : envoie le dossier du projet en **ZIP** sur le VPS et décompresse-le dans `/var/www/agrofinanceplus`.

---

## Étape 7 — Dépendances PHP et fichier `.env`

```bash
cd /var/www/agrofinanceplus
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env
```

Dans `.env`, configure au minimum :

| Variable | Exemple production |
|----------|-------------------|
| `APP_NAME` | `AgroFinance+` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://ton-domaine.com` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `127.0.0.1` |
| `DB_DATABASE` | `agrofinanceplus` |
| `DB_USERNAME` | `agro` |
| `DB_PASSWORD` | le mot de passe de l’étape 4 |

Puis : FedaPay, Africa’s Talking, `SESSION_DRIVER`, `CACHE_STORE`, etc. comme en local mais avec les **clés live** si tu es en production réelle.

---

## Étape 8 — Assets front (Vite)

Sur le VPS **ou** sur ton PC puis envoi du dossier `public/build` :

```bash
# Sur le VPS, si Node est installé (ex. via NodeSource ou nvm)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
cd /var/www/agrofinanceplus
npm ci
npm run build
```

*(Alternative : lancer `npm run build` en local et transférer `public/build` sur le VPS.)*

---

## Étape 9 — Migrations et caches Laravel

```bash
cd /var/www/agrofinanceplus
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Étape 10 — Droits sur `storage` et `bootstrap/cache`

```bash
cd /var/www/agrofinanceplus
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

---

## Étape 11 — Virtual Host Apache (DocumentRoot = `public/`)

Créer le fichier de site :

```bash
sudo nano /etc/apache2/sites-available/agrofinanceplus.conf
```

Coller (remplace `ton-domaine.com` ; pour tester sans DNS, mets l’IP du VPS dans `ServerName` ou utilise `ServerAlias`) :

```apache
<VirtualHost *:80>
    ServerName ton-domaine.com
    ServerAlias www.ton-domaine.com
    DocumentRoot /var/www/agrofinanceplus/public

    <Directory /var/www/agrofinanceplus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/agrofinanceplus-error.log
    CustomLog ${APACHE_LOG_DIR}/agrofinanceplus-access.log combined
</VirtualHost>
```

Activer le site et désactiver le défaut si besoin :

```bash
sudo a2ensite agrofinanceplus.conf
sudo a2dissite 000-default.conf
sudo apache2ctl configtest
sudo systemctl reload apache2
```

---

## Étape 12 — HTTPS avec Let’s Encrypt (recommandé)

```bash
sudo apt install -y certbot python3-certbot-apache
sudo certbot --apache -d ton-domaine.com -d www.ton-domaine.com
```

Puis mets à jour `APP_URL` en `https://...` et :

```bash
cd /var/www/agrofinanceplus
php artisan config:cache
```

---

## Étape 13 — File d’attente et planificateur (optionnel mais utile)

Le projet peut utiliser `QUEUE_CONNECTION=database`. Un worker doit tourner en continu :

```bash
cd /var/www/agrofinanceplus
php artisan queue:work --sleep=3 --tries=3
```

Pour la production, configure **Supervisor** pour relancer `queue:work` automatiquement (voir la doc Laravel « Queue Workers »).

**Cron Laravel** (crontab du user qui exécute le site, souvent `www-data` ou ton user déployeur) :

```bash
sudo crontab -e -u www-data
```

Ajouter une ligne :

```
* * * * * cd /var/www/agrofinanceplus && php artisan schedule:run >> /dev/null 2>&1
```

*(Adapte le chemin si ton projet n’est pas dans `/var/www/agrofinanceplus`.)*

---

## Étape 14 — Vérifications

1. Ouvre `http://ton-domaine.com` ou `https://...` : page d’accueil ou redirection attendue.
2. Test **inscription** + OTP (SMS ou log selon config).
3. Vérifie les logs Apache / Laravel en cas d’erreur :

```bash
sudo tail -f /var/log/apache2/agrofinanceplus-error.log
tail -f /var/www/agrofinanceplus/storage/logs/laravel.log
```

---

## Rappels importants

- Ne commite **jamais** le `.env` du serveur.
- `APP_URL` doit être l’URL **réelle** (HTTPS) pour FedaPay, SMS, PWA.
- En prod : `APP_DEBUG=false`.
- Si tu vois une page blanche ou 500, regarde `storage/logs/laravel.log` et les logs Apache.

---

*Document généré pour le projet AgroFinance+ — Apache sur VPS.*
