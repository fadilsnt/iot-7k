# Panduan Deployment - IoT TKHS

Dokumentasi lengkap untuk deployment aplikasi IoT TKHS ke production environment.

## 📋 Daftar Isi

- [Persiapan Server](#persiapan-server)
- [Deployment ke Shared Hosting](#deployment-ke-shared-hosting)
- [Deployment ke VPS](#deployment-ke-vps)
- [Configuration](#configuration)
- [Security Checklist](#security-checklist)
- [Maintenance](#maintenance)

## 🖥️ Persiapan Server

### Minimum Requirements

- **PHP**: >= 8.2
- **MySQL**: >= 8.0
- **Web Server**: Apache 2.4+ atau Nginx 1.18+
- **Memory**: 512 MB minimum, 1 GB recommended
- **Storage**: 500 MB minimum
- **SSL Certificate**: Recommended untuk production

### PHP Extensions Required

```bash
php -m | grep -E 'openssl|pdo|mbstring|tokenizer|xml|ctype|json|bcmath'
```

Pastikan semua extension berikut terinstall:
- OpenSSL
- PDO
- PDO_MySQL
- Mbstring
- Tokenizer
- XML
- Ctype
- JSON
- BCMath
- Fileinfo

## 🌐 Deployment ke Shared Hosting

### 1. Persiapan File

```bash
# Build assets untuk production
npm run build

# Optimize autoload
composer install --optimize-autoloader --no-dev

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Upload ke Server

**Via FTP/SFTP:**

1. Upload semua file KECUALI:
   - `node_modules/`
   - `.env` (akan dibuat di server)
   - `storage/` (folder akan tetap ada, tapi isi nya jangan di-upload)

2. Struktur folder di server:
   ```
   public_html/
   ├── iot-tkhs/          (folder aplikasi)
   │   ├── app/
   │   ├── bootstrap/
   │   ├── config/
   │   ├── database/
   │   ├── public/        (ini adalah root web)
   │   ├── resources/
   │   ├── routes/
   │   ├── storage/
   │   └── vendor/
   ```

### 3. Setup Database

**Via phpMyAdmin atau MySQL CLI:**

```sql
CREATE DATABASE iot_tkhs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Environment Configuration

Buat file `.env` di root folder aplikasi:

```env
APP_NAME="IoT TKHS"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=iot_tkhs
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database

LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations

```bash
php artisan migrate --force
```

### 7. Set Permissions

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Configure Web Server

**Apache (.htaccess di root):**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Atau pindahkan public ke root:**

```bash
# Pindahkan isi folder public ke root
mv public/* ./
mv public/.htaccess ./

# Update index.php
# Ubah: require __DIR__.'/../vendor/autoload.php';
# Menjadi: require __DIR__.'/vendor/autoload.php';

# Ubah: $app = require_once __DIR__.'/../bootstrap/app.php';
# Menjadi: $app = require_once __DIR__.'/bootstrap/app.php';
```

## 🖥️ Deployment ke VPS

### 1. Install Dependencies

**Ubuntu/Debian:**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-curl php8.2-zip

# Install MySQL
sudo apt install -y mysql-server

# Install Nginx
sudo apt install -y nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Setup MySQL Database

```bash
sudo mysql

CREATE DATABASE iot_tkhs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'iot_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON iot_tkhs.* TO 'iot_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Clone & Setup Application

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-repo/iot-tkhs.git
cd iot-tkhs

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Setup permissions
sudo chown -R www-data:www-data /var/www/iot-tkhs
sudo chmod -R 775 storage bootstrap/cache

# Setup environment
cp .env.example .env
nano .env  # Edit configuration

# Generate key & migrate
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 4. Configure Nginx

**Create config file:**

```bash
sudo nano /etc/nginx/sites-available/iot-tkhs
```

**Add configuration:**

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/iot-tkhs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable site:**

```bash
sudo ln -s /etc/nginx/sites-available/iot-tkhs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Setup SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo systemctl enable certbot.timer
```

### 6. Setup Supervisor (For Queue Workers)

```bash
# Install Supervisor
sudo apt install -y supervisor

# Create config
sudo nano /etc/supervisor/conf.d/iot-tkhs-worker.conf
```

**Add configuration:**

```ini
[program:iot-tkhs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/iot-tkhs/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/iot-tkhs/storage/logs/worker.log
stopwaitsecs=3600
```

**Start worker:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start iot-tkhs-worker:*
```

## ⚙️ Configuration

### Optimize Performance

```bash
# Enable OPcache (php.ini)
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2

# Laravel optimization
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Setup Cron Jobs

```bash
crontab -e
```

Add:

```cron
* * * * * cd /var/www/iot-tkhs && php artisan schedule:run >> /dev/null 2>&1
```

### Backup Strategy

**Daily database backup:**

```bash
#!/bin/bash
# /var/www/iot-tkhs/backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/iot-tkhs"
DB_NAME="iot_tkhs"
DB_USER="iot_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/iot-tkhs/storage

# Remove old backups (older than 30 days)
find $BACKUP_DIR -type f -mtime +30 -delete
```

**Add to cron:**

```bash
0 2 * * * /var/www/iot-tkhs/backup.sh
```

## 🔒 Security Checklist

### Pre-Deployment

- [ ] Set `APP_DEBUG=false` in production
- [ ] Generate strong `APP_KEY`
- [ ] Use strong database passwords
- [ ] Remove `.env.example` from production
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure firewall (UFW/iptables)
- [ ] Setup fail2ban for SSH protection

### Post-Deployment

```bash
# Remove sensitive files
rm -f .env.example
rm -f README.md
rm -f package.json
rm -f composer.json

# Secure .env
chmod 600 .env

# Disable directory listing
# Add to .htaccess or nginx config
Options -Indexes
```

### Laravel Security

```env
# .env production settings
APP_DEBUG=false
APP_ENV=production

# Session security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Database
DB_HOST=127.0.0.1  # Use IP instead of 'localhost'
```

## 🔧 Maintenance

### Update Application

```bash
# Backup first!
php artisan down

# Pull updates
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear & recache
php artisan optimize:clear
php artisan optimize

# Restart services
php artisan up
sudo supervisorctl restart all
```

### Monitor Logs

```bash
# Application logs
tail -f storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### Performance Monitoring

```bash
# Check disk usage
df -h

# Check memory usage
free -m

# Check process list
top

# MySQL performance
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G"
```

## 🆘 Troubleshooting

### Common Issues

**500 Internal Server Error:**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear cache
php artisan optimize:clear
```

**Database Connection Error:**
```bash
# Test connection
mysql -u iot_user -p -h localhost iot_tkhs

# Check .env credentials
cat .env | grep DB_

# Clear config cache
php artisan config:clear
```

**Assets Not Loading:**
```bash
# Rebuild assets
npm run build

# Clear view cache
php artisan view:clear

# Check public path in .env
APP_URL=https://yourdomain.com
```

## 📞 Support

Jika mengalami kendala saat deployment:

1. Cek log aplikasi: `storage/logs/laravel.log`
2. Cek log server (Nginx/Apache)
3. Verifikasi konfigurasi `.env`
4. Pastikan semua service running
5. Contact: info@tkhs.co.id

---

**© 2025 PT. Tuju Kuda Hitam Sakti**
