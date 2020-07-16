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

START_DIR=$(pwd)

if [ ! -f "$FILEPATH" ]; then
    ## remove the .env and protected files file if they exist
	mkdir "../$TIMESTAMP"
    for F in $(cat .gitignore)
    do
        if [ "$F" == "vendor" ]; then
	        echo "skipping vendor directory"
        
        elif [ -f "$F" ]; then
	        echo "file - $F"
            mkdir -p $(dirname "../$TIMESTAMP/$F")
            mv "$F" "../$TIMESTAMP/$F"

        elif [ -d "$F" ]; then
	        echo "dir - $F"
	        mkdir -p $(dirname "../$TIMESTAMP/$F")
            mv "$F" "../$TIMESTAMP/$F"
	        if [ -f "../$TIMESTAMP/$F/.gitkeep" ]; then
		        mkdir "$F"
		        touch "$F/.gitkeep"
	        fi
            
        fi
    done

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

    cd "$START_DIR"
    for F in $(cat .gitignore)
    do
        if [ -f "../$TIMESTAMP/$F" ]; then
            cp "../$TIMESTAMP/$F" "$F"
            
        elif [ -d "../$TIMESTAMP/$F" ]; then
	        echo "dir - $F"
	        if [ ! -d $(dirname "$F") ]; then
		    	  mkdir -p $(dirname "$F")
	        fi
	        cp "../$TIMESTAMP/$F" "$F" 
            
        fi
    done

    # rm -r "../$TIMESTAMP"

    echo "adding back node_modules via npm..."
    cd "src/assets"
    # npm i
    echo "done."

else
    echo "build is current"
fi

