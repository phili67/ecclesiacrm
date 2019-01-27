## Serveur 

* Un serveur sous Linux (Une Ubuntu 16.04LTS par exemple)
* Un php 7.0 au minimum est requis
* Une base de données sous Mysql 5.7 ou plus

## Ces mods sur Apache doivent être activés
* PCRE et UTF-8 sont requis
* Multibyte Encoding
* PHP Phar
* PHP Session
* PHP XML
* PHP EXIF
* PHP iconv
* OpenSSL
* Mod Rewrite
* GD Library for image manipulation
* FileInfo Extension for image manipulation
* cURL
* locale gettext
* Include/Config file is writeable
* Images directory is writeable

## Mémoire
* Max file upload size  32M
* Max POST size  32M
* PHP Memory Limit  128M

## Mode evasive and security
* le module Apache mod-evasive peut restreindre fortement le CRM voir le rendre inopérant.
* le module Apache mod-security doit être fixé le plus légèrement possible.


## Optionnel : WebDAV
* WebDAV est un plus pour que la connexion puisse fonctionner comme NextCloud
* le dossier data doit être réglé à 777
* le dossier private doit être réglé à 777 ainsi que userid

Ce dernier point est non utile pour le fonctionnement, mais est le réel plus de l'application
