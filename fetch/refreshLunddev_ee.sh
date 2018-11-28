#!/bin/bash
#

# Change this to be the root of the database name 
# Example: "lunddev_ee" and "lunddev_ee" have the root "lunddev"
name="lunddev"

DBEE="${name}_ee"

filename="$1"

scp lunddev@208.185.97.59:~/$filename ../.

# Change this to the database you want to refresh with
eeFile="../$filename"

# These are your local MySQL credentials
localUser="root"
localPassword="Unl3aded"



# This is the script
echo "EE DB drop and create"
mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBEE}; CREATE DATABASE ${DBEE};"

echo "Importing EE DB"
mysql --user=${localUser} --password=${localPassword} ${DBEE} < ${eeFile}