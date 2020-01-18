#!/bin/bash

./locale/update-locale.sh

grunt genLocaleJSFiles

# now we have to change all the defineLocale to updateLocale
sed -i 's/defineLocale/updateLocale/g' src/locale/js/*.js
