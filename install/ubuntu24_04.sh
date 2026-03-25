#!/usr/bin/env bash

# Release version (use GitHub release tag without leading 'v')
RELEASE="8.0.0-GM.3"
RELEASEZIP="8.0.0"

# Configuration variables (interactive prompts)
read -p "Domaine (ex: example.com) [test.com]: " DOMAIN
DOMAIN=${DOMAIN:-test.com}
read -p "Email admin (Let's Encrypt) [admin@test.com]: " ADMIN_EMAIL
ADMIN_EMAIL=${ADMIN_EMAIL:-admin@test.com}
read -p "Nom de la base de données [ecrm]: " DB_NAME
DB_NAME=${DB_NAME:-ecrm}
read -p "Nom d'utilisateur DB [admin]: " DB_USER
DB_USER=${DB_USER:-admin}
read -s -p "Mot de passe DB [eccrm2024]: " DB_PASSWORD
echo
DB_PASSWORD=${DB_PASSWORD:-eccrm2024}

read -p "Locale (ex: fr_FR.UTF-8) [fr_FR.UTF-8]: " LOCALE
LOCALE=${LOCALE:-fr_FR.UTF-8}
LANGCODE=${LOCALE%%_*}

# setup the LAMP server with everything
sudo apt update
sudo apt install apache2 mariadb-server  mariadb-client php8.3 libapache2-mod-php8.3 php8.3-fpm

# install language packs for selected locale
sudo apt install -y language-pack-$LANGCODE language-pack-$LANGCODE-base

sudo apt install --no-install-recommends php-mysql php-curl php-gd php-msgpack php-memcached php-intl php-sqlite3 php-gmp php-mbstring php-redis php-xml php-zip php-opcache gettext imagemagick php-imagick

# set the default php.ini settings
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 728M/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 3000/' /etc/php/8.3/apache2/php.ini

# configure opcache for apache
sudo sed -i 's/;opcache.enable=1/opcache.enable=1/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=256/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=10000/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=10/' /etc/php/8.3/apache2/php.ini

sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 728M/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 3000/' /etc/php/8.3/fpm/php.ini

# configure opcache for fpm
sudo sed -i 's/;opcache.enable=1/opcache.enable=1/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=256/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=10000/' /etc/php/8.3/fpm/php.ini
sudo sed -i 's/;opcache.revalidate_freq=2/opcache.revalidate_freq=10/' /etc/php/8.3/fpm/php.ini

sudo systemctl reload apache2

sudo systemctl enable php8.3-fpm.service

sudo a2enmod proxy_fcgi setenvif
sudo a2enconf php8.3-fpm

# install and configure Let's Encrypt (Certbot)
sudo apt install certbot python3-certbot-apache

# enable mod_ssl and mod_rewrite for Apache
sudo a2enmod ssl
sudo a2enmod rewrite


# create Apache vhost for HTTP (always)
VHOST_Default_CONF="/etc/apache2/sites-available/000-default.conf"
sudo tee "$VHOST_Default_CONF" > /dev/null <<EOF    
<VirtualHost *:80>
    # The ServerName directive sets the request scheme, hostname and port that
    # the server uses to identify itself. This is used when creating
    # redirection URLs. In the context of virtual hosts, the ServerName
    # specifies what hostname must appear in the request's Host: header to
    # match this virtual host. For the default virtual host (this file) this
    # value is not decisive as it is used as a last resort host regardless.
    # However, you must set it for any further virtual host explicitly.
    #ServerName www.example.com

    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html

    # Available loglevels: trace8, ..., trace1, debug, info, notice, warn,
    # error, crit, alert, emerg.
    # It is also possible to configure the loglevel for particular
    # modules, e.g.
    #LogLevel info ssl:warn

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined

    # For most configuration files from conf-available/, which are
    # enabled or disabled at a global level, it is possible to
    # include a line for only one particular virtual host. For example the
    # following line enables the CGI configuration for this host only
    # after it has been globally disabled with "a2disconf".
    #Include conf-available/serve-cgi-bin.conf

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    RewriteEngine on
EOF


read -p "Activate HTTPS and Let's Encrypt ? (y/N) [N]: " ENABLE_HTTPS
ENABLE_HTTPS=${ENABLE_HTTPS:-N}
ENABLE_HTTPS=$(echo "$ENABLE_HTTPS" | tr '[:upper:]' '[:lower:]')

# create Apache vhost for HTTP (always)
VHOST_CONF="/etc/apache2/sites-available/${DOMAIN}.conf"
sudo tee "$VHOST_CONF" > /dev/null <<EOF    
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    RewriteEngine on
EOF

if [ "$ENABLE_HTTPS" = "y" ]; then
    # HTTPS section in vhost file
    sudo tee -a "$VHOST_CONF" > /dev/null <<EOF
    RewriteCond %{HTTPS} !=on
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>

<IfModule mod_ssl.c>
<VirtualHost *:443>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot /var/www/html

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/${DOMAIN}/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/${DOMAIN}/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    RewriteEngine on
</VirtualHost>
</IfModule>
EOF

    # configure certbot for ssl
    sudo certbot --apache -d "$DOMAIN" --non-interactive --agree-tos -m "$ADMIN_EMAIL"
else
    sudo tee -a "$VHOST_CONF" > /dev/null <<EOF
    RewriteCond %{HTTPS} !=on
    RewriteRule ^/?(.*) http://%{SERVER_NAME}/$1 [R=302,L]
</VirtualHost>
EOF
fi

sudo a2ensite "${DOMAIN}.conf"
sudo systemctl reload apache2

# secure mariadb
sudo mysql_secure_installation

sudo systemctl enable mariadb.service

# create default database and user
sudo mysql -e "CREATE DATABASE $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# security
sudo apt install ufw

sudo ufw allow http
sudo ufw allow https
sudo ufw reload

sudo ufw enable

# set the system locale
sudo locale-gen "$LOCALE"
sudo update-locale LANG="$LOCALE"

# install the last zip file
sudo apt install unzip

cd /var/www/

sudo wget "https://github.com/phili67/ecclesiacrm/releases/download/v${RELEASE}/EcclesiaCRM-${RELEASEZIP}.zip"
sudo unzip "EcclesiaCRM-${RELEASEZIP}.zip"

sudo rm -rf html
sudo mv ecclesiacrm html
sudo chown -R www-data:www-data html
