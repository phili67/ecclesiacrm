#!/usr/bin/env bash
sudo yum update -y
sudo yum install unzip -y

sudo yum install httpd -y
chkconfig --level 234 httpd on

rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
sudo yum install php70w php70w-pear php70w-mcrypt php70w-mysql php70w-zip php70w-phar php70w-gd php70w-mbstring -y
service httpd start
php --version

wget http://repo.mysql.com/mysql-community-release-el7-5.noarch.rpm
sudo rpm -ivh mysql-community-release-el7-5.noarch.rpm
sudo yum install mysql-server -y
chkconfig --level 234 mysqld on
service mysqld start

cd /var/www/
rm -rf html
sudo wget https://github.com/phili67/ecclesiacrm/releases/download/5.4.3/EcclesiaCRM-5.4.3.zip
sudo unzip EcclesiaCRM-5.4.3.zip
mv ecclesiacrm/ html
