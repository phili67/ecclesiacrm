#!/bin/bash

if [ ! -f "./BuildConfig.json" ]; then
  cp BuildConfig.json.example BuildConfig.json
  npm install -g github:phili67/i18next-extract-gettext
  npm install i18next-conv -g
  npm install --global strip-json-comments-cli
  npm install grunt --save-dev
fi

npm rebuild node-sass

#  it's time to install all the node js files
npm install --unsafe-perm --legacy-peer-deps --save .

# to run the installation
npm run install

cp node_modules/bootstrap-datepicker/dist/locales/bootstrap-datepicker.no.min.js node_modules/bootstrap-datepicker/dist/locales/bootstrap-datepicker.nb.min.js

npm run postinstall
npm run orm-gen

npm run clean-datatables-lang-files

#npm run composer-update

chown -R www-data:www-data src

# install composer ...
cd src
composer i

