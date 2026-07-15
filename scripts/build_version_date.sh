build=$(jq -r '.build' src/composer.json)

build=$((build+1))

MA_DATE=$(date +%F)

jq ".build = \"$build\"" src/composer.json > src/composer.tmp.json && mv src/composer.tmp.json src/composer.json
jq ".time = \"$MA_DATE\"" src/composer.json > src/composer.tmp.json && mv src/composer.tmp.json src/composer.json

