#!/bin/bash

## run via npm run dist


exit;



GIT_CLEAN=$(git status -s)

if [ ! -z "$GIT_CLEAN" ]; then
	echo "There are changed files that must be committed before proceeding."
	echo "exiting."
	exit

fi

npm run production
git commit -am 'automated build.'
cd ../..
bash build.sh
