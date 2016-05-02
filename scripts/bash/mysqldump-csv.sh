#!/bin/bash

function usage()
{
	PROG=$0
	echo "Usage: $PROG table database field filename.csv"
}

# Validate parameters
if [ "$1" == "" ]; then
	echo "Parameters missing."
	usage
	exit 1	
fi

# Set defaults
TABLE=$1
DATABASE=$2
FIELD=$3
FILENAME=$4


# Validate defaults
if [ "$TABLE" == "" ]; then
	echo "Table missing."
	usage
	exit 1	
fi

if [ "$DATABASE" == "" ]; then
	echo "Database missing."
	usage
	exit 1	
fi

if [ "$FIELD" == "" ]; then
	FIELD="*"	
fi

if [ "$FILENAME" == "" ]; then
	FILENAME="/tmp/mydump.csv"
fi


# Run
mysql ${DATABASE} -e "select ${FIELD} from ${TABLE} into outfile '${FILENAME}' fields enclosed by '' lines terminated by '\n';"
