#!/bin/bash

# copyright 2018 Philippe Logel EcclesiaCRM

cd src
# Extract PHP Terms
find . -iname '*.php' -not -path "./Plugins/*" | sort | grep -v ./vendor | xargs xgettext --from-code=UTF-8 -o ../locale/messages.pot -L PHP

# Extract JS Terms
i18next-extract-gettext --files=skin/js/*.js --output=../locale/js-strings1.pot
i18next-extract-gettext --files=skin/js/calendar/*.js --output=../locale/js-strings2.pot
i18next-extract-gettext --files=skin/js/ckeditor/*.js --output=../locale/js-strings3.pot
i18next-extract-gettext --files=skin/js/ckeditor/configs/*.js --output=../locale/js-strings33.pot
i18next-extract-gettext --files=skin/js/email/*.js --output=../locale/js-strings4.pot
i18next-extract-gettext --files=skin/js/event/*.js --output=../locale/js-strings5.pot
i18next-extract-gettext --files=skin/js/finance/*.js --output=../locale/js-strings6.pot
i18next-extract-gettext --files=skin/js/gdpr/*.js --output=../locale/js-strings7.pot
i18next-extract-gettext --files=skin/js/group/*.js --output=../locale/js-strings8.pot
i18next-extract-gettext --files=skin/js/people/*.js --output=../locale/js-strings9.pot
i18next-extract-gettext --files=skin/js/sidebar/*.js --output=../locale/js-strings10.pot
i18next-extract-gettext --files=skin/js/sundayschool/*.js --output=../locale/js-strings11.pot
i18next-extract-gettext --files=skin/js/system/*.js --output=../locale/js-strings12.pot
i18next-extract-gettext --files=skin/js/user/*.js --output=../locale/js-strings13.pot

i18next-extract-gettext --files=skin/js/email/MailChimp/*.js --output=../locale/js-strings14.pot
i18next-extract-gettext --files=skin/js/backup/*.js --output=../locale/js-strings16.pot
i18next-extract-gettext --files=skin/js/Search/*.js --output=../locale/js-strings17.pot
i18next-extract-gettext --files=skin/js/pastoralcare/*.js --output=../locale/js-strings18.pot
i18next-extract-gettext --files=skin/js/fundraiser/*.js --output=../locale/js-strings19.pot
i18next-extract-gettext --files=skin/js/groupcommon/*.js --output=../locale/js-strings20.pot

i18next-extract-gettext --files=skin/js/jquery-photo-uploader/*.js --output=../locale/js-strings21.pot


msgcat ../locale/js-strings1.pot ../locale/js-strings2.pot ../locale/js-strings3.pot  ../locale/js-strings33.pot ../locale/js-strings4.pot ../locale/js-strings5.pot ../locale/js-strings6.pot ../locale/js-strings7.pot ../locale/js-strings8.pot ../locale/js-strings9.pot ../locale/js-strings10.pot ../locale/js-strings11.pot ../locale/js-strings12.pot ../locale/js-strings13.pot ../locale/js-strings14.pot ../locale/js-strings16.pot ../locale/js-strings17.pot ../locale/js-strings18.pot ../locale/js-strings19.pot ../locale/js-strings20.pot ../locale/js-strings21.pot -o ../locale/js-strings.pot

rm ../locale/js-strings1.pot ../locale/js-strings2.pot ../locale/js-strings3.pot ../locale/js-strings33.pot  ../locale/js-strings4.pot ../locale/js-strings5.pot ../locale/js-strings6.pot ../locale/js-strings7.pot ../locale/js-strings8.pot ../locale/js-strings9.pot ../locale/js-strings10.pot ../locale/js-strings11.pot ../locale/js-strings12.pot ../locale/js-strings13.pot ../locale/js-strings14.pot ../locale/js-strings16.pot ../locale/js-strings17.pot ../locale/js-strings18.pot ../locale/js-strings19.pot ../locale/js-strings20.pot ../locale/js-strings21.pot

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
./
   lang=$(echo $(_jq '.locale'))

   echo "Merge '${lang}'"

   if [ "${lang}" == "en_US" ] || [ "${lang}" == "en_GB" ] || [ "${lang}" == "en_CA" ] || [ "${lang}" == "en_AU" ] ; then
     msgfmt -o "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.mo" "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.po"
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

   if [ -f "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.po~" ]; then
        rm "../src/locale/textdomain/${lang}/LC_MESSAGES/messages.po~"
   fi

   if [ -f "JSONKeys_JS/${lang}/js-strings.po" ]; then
     echo "traduction exist for ${lang}"
     # To do
     cp js-strings.pot js-strings.po

     # backup file
     cp JSONKeys_JS/${lang}/js-strings.po "backup/js-strings_${lang}_"`date '+%Y-%m-%d_%H:%M:%S'`".po"

     # We start the merge
     msgmerge -U JSONKeys_JS/${lang}/js-strings.po js-strings.po
     msgmerge -U JSONKeys_JS/${lang}/js_extra.po js_extra.pot

     i18next-conv -l fr -s "JSONKeys_JS/${lang}/js-strings.po" -t "JSONKeys_JS/${lang}.json"
     i18next-conv -l fr -s "JSONKeys_JS/${lang}/js_extra.po" -t "JSONKeys_JS/${lang}/js_extra.json"

     # now we add the extra terms
     mergeJson=$(jq -s '.[0] * .[1]' "JSONKeys_JS/${lang}.json" "JSONKeys_JS/${lang}/js_extra.json")

     echo $mergeJson > "JSONKeys_JS/${lang}.json"


     # cleanup
     rm "JSONKeys_JS/${lang}/js_extra.json"

     if [ -f "JSONKeys_JS/${lang}/js-strings.po~" ]; then
        rm "JSONKeys_JS/${lang}/js-strings.po~"
     fi

     if [ -f "JSONKeys_JS/${lang}/js_extra.po~" ]; then
        rm "JSONKeys_JS/${lang}/js_extra.po~"
     fi
   fi
   

   # now we update the plugins
   for d in ../src/Plugins/*/ ; do
       plugin="$d"

       pluginName="$(basename $plugin)"

       # messages.po for plugin
       msgfmt -o "../src/Plugins/${pluginName}/locale/textdomain/${lang}/LC_MESSAGES/messages-${pluginName}.mo" "../src/Plugins/${pluginName}/locale/textdomain/${lang}/LC_MESSAGES/messages-${pluginName}.po"
       
       # js files for plugin
       i18next-conv -l fr -s "JSONKeys_JS_Plugins/${pluginName}/${lang}/js-strings.po" -t "JSONKeys_JS_Plugins/${pluginName}/${lang}.json"
       
       echo "try {window.CRM.${pluginName}_i18keys = " > "../src/Plugins/${pluginName}/locale/js/${lang}.js"
       cat "JSONKeys_JS_Plugins/${pluginName}/${lang}.json" >> "../src/Plugins/${pluginName}/locale/js/${lang}.js"
       echo ";} catch(e) {};" >> "../src/Plugins/${pluginName}/locale/js/${lang}.js"
       
       # we delete the new line
       tr -d '\r\n' < "../src/Plugins/${pluginName}/locale/js/${lang}.js" > "../src/Plugins/${pluginName}/locale/js/${lang}1.js"
       mv -f  "../src/Plugins/${pluginName}/locale/js/${lang}1.js" "../src/Plugins/${pluginName}/locale/js/${lang}.js"
   done
done

# and the next languages

# merge PHP & DB & JS Terms
#msgcat messages.po js-strings.po -o messages.po

# Cleanup
#rm js-strings.*
rm db-strings/*
#rm messages.po
