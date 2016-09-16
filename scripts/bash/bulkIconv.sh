#!/bin/bash

function usage()
{
	PROG=$0
	echo "Usage: $PROG srcCharset destCharset"
}

# Parameters
srcCharset=$1
destCharset=$2


# Validate
if [ "$srcCharset" == "" ]; then
	echo "Source charset missing."
	usage
	exit 1	
fi

if [ "$destCharset" == "" ]; then
	echo "Destination charset missing."
	usage
	exit 1	
fi


# Run
for file in *; do
		iconv -f $srcCharset -t $destCharset "$file" -o "___temporary-iconv-file___"
		mv ___temporary-iconv-file___ $file
		echo -e "\e[32m[OK]\e[0m Converted $file..."
done
