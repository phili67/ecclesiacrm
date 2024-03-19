## Server

* A Linux server
* A server LAMP : Linux Apache Mysql or Php is required
* With NGinx one may have issue ( not tested yet)
* php 8.0 is required at the minimum
* A Mysql 5.7 database
* MariaDB also works

## Mods to activate on Apache:
* PCRE and UTF-8 are required
* Multibyte Encoding
* PHP Phar
* PHP Session
* PHP XML
* PHP EXIF
* PHP imagick
* PHP iconv
* OpenSSL
* Mod Rewrite
* GD Library to handle the pictures
* FileInfo Extension for the files' infos
* cURL
* locale gettext
* Include/Config the configuration file is accessible in writing
* the picture file is accessible in writing also

**Note** for imagick
```
sudo sed -i_bak 's/rights="none" pattern="PDF"/rights="read | write" pattern="PDF"/' /etc/ImageMagick-6/policy.xml
```

## for Apache vhost

```
    <IfModule mod_env.c>
        ## Tell PHP that the mod_rewrite module is ENABLED.
        SetEnv HTTP_MOD_REWRITE On
    </IfModule>
```

## Memory
* Max file upload size  32M
* Max POST size  32M
* PHP Memory Limit  128M

## Mode evasive and security
* Apache mod-evasive may make the CRM ineffective
* Apache mod-security has to be be turned off


## Optional : WebDAV
* WebDAV/CalDav and CardDav are assets for the connection to work like NextCloud ...
* The file "data" has to be fix to 755
* The file "private" has to be fix to 755 as well as userid
* The file "public" has to be fix to 755 as well as userid

The last point is useless for the operation but a true asset for the app
