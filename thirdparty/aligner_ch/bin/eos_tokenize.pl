#!/usr/bin/perl -n
#
# Purpose: split input into one token per line,
#          put <EOS> marker at the end of sentence
# Author: Xiaoyi Ma, LDC
# Date:   September 17, 2003
# Input: text
# Output: text, one token per line
#         <EOS> marker at the end of each sentence
#

split ' ', $_;

foreach (@_) {
    print "$_\n";
}

print "<EOS>\n";
