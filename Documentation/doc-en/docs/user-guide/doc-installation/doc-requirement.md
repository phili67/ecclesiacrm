## Server

* A Linux server
* A server LAMP : Linux Apache Mysql or Php is required
* With NGinx one may have issue ( not tested yet)
* php 8.1 is required at the minimum
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

## PHP server type

* You must install PHP-FPM
* and set `pm.max_children` to 100 (`/etc/php/8.x/fpm/pool.d/www.conf`). This setting is essential to prevent the CRM from hanging.

## Memory
To do this, open `/etc/php/8.x/fpm/php.ini`

* Max file upload size  ≥ 512M : `upload_max_filesize=1000M`
* Max POST size  ≥ 512M : `post_max_size=1000M`
* PHP Memory Limit  ≥ 128M : `memory_limit=2048M`

## opcache
To do this, open `/etc/php/8.x/fpm/php.ini`
```
[Implementation of opcache]
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
* Apache mod-evasive may make the CRM ineffective
* Apache mod-security has to be be turned off

## Optional : WebDAV
* WebDAV/CalDav and CardDav are assets for the connection to work like NextCloud ...
* The file "data" has to be fix to `755`
* The file "private" has to be fix to `755` as well as userid
* The file "public" has to be fix to `755` as well as userid

The last point is useless for the operation but a true asset for the app
