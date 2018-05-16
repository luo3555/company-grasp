#!/bin/bash
# different time use different max process
START_TIME=18
END_TIME=5

# DATE from 1 to 24
DATE_TIME=`date '+%H'`

if [ $DATE_TIME -gt $START_TIME ] || [ $DATE_TIME -lt $END_TIME ];
then
    MAX_PROCESS=6
else
    MAX_PROCESS=4
fi

# Max Proess
MAX_PROCESS=1

BASEPATH=$(cd $(dirname $0); pwd)
PHP_BIN=$(which php)
RUN_SCRIPT='collect.php '
RUN_COMMAND=$BASEPATH'/'$RUN_SCRIPT$1

# Current process
CURRENT_PROCESS=$(ps -ef | grep $BASEPATH'/'$RUN_SCRIPT | grep -v grep | wc -l)

if [ $CURRENT_PROCESS -lt $MAX_PROCESS ]; 
then
  $PHP_BIN $RUN_COMMAND >> $BASEPATH'/collect.log' & 
else
  echo 'Current Process:'$CURRENT_PROCESS
  echo 'Allow Max Process:'$MAX_PROCESS
fi