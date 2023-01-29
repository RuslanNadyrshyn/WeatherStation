#!/bin/bash

variable=TRUE;
while variable==TRUE; do
  date +"%Y/%m/%d %H:%M:%S Running check_connection.php"
  temp=$(/usr/bin/php -f ./check_connection.php)
  if [[ "${temp:-}" = "0" ]]; then
    date +"%Y/%m/%d %H:%M:%S No connection, exiting"
    exit 1
  fi
  sleep 5m
done
