#!/usr/bin/env bash

githubToken=$1

if [ -z ${githubToken} ]; then
    echo -n "Enter your github token and press [ENTER]: "
    read githubToken
fi

npm install github-release-notes -g

export GREN_GITHUB_TOKEN=${githubToken}

gren changelog --override --tags=all
