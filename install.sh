#!/bin/bash

# Dans le cas de php 7.2
cp -rf propel/vendor/* src/vendor/propel


npm install --unsafe-perm

npm run install
npm run postinstall
npm run orm-gen

#npm run composer-update

chown -R www-data:www-data src


