#!/usr/bin/env bash

githubToken=$1

if [ -z ${githubToken} ]; then
    echo -n "Enter your github token and press [ENTER]: "
    read githubToken
fi

npm install github-release-notes -g

gren changelog --generate --override --token=${githubToken}
