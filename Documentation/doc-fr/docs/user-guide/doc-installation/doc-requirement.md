## Serveur

* Un serveur sous Linux (Une Ubuntu 16.04LTS par exemple)
* Un serveur LAMP : Linux Apache Mysql et Php est requis.
* Sous NGinx vous pourriez rencontrer des difficultés (non testé).
* Un php 8.0 au minimum est requis
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

## Mémoire
* Max file upload size  32M
* Max POST size  32M
* PHP Memory Limit  128M

## Mode evasive and security
* le module Apache mod-evasive peut restreindre fortement le CRM voir le rendre inopérant.
* le module Apache mod-security doit être fixé le plus légèrement possible ou être désactivé.


## Optionnel : WebDAV
* WebDAV/CalDav et CardDav sont des plus pour que la connexion puisse fonctionner comme NextCloud ...
* le dossier data doit être réglé à 755
* le dossier private doit être réglé à 755 ainsi que userid
* le dossier public doit être réglé à 755 ainsi que userid

Ce dernier point est non utile pour le fonctionnement, mais est un réel plus de l'application
