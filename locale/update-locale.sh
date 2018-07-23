#!/bin/bash

cd src
# Extract PHP Terms
find . -iname '*.php' | sort | grep -v ./vendor | xargs xgettext --from-code=UTF-8 -o ../locale/messages.pot -L PHP

# Extract JS Terms
i18next-extract-gettext --files=skin/js/*.js --output=../locale/js-strings.pot

cd ../locale

# we create the backup folder
if [ ! -d "backup" ]; then
  mkdir "backup"
fi

# Extract DB Terms
php extract-db-locale-terms.php
cd db-strings
find . -iname "*.php" | sort | xargs xgettext --join-existing --from-code=UTF-8 -o ../messages.pot

# adding extra msg
cat .."/extra_messages.po" >> .."/messages.pot"

cd ..

for row in $(cat "../src/locale/locales.json" | jq -r '.[] | @base64'); do
   _jq() {
     echo ${row} | base64 --decode | jq -r ${1}
   }
   
   lang=$(echo $(_jq '.locale'))
   
   echo "Merge '${lang}'"

   if [ "${lang}" == "en_US" ] || [ "${lang}" == "en_GB" ] || [ "${lang}" == "en_CA" ] || [ "${lang}" == "en_AU" ] ; then
     continue
   fi

   
   if [ ! -d "JSONKeys_JS/${lang}" ]; then
     mkdir "JSONKeys_JS/${lang}"
   fi
   
   #cp messages.pot messages.po
   
   # backup file
   cp .."/src/locale/textdomain/${lang}/LC_MESSAGES/messages.po" "backup/message_${lang}_"`date '+%Y-%m-%d_%H:%M:%S'`".po"
   
   # We start the merge 
   msgmerge -U .."/src/locale/textdomain/${lang}/LC_MESSAGES/messages.po"  messages.pot

   msgfmt -o "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.mo" "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.po"
   
   if [ -f "JSONKeys_JS/${lang}/js-strings.po" ]; then
     echo "traduction exist for ${lang}"
     # To do
     cp js-strings.pot js-strings.po
     
     # backup file
     cp JSONKeys_JS/${lang}/js-strings.po "backup/js-strings_${lang}_"`date '+%Y-%m-%d_%H:%M:%S'`".po"
     
     # We start the merge 
     msgmerge -U JSONKeys_JS/${lang}/js-strings.po js-strings.po
     
     i18next-conv -l fr -s "JSONKeys_JS/${lang}/js-strings.po" -t "JSONKeys_JS/${lang}.json"
     
     # now we add the extra terms
     mergeJson=$(jq -s '.[0] * .[1]' "JSONKeys_JS/${lang}.json" "JSONKeys_JS/${lang}/${lang}_extra.json")
     
     echo $mergeJson > "JSONKeys_JS/${lang}.json"
   fi 

done

# and the next languages

# merge PHP & DB & JS Terms
#msgcat messages.po js-strings.po -o messages.po

# Cleanup
#rm js-strings.*
rm db-strings/*
#rm messages.po
