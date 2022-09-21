#!/bin/bash

# create the first times the message-${pluginName}.po for each plugins for each locale : php files
# to use : createPluginArch.sh NameOfPlugin

#for d in src/Plugins/*/ ; do
#    plugin="$d"

#    echo "${plugin}"

#    pluginName="$(basename $plugin)"


    pluginName=$1

    # we work first with locale files
    mkdir "src/Plugins/${pluginName}"

    touch "src/Plugins/${pluginName}/config.json"

    echo '{
              "Name": "'${pluginName}'",
              "copyrights": "",
              "version": "1.0",
              "Description": "",
              "infos": "Name of your team",
              "url_infos": "https://team_url",
              "url_docs": "https://doc_url",
              "Settings_url": "meeting/settings",
              "Details": "https://url;iframe=true&amp;width=772&amp;height=549"
          }' >> "src/Plugins/${pluginName}/config.json"

    mkdir "src/Plugins/${pluginName}/api"
    touch "src/Plugins/${pluginName}/api/plgnapi.php"

    mkdir "src/Plugins/${pluginName}/core"
    mkdir "src/Plugins/${pluginName}/core/APIControllers"
    mkdir "src/Plugins/${pluginName}/core/model"
    mkdir "src/Plugins/${pluginName}/core/model/Base"
    mkdir "src/Plugins/${pluginName}/core/model/Map"

    mkdir "src/Plugins/${pluginName}/core/VIEWControllers"


    mkdir "src/Plugins/${pluginName}/locale"
    touch "src/Plugins/${pluginName}/locale/index.html"
    mkdir "src/Plugins/${pluginName}/locale/js"
    mkdir "src/Plugins/${pluginName}/locale/textdomain"

    mkdir "src/Plugins/${pluginName}/mysql"
    touch "src/Plugins/${pluginName}/mysql/index.html"
    touch "src/Plugins/${pluginName}/mysql/Install.sql"
    touch "src/Plugins/${pluginName}/mysql/Uninstall.sql"
    mkdir "src/Plugins/${pluginName}/mysql/upgrade"
    echo '{
          "current": {
                   "versions": [
                   ],
                   "prescripts": [
                   ],
                   "scripts": [
                   ],
                   "dbVersion": "1.0.0"
               }
           }' > "src/Plugins/${pluginName}/mysql/upgrade.json"
    touch "src/Plugins/${pluginName}/mysql/index.html"

    mkdir "src/Plugins/${pluginName}/skin"
    mkdir "src/Plugins/${pluginName}/skin/css"
    mkdir "src/Plugins/${pluginName}/skin/js"

    mkdir "src/Plugins/${pluginName}/v2"
    mkdir "src/Plugins/${pluginName}/v2/routes"
    touch "src/Plugins/${pluginName}/v2/routes/v2route.php"
    mkdir "src/Plugins/${pluginName}/v2/templates"

    new_dir="src/Plugins/${pluginName}/locale/textdomain/"

    # now we manage the localisation files
    # this files are required to avoid a bug while opening page
    touch "src/Plugins/${pluginName}/locale/js/en_US.js"
    touch "src/Plugins/${pluginName}/locale/js/en_CA.js"
    touch "src/Plugins/${pluginName}/locale/js/en_AU.js"
    touch "src/Plugins/${pluginName}/locale/js/en_GB.js"

    for row in $(cat "src/locale/locales.json" | jq -r '.[] | @base64'); do
       _jq() {
         echo ${row} | base64 --decode | jq -r ${1}
       }

       lang=$(echo $(_jq '.locale'))

       if [ ! -e "${new_dir}/${lang}" ]; then
            mkdir -p "${new_dir}${lang}/LC_MESSAGES/"
       fi

       touch "${new_dir}${lang}/LC_MESSAGES/messages-${pluginName}.po"

       plugin_js_dir="locale/JSONKeys_JS_Plugins/${pluginName}/"

       if [ ! -e "${plugin_js_dir}" ]; then
       			mkdir "${plugin_js_dir}"
       fi

       if [ ! -e "${plugin_js_dir}${lang}" ]; then
       			mkdir "${plugin_js_dir}${lang}"
       fi

       touch "${plugin_js_dir}${lang}/js-strings.po"
    done
#done
