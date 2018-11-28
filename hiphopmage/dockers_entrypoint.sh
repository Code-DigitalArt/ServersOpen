#!/bin/bash

hhvm --mode daemon

apache2ctl start

sleep 10

linx -dump 0.0.0.0 | sed 's/http.*.dev/http:\/\/0.0.0.0/g' | sed 's/http.*.com/ /g' | sed 's/http.*.net/ /g' | awk '/http/{print $2}' > links.txt \

while read url; do
    curl "$url"
done < links.txt

tail -f /var/log/apache2/error.log
