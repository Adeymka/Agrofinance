# Guide de Déploiement — AgroFinance+ (#42)

Stack recommandée : **Ubuntu 22.04 LTS + Nginx + PHP 8.2-FPM + MySQL 8 + Redis 7 + Supervisor**

---

## 1. Prérequis serveur

```bash
apt update && apt upgrade -y
apt install -y nginx mysql-server redis-server supervisor \
    php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-gd php8.2-zip php8.2-intl php8.2-bcmath php8.2-redis \
    php8.2-curl git unzip curl
```

---

## 2. Cloner et configurer l'application

```bash
cd /var/www
git clone https://github.com/Adeymka/Agrofinance.git agrofinanceplus
cd agrofinanceplus

# Dépendances PHP
composer install --no-dev --optimize-autoloader

# Dépendances Node (assets)
npm ci && npm run build

# Configuration
cp .env.production.example .env
nano .env                      # Remplir toutes les variables
php artisan key:generate

# Droits
chown -R www-data:www-data /var/www/agrofinanceplus
chmod -R 755 /var/www/agrofinanceplus
chmod -R 775 storage bootstrap/cache
```

---

## 3. Base de données

```bash
mysql -u root -p
CREATE DATABASE agrofinanceplus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agrofinance'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL ON agrofinanceplus.* TO 'agrofinance'@'localhost';
FLUSH PRIVILEGES;
EXIT;

php artisan migrate --force
php artisan db:seed --force    # Si tu veux les données de base (plans, aide)
```

---

## 4. Nginx (#42)

```bash
nano /etc/nginx/sites-available/agrofinanceplus
```

```nginx
server {
    listen 80;
    server_name votre-domaine.bj www.votre-domaine.bj;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name votre-domaine.bj www.votre-domaine.bj;

    root /var/www/agrofinanceplus/public;
    index index.php;
    charset utf-8;

    # SSL (Let's Encrypt via certbot)
    ssl_certificate     /etc/letsencrypt/live/votre-domaine.bj/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.bj/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logs
    access_log  /var/log/nginx/agrofinance_access.log;
    error_log   /var/log/nginx/agrofinance_error.log;

    # Assets statiques Vite
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot|webp)$ {
        expires max;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Bloquer accès aux fichiers sensibles
    location ~ /\.(env|git|htaccess) { deny all; }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass   unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
        fastcgi_read_timeout 300;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/agrofinanceplus /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

---

## 5. SSL avec Let's Encrypt

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d votre-domaine.bj -d www.votre-domaine.bj
# Auto-renouvellement tous les 90 jours (déjà géré par certbot systemd timer)
```

---

## 6. PHP-FPM — Optimisation

```bash
nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10
pm.max_requests = 500
```

```bash
systemctl restart php8.2-fpm
```

---

## 7. Redis (#11 / #41)

Vérifier que Redis tourne sur 127.0.0.1:6379 :

```bash
redis-cli ping    # → PONG
```

Dans `.env` production :
```
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

## 8. Supervisor — Queue worker (#25)

```bash
nano /etc/supervisor/conf.d/agrofinance-worker.conf
```

```ini
[program:agrofinance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agrofinanceplus/artisan queue:work redis --queue=rapports --sleep=3 --tries=3 --timeout=120 --max-jobs=500
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/agrofinance-worker.log
stopwaitsecs=120
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start agrofinance-worker:*
```

---

## 9. Scheduler Laravel (cron)

```bash
crontab -e -u www-data
```

```cron
* * * * * php /var/www/agrofinanceplus/artisan schedule:run >> /dev/null 2>&1
```

Le scheduler exécute automatiquement :
- `backup:run` (sauvegarde quotidienne)
- `backup:clean` (nettoyage selon la stratégie de rétention)
- `backup:monitor` (alerte si backup trop ancienne)

---

## 10. Déploiement (zéro-downtime)

```bash
# Sur le serveur, après git pull
cd /var/www/agrofinanceplus
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
php artisan backup:run
supervisorctl restart agrofinance-worker:*
```

---

## 11. Sentry APM (#10)

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish --dsn=<TON_DSN_SENTRY>
```

Vérification :
```bash
php artisan sentry:test
```

---

## 12. Vérifications post-déploiement

```bash
curl https://votre-domaine.bj/health
# → {"database":"ok","cache":"ok","storage":"ok"}

php artisan sentry:test
# → Exception envoyée à Sentry

php artisan backup:run
# → Backup créée dans storage/app/

supervisorctl status
# → agrofinance-worker:* RUNNING
```
