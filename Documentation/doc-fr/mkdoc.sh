#!/bin/bash

red=`tput setaf 1`
green=`tput setaf 2`
yellow=`tput setaf 3`
blue=`tput setaf 4`
reset=`tput sgr0`


mkdocs build

echo "${red}Voulez-vous l'envoyer en ligne y/n${reset}"
read reponse
if [[ "$reponse" == "y" ]]
then


sshpass -p "#aliasdircat1" ssh philippelo@imathgeo.com -p22 "rm -rf /home3/philippelo/docs.ecclesiacrm.com/fr/*"

sshpass -p "#aliasdircat1" scp -rp site/* philippelo@imathgeo.com:/home3/philippelo/docs.ecclesiacrm.com/fr

fi

