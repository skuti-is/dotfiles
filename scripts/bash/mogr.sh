param1=$1
filename=${param1#./}

convert $1 -resize 200x200 t_$filename
