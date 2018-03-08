if [ -z "$2" ]; then
	echo usage: $0 GOsource language
	exit
fi


if [ -d $2 ]; then
	echo Directory $2 is in my way!
	echo usage: $0 GOsource language
	exit
fi

if [ -d $1 ]; then

	cp -R $1 $2
	
	if [ -d $2 ]; then
		cd $2
		find . -type f ! \(  -name "$2.js" -o  -name "$2.inc.php" \) -exec rm -f {} \;
		find -depth -type d -empty -exec rmdir {} \;
	else
		echo "Error: could not create $2";
		echo usage: $0 GOsource language
		exit
	fi
else
	echo "Error: $1 does not exist"
	echo usage: $0 GOsource language
fi