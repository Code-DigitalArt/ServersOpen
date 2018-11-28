#!/bin/bash
#
name="eldorado"
hasMage=1
hasEE=0

timeFormat='%Y%m%d.%H%M'
timeNow=$(date +"${timeFormat}")

remoteHost="208.185.97.31"

remoteUserMage="${name}_mage"
remotePasswordMage="Ux1kZzg0"

remoteUserEE="${name}_ee"
remotePasswordEE=""

DBMage="${remoteUserMage}"
DBEE="${remoteUserEE}"

mageFile="../${DBMage}.${timeNow}.sql"
eeFile="../${DBEE}.${timeNow}.sql"

localUser="root"
localPassword="Unl3aded"

getMageDB(){
	echo "Grabbing Mage DB"
	mysqldump --user=${remoteUserMage} --password=${remotePasswordMage} -h ${remoteHost} ${DBMage} > ${mageFile}

	echo "Mage DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBMage}; CREATE DATABASE ${DBMage};"

	echo "Importing Mage DB"
	mysql --user=${localUser} --password=${localPassword} ${DBMage} < ${mageFile}	
}

getEEDB(){
	echo "Grabbing EE DB"
	mysqldump --user=${remoteUserEE} --password=${remotePasswordEE} -h ${remoteHost} ${DBEE} > ${eeFile}

	echo "EE DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBEE}; CREATE DATABASE ${DBEE};"

	echo "Importing EE DB"
	mysql --user=${localUser} --password=${localPassword} ${DBEE} < ${eeFile}
}

[ $hasMage -eq 1 ] && getMageDB
[ $hasEE -eq 1 ] && getEEDB