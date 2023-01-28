#!/bin/bash

variable=TRUE;
while variable==TRUE; do
  echo "running check_connection.php"
  php -f check_connection.php
  sleep 5m
done
