#!/bin/bash
#
name="wrc"
hasMage=1
hasEE=1

timeFormat='%Y%m%d.%H%M'
timeNow=$(date +"${timeFormat}")

remoteHost="68.64.209.250"

remoteUserMage="${name}_mage"
remotePasswordMage="XDXn4cwzVz2k06ie-i*jaRMoVietviKf1zP3fNz0"

remoteUserEE="${name}_ee"
remotePasswordEE="asbmAfKd0qCyVy*G4wSV-DD1pXwBrt#yxMJw3on4"

DBMage="${remoteUserMage}"
DBEE="${remoteUserEE}"

mageFile="/home/radosun/Data/${DBMage}.${timeNow}.sql"
eeFile="/home/radosun/Data/${DBEE}.${timeNow}.sql"

localUser="root"
localPassword="password"
localhost="127.0.0.1"

getMageDB(){
	echo "Grabbing Mage DB"
	mysqldump --user=${remoteUserMage} --password=${remotePasswordMage} -h ${remoteHost} --skip-triggers --single-transaction ${DBMage} > ${mageFile}

	echo "Mage DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -h ${localhost} -e "DROP DATABASE IF EXISTS ${DBMage}; CREATE DATABASE ${DBMage};"

	echo "Importing Mage DB"
	mysql --user=${localUser} --password=${localPassword} -h ${localhost} ${DBMage} < ${mageFile}
}

getEEDB(){
	echo "Grabbing EE DB"
	mysqldump --user=${remoteUserEE} --password=${remotePasswordEE} -h ${remoteHost} ${DBEE} > ${eeFile}

	echo "EE DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -h ${localhost} -e "DROP DATABASE IF EXISTS ${DBEE}; CREATE DATABASE ${DBEE};"

	echo "Importing EE DB"
	mysql --user=${localUser} --password=${localPassword} -h ${localhost} ${DBEE} < ${eeFile}
}

[ $hasMage -eq 1 ] && getMageDB
[ $hasEE -eq 1 ] && getEEDB