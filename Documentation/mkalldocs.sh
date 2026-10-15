#!/bin/bash

# 1. Masquer l'alerte MkDocs 2.0
export NO_MKDOCS_2_WARNING=1

# 2. Mettre à jour les outils avec les bonnes versions
pip install --upgrade -r requirements.txt

cd doc-en/
bash mkdoc.sh

cd ../doc-fr/
bash mkdoc.sh

cd ..
