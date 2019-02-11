#!/usr/bin/env bash

# Philippe Logel 2019

# setup the LAMP server with everything
sudo apt install tasksel
sudo tasksel install lamp-server

sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload

# set the language
locale -a
locale-gen fr_FR.*
locale-gen fr_BE.*
locale-gen fr_CH.*
locale-gen fr_CA.*

# export the french language for example
export LANG=fr_FR.UTF-8
export LC_ALL=fr_FR.UTF-8
export LANGUAGE=fr_FR.UTF-8

# set the default php.ini settings
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/7.2/apache2/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 728M/' /etc/php/7.2/apache2/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 3000/' /etc/php/7.2/apache2/php.ini

service apache2 reload

# install the last zip file
sudo apt install unzip

cd /var/www/

sudo wget https://github.com/phili67/ecclesiacrm/releases/download/5.4.3/EcclesiaCRM-5.4.3.zip
sudo unzip EcclesiaCRM-5.4.3.zip

sudo rm -rf html
sudo mv ecclesiacrm html
sudo chown -R www-data:www-data html
