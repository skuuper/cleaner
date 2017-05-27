#!/bin/sh

echo Russian tokenization has not been implemented yet, using generic scripts...

cat $* | \
$CTK/bin/axis.generic/gtoken.pl | \
$CTK/bin/eos_tokenize.pl
