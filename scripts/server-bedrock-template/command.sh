#!/bin/bash

cd `dirname $0`
export command=$1

SCREEN_NAME=$(basename `pwd`)

SCREEN_NAMES=$(screen -ls | grep ${SCREEN_NAME} | perl -pe 's/\s+([^\s]*)\s+.+/$1/')

for name in $SCREEN_NAMES; do
    screen -p 0 -S $name -X eval "stuff '$command\015'"
done
