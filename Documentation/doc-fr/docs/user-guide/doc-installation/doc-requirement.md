## Serveur

* Un serveur sous Linux (Une Ubuntu 16.04LTS par exemple)
* Un serveur LAMP : Linux Apache Mysql et Php est requis.
* Sous NGinx vous pourriez rencontrer des difficultés (non testé).
* Un php 8.1 au minimum est requis
* Une base de données sous Mysql 5.7 ou plus
* MariaDB fonctionne sans souci

## Ces mods sur Apache doivent être activés
* PCRE et UTF-8 sont requis
* Multibyte Encoding
* PHP Phar
* PHP Session
* PHP XML
* PHP EXIF
* PHP imagick
* PHP iconv
* OpenSSL
* Mod Rewrite
* GD Library pour la manipulation sur les images.
* FileInfo Extension pour les informations sur les fichiers.
* cURL
* locale gettext
* Include/Config le fichier de configuration est accessible en écriture
* Images/ le dossier est accessible en écriture également

**Note** pour imagick
```
sudo sed -i_bak 's/rights="none" pattern="PDF"/rights="read | write" pattern="PDF"/' /etc/ImageMagick-6/policy.xml
```

## Pour le vhost apache

```
    <IfModule mod_env.c>
        ## Tell PHP that the mod_rewrite module is ENABLED.
        SetEnv HTTP_MOD_REWRITE On
    </IfModule>
```

## type de serveur php
* il faut installer php-fpm
* et régler `pm.max_children` à 100 (`/etc/php/8.x/fpm/pool.d/www.conf`), ce réglage est impératif, il permet de ne pas avoir un CRM qui a tendace à se bloquer.

## Mémoire

Pour cela il faut aller dans `/etc/php/8.x/fpm/php.ini`

* Max file upload size  ≥ 512M : `upload_max_filesize=1000M`
* Max POST size  ≥ 512M : `post_max_size=1000M`
* PHP Memory Limit  ≥ 128M : `memory_limit=2048M`

## opcache
Pour cela aller au bout de `/etc/php/8.x/fpm/php.ini`
```
[mise en place du opcache]
opcache.enable=1
opcache.enable_cli=1
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=10000
opcache.memory_consumption=2048
opcache.save_comments=1
opcache.revalidate_freq=60
opcache.validate_timestamps = 0
opcache.jit = 1255
opcache.jit_buffer_size = 128M
```

## Mode evasive and security
* le module Apache mod-evasive peut restreindre fortement le CRM voir le rendre inopérant.
* le module Apache mod-security doit être fixé le plus légèrement possible ou être désactivé.

## Optionnel : WebDAV
* WebDAV/CalDav et CardDav sont des plus pour que la connexion puisse fonctionner comme NextCloud ...
* le dossier data doit être réglé à 755
* le dossier private doit être réglé à 755 ainsi que userid
* le dossier public doit être réglé à 755 ainsi que userid

Ce dernier point est non utile pour le fonctionnement, mais est un réel plus de l'application
