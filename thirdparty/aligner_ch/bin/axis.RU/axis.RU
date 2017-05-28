#!/bin/sh

echo Russian tokenization has not been implemented yet, using generic scripts... 1>&2

cat $* | \
$CTK/bin/axis.RU/gtoken.pl | \
$CTK/bin/axis.RU/lowercase

#$CTK/bin/axis.EN/english_stemmer.pl
