#!/bin/bash

# Dans le cas de php 7.2
#cp -rf propel/vendor/* src/vendor/propel
#
# npm install node-sass
# npm install i18next-extract-gettext -g
# npm install i18next-conv -g
# npm install grunt -g

# npm install --global strip-json-comments-cli

# npm install grunt --save-dev

# rebuild the nodes-sass base
npm rebuild node-sass

#  it's time to install all the node js files
npm install --unsafe-perm --legacy-peer-deps

cd src/

sudo composer i

cd ..

sudo cp BuildConfig.json.example BuildConfig.json

# to run the installation
npm run install
npm run postinstall
npm run orm-gen

npm run clean-datatables-lang-files

#npm run composer-update

chown -R www-data:www-data src

cp -Rf ckeditorExtraPlugins/html5video src/skin/external/ckeditor/plugins

# install composer ...
cd src
composer i

