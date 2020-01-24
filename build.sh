#!/bin/bash

VERSION=$(git rev-parse --short HEAD)

if [ ! -d "../auth_proxy_builds" ]; then
    mkdir "../auth_proxy_builds"
fi

FILENAME="powerbi_auth_proxy_build_$VERSION"

if [ ! -f "../auth_proxy_builds/$FILENAME.zip" ]; then
    composer archive -f zip --dir="../auth_proxy_builds" --file="$FILENAME" --ignore-filters
fi
