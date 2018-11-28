#!/bin/bash
#
DBMage="lunddev_mage"
mageFile="../sql/lunddev_mage.local.20160822.1458.sql"

localUser="root"
localPassword="Unl3aded"

refreshMageDB() {
	echo "Mage DB drop and create"
	mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBMage}; CREATE DATABASE ${DBMage};"

	echo "Importing Mage DB"
	mysql --user=${localUser} --password=${localPassword} ${DBMage} < ${mageFile}
}

refreshMageDB