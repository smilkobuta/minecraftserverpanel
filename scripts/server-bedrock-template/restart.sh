#!/bin/bash

cd `dirname $0`

WAIT=5
STOPSCRIPT=./stop.sh
STARTSCRIPT=./start.sh
SCREEN_NAME=$(basename `pwd`)

screen -p 0 -S ${SCREEN_NAME} -X eval 'stuff "say '${WAIT}'秒後にサーバーを再起動します\015"'
screen -p 0 -S ${SCREEN_NAME} -X eval 'stuff "say すぐに再接続可能になるので、しばらくお待ち下さい\015"'

sleep $WAIT
$STOPSCRIPT

mkdir -p world_backups
find world_backups/ -mtime +3 -name "*.tar.gz" -exec rm {} \;
tar -czf world_backups/worlds_`date +%Y%m%d`.tar.gz worlds

$STARTSCRIPT

