#!/usr/bin/perl
#
# Purpose: convert hindi numbers to arabic numbers
# Author : Xiaoyi Ma
# Date   : 6/9/2003

use utf8;
use Encode;

$Usage = "Usage: $0 [filename]\n";
die $Usage if ( @ARGV > 1 or ( @ARGV == 1 and ! -f $ARGV[0] ));

if ( @ARGV ) {
    open( STDIN, shift );
}
binmode STDIN, ":utf8";
binmode STDOUT, ":utf8";

while(<>) {
    $org = $_;

    s/\x{0966}/0/g;
    s/\x{0967}/1/g;
    s/\x{0968}/2/g;
    s/\x{0969}/3/g;
    s/\x{096a}/4/g;
    s/\x{096b}/5/g;
    s/\x{096c}/6/g;
    s/\x{096d}/7/g;
    s/\x{096e}/8/g;
    s/\x{096f}/9/g;
    
    if(/^\d+$/) {
	print $_;
    } else {
	print $org;
    }
}
