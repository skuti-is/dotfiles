#!/bin/bash

function usage()
{
	PROG=$0
	echo "Usage: $PROG domain"
}

# Set parameters
DOMAIN=$1


# Set defaults
HOST=${DOMAIN%.is}
CONFPATH="/etc/apache2/sites-available/"


# Validate required
if [ "$DOMAIN" == "" ]; then
	echo "Parameter missing."
	usage
	exit 1	
fi


# Run
cat > "$CONFPATH$DOMAIN.conf" <<EOD
<VirtualHost *:80>
	ServerName $HOST.th
	DocumentRoot /home/thrstn/www/$HOST.is/current/
	ErrorLog /var/log/apache2/$HOST.is-error_log
	CustomLog /var/log/apache2/$HOST.is-access_log combined
</VirtualHost>
EOD
a2ensite $DOMAIN
service apache2 reload
exit 0
