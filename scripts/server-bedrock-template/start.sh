#!/bin/bash

cd `dirname $0`

SCREEN_NAME=$(basename `pwd`)

screen -UAdmS $SCREEN_NAME bash -c 'LD_LIBRARY_PATH=.:/usr/local/gcc-7.4.0/lib64 ./bedrock_server; exec bash'

