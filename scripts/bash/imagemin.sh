#!/bin/bash

# Read parameters
IMAGEPATH=$1


imagemin=$(command -v imagemin)
if [ "$imagemin" == "" ]; then
	echo -e "Could not find imagemin. Please install as root:\nnpm install --g imagemin-cli imagemin-jpeg-recompress imagemin-pngquant"
	exit 3
fi

# Set defaults
if [ "$IMAGEPATH" == "" ]; then
	echo "No path given. Usage: imagemin.sh PATH"
	exit 1
fi

TEMPFILE="____temp____";

function o_jpg() {
	$imagemin -p -o 7 --plugin jpeg-recompress --method smallfry --quality high --min 60 "$1" > "$2"
}
function o_png() {
	$imagemin -p -o 7 --plugin pngquant "$1" > "$2"
}
function optimize() {
	f="$1"
	cmd="$2"
	sizeBefore=$(stat -c%s "$1")
	sizeBeforeFmt=$(numfmt --to=iec-i --suffix=B "$sizeBefore")

	$cmd "$f" "$TEMPFILE"
	
	if [ -f $TEMPFILE ]; then
		sizeAfter=$(stat -c%s "$TEMPFILE")
		sizeAfterFmt=$(numfmt --to=iec-i --suffix=B "$sizeAfter")
		let diff="$sizeBefore - $sizeAfter"

		echo -n "$f: "

		if [ $diff -gt 0 ]; then
			perc=$(gawk "BEGIN { printf \"%.0f\", ($diff/$sizeBefore)*100}")
			echo "$sizeBeforeFmt >> $sizeAfterFmt ($perc%)"
			chown --reference=$f $TEMPFILE
			chmod --reference=$f $TEMPFILE
			mv $TEMPFILE $f
		else
			echo "No optimization"
		fi
	else
		echo "Optimisazion failed!"
	fi
}
function findCommand() {
	ret="-iname *.$1"
	shift
	for i in $@; do
		ret="$ret -o -iname *.$i"
	done
	echo $ret
}
function optimizeDir() {
	cmd=$1
	shift
	findCmd=$(findCommand $@)
	for f in $(find $IMAGEPATH $findCmd); do
		if [ -w "$f" ]; then
			optimize "$f" "$cmd"
		else
			echo "$f is not writable"
		fi
	done;
}

# for debugging
#set -x
# no globbing
set -f

echo "optimizing images"
optimizeDir o_png png
optimizeDir o_jpg jpg jpeg

