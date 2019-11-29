#!/bin/bash

cd `dirname $0`

SCREEN_NAMES=$(screen -ls | grep server-bedrock | perl -pe 's/.*server-bedrock-([^\s]+).+/$1/')

for name in $SCREEN_NAMES; do
    ./server-bedrock-${name}/restart.sh
done

