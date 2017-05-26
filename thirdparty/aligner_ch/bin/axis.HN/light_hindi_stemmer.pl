#!/usr/bin/perl
##################################################
#
#  Light Stemmer for Hindi
#  
#  Modified by Xiaoyi Ma 6/6/2003
#  Created by Kareem Darwish
#  Last Modified June 4, 2003
#  kareem@glue.umd.edu
#
##################################################
#
#  Based on the suffix list provided by A. Ramanathan 
#  and D. Rao in their paper entitled
#  "A Lightweight Stemmer for Hindi"
#
#  usage:  Reads STDIN, outputs to STDOUT
#  uses utf8 encoding
#  
##################################################
#use utf8;
use Encode;

$Usage = "Usage: $0 [filename]\n";
die $Usage if ( @ARGV > 1 or ( @ARGV == 1 and ! -f $ARGV[0] ));

if ( @ARGV ) {
    open( STDIN, shift );
}
binmode STDIN, ":utf8";
binmode STDOUT, ":utf8";

# sort by string length
sub lengthly {length $b <=> length $a };

# possible suffixes
$suffixes = "\x{0906} \x{0907} \x{0908} \x{0909} \x{090a} \x{090f} \x{0913} \x{090f}\x{0902} \x{0913}\x{0902} \x{0906}\x{0902} \x{0909}\x{0906}\x{0902} \x{0909}\x{090f}\x{0902} \x{0909}\x{0913}\x{0902} \x{0906}\x{090f}\x{0902} \x{0906}\x{0913}\x{0902} \x{0907}\x{092f}\x{093e}\x{0905}\x{0902} \x{0907}\x{092f}\x{0913}\x{0902} \x{0906}\x{0907}\x{092f}\x{093e}\x{0905}\x{0902} \x{0906}\x{0902}\x{0939} \x{0906}\x{0907}\x{092f}\x{0913}\x{0902} \x{0907}\x{092f}\x{093e}\x{0905}\x{0902}\x{0939} \x{0906}\x{0907}\x{092f}\x{093e}\x{0905}\x{0902}\x{0939} \x{0905}\x{0924}\x{093e}\x{0905}\x{090f}\x{0902} \x{0905}\x{0924}\x{093e}\x{0905}\x{0913}\x{0902} \x{0905}\x{0928}\x{093e}\x{0905}\x{090f}\x{0902} \x{0905}\x{0928}\x{093e}\x{0905}\x{0913}\x{0902} \x{0905}\x{0924}\x{093e}\x{0905} \x{0905}\x{0924}\x{0948}\x{0907} \x{0908}\x{0902} \x{0905}\x{0924}\x{0948}\x{0907}\x{0902} \x{0905}\x{0924}\x{090f} \x{0906}\x{0924}\x{093e}\x{0905} \x{0906}\x{0924}\x{0948}\x{0907} \x{0906}\x{0924}\x{0948}\x{0907}\x{0902} \x{0906}\x{0924}\x{090f} \x{0905}\x{0928}\x{093e}\x{0905} \x{0905}\x{0928}\x{0948}\x{0907} \x{0905}\x{0928}\x{090f} \x{0906}\x{0928}\x{093e}\x{0905} \x{0906}\x{0928}\x{090f} \x{090a}\x{0902}\x{0917}\x{093e}\x{0905} \x{090a}\x{0902}\x{0917}\x{0948}\x{0907} \x{0906}\x{090a}\x{0902}\x{0917}\x{093e}\x{0905} \x{0906}\x{090a}\x{0902}\x{0917}\x{0948}\x{0907} \x{090f}\x{0902}\x{0917}\x{090f} \x{090f}\x{0902}\x{0917}\x{0948}\x{0907} \x{0906}\x{090f}\x{0902}\x{0917}\x{090f} \x{0906}\x{090f}\x{0902}\x{0917}\x{0948}\x{0907} \x{0913}\x{0917}\x{090f} \x{0913}\x{0917}\x{0948}\x{0907} \x{0906}\x{0913}\x{0917}\x{090f} \x{0906}\x{0913}\x{0917}\x{0948}\x{0907} \x{090f}\x{0917}\x{093e}\x{0905} \x{090f}\x{0917}\x{0948}\x{0907} \x{0906}\x{090f}\x{0917}\x{093e}\x{0905} \x{0906}\x{090f}\x{0917}\x{0948}\x{0907} \x{0906}\x{092f}\x{093e}\x{0905} \x{0906}\x{090f} \x{0906}\x{0908} \x{0906}\x{0908}\x{0902} \x{0907}\x{090f} \x{0906}\x{0913} \x{0906}\x{0907}\x{090f} \x{0905}\x{0915}\x{093e}\x{0930}\x{093e} \x{0906}\x{0915}\x{093e}\x{0930}\x{093e}";

@s = sort lengthly ( split ' ', $suffixes );
while (<>) {
    @_ = split ' ', $_;
    # candidate stems -- the code eventually picks the shortest one at the end
    foreach $w (@_) {
	$c = $w;
	foreach $stem (@s) {
	    if ($w =~ /(.+?)($stem)$/) {
		$c = $1;
		last;
	    }
	}
	print "$c ";
    }
    print "\n";
}

