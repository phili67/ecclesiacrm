#!/usr/bin/env bash

# setup the LAMP server with everything
sudo apt install tasksel
sudo tasksel install lamp-server

# set the default php.ini settings
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/7.2/apache2/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 728M/' /etc/php/7.2/apache2/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 3000/' /etc/php/7.2/apache2/php.ini

service apache2 reload

# secure mysql
sudo mysql_secure_installation

#security
sudo apt install ufw

sudo ufw allow http
sudo ufw allow https
sudo ufw reload

sudo ufw enable

# set the language
sudo dpkg-reconfigure locales

# install the last zip file
sudo apt install unzip

cd /var/www/

sudo wget https://github.com/phili67/ecclesiacrm/releases/download/5.4.3/EcclesiaCRM-5.4.3.zip
sudo unzip EcclesiaCRM-5.4.3.zip

sudo rm -rf html
sudo mv ecclesiacrm html
sudo chown -R www-data:www-data html
