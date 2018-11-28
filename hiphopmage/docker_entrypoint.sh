#!/bin/bash

hhvm --mode daemon

apache2ctl start

service ssh start

sleep 10

lynx -dump 0.0.0.0 | sed 's/http.*.dev/http:\/\/0.0.0.0/g' | sed 's/http.*.com/ /g' | sed 's/http.*.net/ /g' | sed 's/http.*0.0.0.0\/#/ /g' | awk '/http/{print $2}'  > links.txt \

while read url; do
    curl "$url"
done < links.txt

tail -f /var/log/apache2/error.log
