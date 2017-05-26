#!/bin/sh
#
# Purpose: Wrapper script to align English and Hindi (UTF8)
#          parallel text.
# Usage: champollion.EH <english sentence file> <hindi sentence file> <alignment file>
# Input: English text file
#        Hindi text file
# Output: alignment file, alignment are in the format of:
#         e_st_ids <=> h_st_ids
#         Each e_st_ids or h_st_ids can contain up to four sentence ids
#         delimited by comas, it also can be "omitted" indicating no
#         mapping has been found.
#

# print usage if necessary
if [ $# -ne 3 ]; then
    echo "Usage: $0 <english sentence file> <hindi sentence file> <alignment file>"
    exit 1
fi

en_axis=/tmp/`basename $1`.en.axis
hn_axis=/tmp/`basename $2`.hn.axis


# Tokenize English
echo "Tokenizing English ... "
$CTK/bin/axis.EN/axis.EN $1 > $en_axis
if [ $? -ne 0 ]; then
exit 2
fi

# Tokenize Hindi
echo "Tokenizing Hindi ... "
$CTK/bin/axis.HN/axis.HN $2 > $hn_axis
if [ $? -ne 0 ]; then
exit 3
fi
# Align sentences
echo "Aligning Sentences ... "
$CTK/bin/champollion -d $CTK/lib/ehdict.utf8.txt -s $CTK/lib/E.stoplist -c 1.6 $en_axis $hn_axis $3

# Remove intermediate files
rm $en_axis $hn_axis
