#!/bin/bash

cd `dirname $0`

TARGET_DIR=`basename "$1"`
BACKUP_SUFFIX="."`date +%Y%m%d`
PROP_KEY=$2
PROP_VAL=$3

if [ "$TARGET_DIR" = "" -o "$PROP_KEY" = "" -o "$PROP_VAL" = "" ]; then
	echo "Usage: update-server-properties.sh server-bedrock-xxx key value"
	exit
fi


if [ ! -e $TARGET_DIR ]; then
        echo "$TARGET_DIR directory not found!"
        exit
fi

echo "s/^($PROP_KEY)=.+/\$1=$PROP_VAL/"


perl -i"$BACKUP_SUFFIX" -pe "s/^($PROP_KEY)=.*/\$1=$PROP_VAL/" $TARGET_DIR/server.properties

