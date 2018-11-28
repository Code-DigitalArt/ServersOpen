#!/bin/bash

Name="lunddev"

TimeString=$(date +"%Y%m%d.%H%M")

MagePassword="SNQZ8gIhc5gVV6RAtSrm"
EEPassword="15CtGD-N**5Pf-uJRdgk"

MageDB="${Name}_mage"
EEDB="${Name}_ee"

MageFile="${MageDB}.${TimeString}.sql"
EEFile="${EEDB}.${TimeString}.sql"

MageDBSnapshot(){
	mysqldump --user=${MageDB} --password=${MagePassword} ${MageDB} > ${MageFile}	
	echo $MageFile
}

EEDBSnapshot(){
	mysqldump --user=${EEDB} --password=${EEPassword} ${EEDB} > ${EEFile}
	echo $EEFile
}

EEDBSnapshot
MageDBSnapshot