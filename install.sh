#!/bin/bash

npm install --unsafe-perm

npm run install
npm run postinstall
npm run orm-gen

#npm run composer-update


