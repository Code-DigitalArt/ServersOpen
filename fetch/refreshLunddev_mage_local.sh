#!/bin/bash
#

# Change this to be the root of the database name 
# Example: "lunddev_ee" and "lunddev_mage" have the root "lunddev"
name="lunddev"

DBMage="${name}_mage"

filename="$1"

# Change this to the database you want to refresh with
mageFile="../$filename"
# mageFile="../lunddev_mage.nicklocal.afterpims0.0.5setup.sql"

# This will be the remote URL for the site's database you are using, probably an
# Unleaded Group dev server...
remoteURL="lunddev.build.moe"

# Change this depending on how you have your local environment set up
localURL="lunddev.local.com"

# These are your local MySQL credentials
localUser="root"
localPassword="Unl3aded"



# This is the script
echo "Mage DB drop and create"
mysql --user=${localUser} --password=${localPassword} -e "DROP DATABASE IF EXISTS ${DBMage}; CREATE DATABASE ${DBMage};"

echo "Importing Mage DB"
mysql --user=${localUser} --password=${localPassword} ${DBMage} < ${mageFile}

echo "Fixing urls"
mysql --user=${localUser} --password=${localPassword} ${DBMage} << END

UPDATE core_config_data SET value = replace(value, "${remoteURL}", "${localURL}");

END