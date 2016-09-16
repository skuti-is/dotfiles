#!/bin/bash

# Read parameters
IMAGEPATH=$1
MTIME=$2

# Check for compressors
CMDOPTIPNG=$(command -v optipng)
CMDJPEGTRAN=$(command -v jpegtran)

if [ "$CMDOPTIPNG" == "" -o "$CMDJPEGTRAN" == "" ]; then
	echo -e "Could not find compressors. Please install as root:\nnpm install -g optipng-bin jpegtran-bin"
	exit 3
fi

# Set defaults
if [ "$IMAGEPATH" == "" ]; then
	echo "No path given. Usage: $0 PATH (MTIME)"
	echo "PATH: Path to files"
	echo "MTIME: Optional. Only find files modified less than MTIME days ago"
	exit 1
fi

# Create temporary filename
#TEMPFILE=".$(date +%s)-temp___";
TEMPFILE="___temp___";

function getJpegCmd() {
	$CMDJPEGTRAN -copy none -progressive -optimize -trim -outfile "$2" "$1"
}
function getPngCmd() {
	$CMDOPTIPNG -silent -strip all -o2 -out "$2" "$1"
}
function optimizeFile() {
	f="$1"
	cmd="$2"
	sizeBefore=$(stat -c%s "$1")

	$cmd "$f" "$TEMPFILE"
	
	if [ -f $TEMPFILE ]; then
		sizeAfter=$(stat -c%s "$TEMPFILE")
		if [ $sizeAfter -gt 32 ]; then
			let diff="$sizeBefore - $sizeAfter"

			echo -n "$f: "

			if [ $diff -gt 0 ]; then
				perc=$(gawk "BEGIN { printf \"%.0f\", ($diff/$sizeBefore)*100}")
				echo -e "\e[32m($perc%)\e[0m"
				chown --reference="$f" "$TEMPFILE"
				chmod --reference="$f" "$TEMPFILE"
				mv "$TEMPFILE" "$f"
			else
				echo -e "\e[2mNo optimization\e[0m"
			fi
		else
			echo -e "\e[31mOptimisazion failed!\e[0m Temporary file zero bytes."
		fi
	else
		echo -e "\e[31mOptimisazion failed!\e[0m Temporary file not created."
	fi
}
function findCommand() {
	ext=""
	for i in $@; do
		if [ $ext ]; then
			ext="$ext|$i"
		else
			ext="$i"
		fi
	done
	ret="-regextype posix-egrep -regex .*\.($ext)$"

	if [ $MTIME ]; then
		ret="$ret -mtime -$MTIME"
	fi

	echo $ret
}
function optimizeDir() {
	cmd=$1
	shift
	findCmd=$(findCommand $@)

	# we need to use while read to optimize files with space in filename
	find $IMAGEPATH $findCmd | while read -r FILE;do
		optimizeFile "$FILE" "$cmd"
	done
}

# for debugging
#set -x
# no globbing
set -f

#echo "fixing permissions. requires root." 
#sudo chmod a+rw $IMAGEPATH -R

echo "optimizing images"

# optimize
optimizeDir getPngCmd png
optimizeDir getJpegCmd jpg jpeg

# remove any tempfile
if [ -f $TEMPFILE ]; then
	rm $TEMPFILE
fi
