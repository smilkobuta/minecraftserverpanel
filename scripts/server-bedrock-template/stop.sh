#!/bin/bash

cd `dirname $0`

SCREEN_NAME=$(basename `pwd`)

SCREEN_NAMES=$(screen -ls | grep ${SCREEN_NAME} | perl -pe 's/\s+([^\s]*)\s+.+/$1/')

for name in $SCREEN_NAMES; do
    screen -p 0 -S $name -X eval 'stuff "^C"'
    screen -X -S $name quit > /dev/null 2>&1
done

