#!/bin/bash

variable=TRUE;
while variable==TRUE; do
  echo "running check_connection.php"
  temp=$(/usr/bin/php -f ./check_connection.php)
  [[ temp=='0' ]] && { echo 'No connection, exiting'; exit 1; }
  sleep 5m
done
