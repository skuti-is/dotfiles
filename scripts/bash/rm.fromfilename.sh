# removes periots from filename
for i in $(find -name '*.*.jpg' -type f); do 
	b=$(basename $i); 
	d=$(dirname $i); 
	n=$(echo $b | sed 's/\./_/g' | sed 's/_jpg$/.jpg/'); 
	mv -v $i $d/$n; 
done
