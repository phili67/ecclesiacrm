#!/bin/bash

# we install the special config file for docker env
#cp docker/Config.php src/Include/

# first : we shutdown the container
docker-compose down

# Last part
cd ..
cp BuildConfig.json.example BuildConfig.json
cd docker

# now it's time to start it
docker-compose up -d

cd ../locale/

echo "<?php
  \$db_servername = 'database';
  \$db_username = 'root';
  \$db_password = 'tiger';
  \$db_name = 'docker';" > connection.php

cd ../docker

# and to install npm to work
docker-compose exec --index=1 webserver  sh -c "sed -i_bak 's/rights=\"none\" pattern=\"PDF\"/rights=\"read | write\" pattern=\"PDF\"/' /etc/ImageMagick-6/policy.xml && /etc/init.d/apache2 reload"
docker-compose exec --index=1 webserver  sh -c "/var/www/html/install.sh"

# now we log in
docker-compose exec webserver bash
