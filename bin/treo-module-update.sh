#!/bin/bash

# prepare PHP
php=$2

# prepare file(s) path
path="data/treo-module-update.txt"
log="data/composer.log"

while true
do
   # is neet to update composer
   if [ -f $path ]; then
     # delete file
     rm $path;

     # run composer update command
     $php composer.phar run-script pre-update-cmd > /dev/null 2>&1
     if ! $php composer.phar update --no-dev --no-scripts > $log 2>&1; then
       echo "{{error}}" >> $log 2>&1
     else
       $php composer.phar run-script post-update-cmd > /dev/null 2>&1
       $php console.php composer-log > /dev/null 2>&1
       echo "{{finished}}" >> $log 2>&1
     fi
   fi
   sleep 1;
done