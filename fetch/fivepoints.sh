#!/bin/bash
#
name="fivepoints"
hasWP=1

timeFormat='%Y%m%d.%H%M'
timeNow=$(date +"${timeFormat}")

remoteHost="208.185.97.42"

remoteUserWP="${name}_wp"
remotePasswordWP="QVsRvh*ThiKOnq"

DBWP="${remoteUserWP}"

WPFile="../${DBWP}.${timeNow}.sql"

localUser="root"
localPassword="Unl3aded"

getWPDB(){
	echo "Grabbing WP DB"
	mysqldump --user=${remoteUserWP} --password=${remotePasswordWP} -h ${remoteHost} ${DBWP} > ${WPFile}

	echo "WP DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBWP}; CREATE DATABASE ${DBWP};"

	echo "Importing WP DB"
	mysql --user=${localUser} --password=${localPassword} ${DBWP} < ${WPFile}	
}

[ $hasWP -eq 1 ] && getWPDB