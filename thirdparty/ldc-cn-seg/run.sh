if [ "$#" -ne 2 ]; then
    echo "UTF-8 compatible Chinese segmenter"
    echo "Usage: ./run.sh path_to_source.txt path_to_dest.txt"
fi
perl mansegment-utf8.pl Mandarin.fre.utf8 < $1 > $2
