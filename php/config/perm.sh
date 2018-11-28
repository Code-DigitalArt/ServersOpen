#!/bin/bash

if [ -z "$1" ]
  then
    echo "No argument supplied"
fi
find $1 -type d -exec chmod 755 {} \;
find $1 -type f -exec chmod 644 {} \;
