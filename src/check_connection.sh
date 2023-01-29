#!/bin/bash

variable=TRUE;
while variable==TRUE; do
  echo "running check_connection.php"
  temp=$(/usr/bin/php -f ./check_connection.php)
  if [[ "${temp:-}" = "0" ]]; then 
    echo 'No connection, exiting'
    exit 1
  fi
  sleep 5m
done
