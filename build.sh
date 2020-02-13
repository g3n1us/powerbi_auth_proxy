#!/bin/bash

VERSION=$(git rev-parse --short HEAD)

if [ ! -d "../auth_proxy_builds" ]; then
    mkdir "../auth_proxy_builds"
fi

if [ ! -d "../auth_proxy_builds/versions" ]; then
    mkdir "../auth_proxy_builds/versions"
fi

FILENAME="powerbi_auth_proxy_build_$VERSION"
FILEPATH="../auth_proxy_builds/$FILENAME.zip"

TIMESTAMP=$(date +%F--%H-%M-%S)
VERSIONSDIR="../auth_proxy_builds/versions"

if [ ! -f "$FILEPATH" ]; then
    echo $VERSION > hash.txt
    composer archive -f zip --dir="../auth_proxy_builds" --file="$FILENAME" --ignore-filters
    cd $VERSIONSDIR
    ln -s "../$FILENAME.zip" "build_$TIMESTAMP.zip"
    cd ..
    if [ -f current.zip ]; then
        rm "current.zip"
    fi
    if [ -f hash.txt ]; then
        rm "hash.txt"
    fi
    cp "$FILENAME.zip" "current.zip"
    echo $VERSION > hash.txt
    echo "syncing..."
    bash "../S3_SYNC_AUTH_PROXY.sh"

else
    echo "build is current"
fi

