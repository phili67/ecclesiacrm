#!/bin/bash

# Attention pour installer tout ce qu'il faut :
# npm install grunt -g
# npm install i18next-conv -g
# npm install i18next-extract-gettext -g
 
./locale/update-locale.sh

grunt genLocaleJSFiles

# now we have to change all the defineLocale to updateLocale
sed -i 's/defineLocale/updateLocale/g' src/locale/js/*.js
