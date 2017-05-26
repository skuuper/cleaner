#!/usr/bin/perl
#
# Author: Xiaoyi Ma at the LDC, 06/06/2003
# Purpose: given sentences of both sides and an alignment file
#          merge_alignment.pl merge two sides together and 
#          print an easy-to-read output
# Usage: merge_alignment.pl [hod] X_sentence_file Y_sentence_file aligment_result
#        X_sentence_file: files contains all X sentences, indicated by 
#           <seg id=###> </seg>
#        the seg ids should be sequential numbers, starting from one.
#        Y_sentence_file: files contains all Y sentences, indicated by
#           <seg id=###> </seg>
#        the seg ids should be sequential numbers, starting from one.
#        alignment_result: alignment file, one alignment per line

use Getopt::Std;

getopts('hod', \%opts) || usage();
usage() if $opts{h};

$printomission = $opts{o};
$debug = $opts{d};
usage() if @ARGV != 3;

($efn, $cfn, $align) = @ARGV;


open E, "<$efn" or die "$0: can not open $efn\n";
open C, "<$cfn" or die "$0: can not open $cfn\n";
open A, "<$align" or die "$0: can not open $align\n";

$docid = `basename $cfn`;
$docid =~ s/\..+//;
chomp $docid;

while(<E>) {
    chomp;
    if (/<seg id=(\d+)>(.*)<\/seg>/) {
	$ln = $1;
	$es = $2;
	$eline{$ln} = $es;
    }
}

while(<C>) {
    chomp;
    if (/<seg id=(\d+)>(.*)<\/seg>/) {
	$ln = $1;
        $cs = $2;
	$cline{$ln} = $cs;
    }
}


print "<DOC docid=$docid>\n";

while(<A>){
    chomp;
    next unless / <=> /;
    unless ($printomission) {
	next if /omitted/;
    }
    
    /(.+) <=> (.+)/;
    $esent = $1; $csent = $2;

    if ($debug) {
	print "\n$_\n";
    }

    if ($esent =~ /omitted/) {
	$etype = "0";
	undef @esent;
    } else {
	@esent = split /,/, $esent;
	$etype = @esent;
    }

    if ($csent =~ /omitted/) {
	$ctype = "0";
	undef @csent;
    } else {
	@csent = split /,/, $csent;
	$ctype = @csent;
    }

    print "<SENT type=$etype-$ctype>\n";

    foreach (@esent) {
	print $eline{$_}," ";
    }
    print "\n";

    foreach (@csent) {
	print $cline{$_};
    }
    print "\n</SENT>\n";

}

print "</DOC>\n";
exit;

sub usage() {
    print STDERR << "EOF";
usage: $0 [-hod] <X file> <Y file> <alignment file>
      
      -h  : this (help) message
      -o  : print deletion and insertion (default is no).
      -d  : debug mode, prints alignment as well. (default is no)

EOF

       exit;
}
