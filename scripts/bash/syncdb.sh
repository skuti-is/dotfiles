#!/bin/bash

function usage()
{
	PROG=$0
	echo "Usage: $PROG source [host] [destination]"
}

# Set parameters
SRC=$1
HOST=$2
DEST=$3

# Validate required
if [ "$SRC" == "" ]; then
	echo "Source parameter missing."
	usage
	exit 1	
fi


# Set defaults
if [ "$DEST" == "" ]; then
	DEST=$SRC
fi

if [ "$HOST" == "" ]; then
	HOST="dragora.stefna.is"
fi


# Run
if [ "$SRC" != "" ] && [ "$DEST" != "" ]; then
	ssh $HOST 'mysqldump '$SRC'' | mysql $DEST
	exit 0
else
	echo "Couldn't detect database config."
	exit 1
fi
