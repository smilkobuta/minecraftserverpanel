#!/bin/bash

cd `dirname $0`

COPY_DIR=server-bedrock
TARGET_DIR=`basename "$1"`
BACKUP_SUFFIX="."`date +%Y%m%d`
COPY_FILES1=(access.sh restart.sh start.sh stop.sh world_backups)
COPY_FILES2=(permissions.json server.properties whitelist.json worlds)

if [ "$TARGET_DIR" = "" ]; then
	echo "Usage: update-server-bedrock.sh server-bedrock-xxx"
	exit
fi


if [ ! -e $COPY_DIR ]; then
	echo "$COPY_DIR directory not found!"
	exit
fi

if [ ! -e $TARGET_DIR ]; then
        echo "$TARGET_DIR directory not found!"
        exit
fi

echo "Stopping $TARGET_DIR"
$TARGET_DIR/stop.sh

echo "Update $TARGET_DIR"

BACKUP_DIR=$TARGET_DIR$BACKUP_SUFFIX

echo "Backup tp $BACKUP_DIR"
rm -rf $BACKUP_DIR
mv $TARGET_DIR $BACKUP_DIR
cp -Rpd $COPY_DIR $TARGET_DIR

for item in ${COPY_FILES1[@]}; do
    echo "Coping $item .."
    cp -Rpd "$BACKUP_DIR/$item" "$TARGET_DIR/$item"
done

cd $TARGET_DIR;

echo "Startup default world"
screen -UAdmS update-minecraft bash -c 'LD_LIBRARY_PATH=. ./bedrock_server; exec bash'
echo "Waiting for 20 seconds.."
sleep 20
echo "Finising default world"
screen -p 0 -S update-minecraft -X eval 'stuff "^C"'
screen -p 0 -S update-minecraft -X quit

cd ../
echo "done"

for item in ${COPY_FILES2[@]}; do
    echo "Coping $item .."
    rm -rf "$TARGET_DIR/$item"
    cp -Rpd "$BACKUP_DIR/$item" "$TARGET_DIR/$item"
done

echo "Starting $TARGET_DIR"
$TARGET_DIR/start.sh

