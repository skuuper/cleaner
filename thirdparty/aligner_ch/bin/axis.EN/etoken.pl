#!/usr/bin/perl -n

use strict;

my $segid;
my $seg;

if (/<seg id=(\d+)>(.*)<\/seg>/) {
    $segid = $1; $seg = $2;

} else {
    chomp;
    $segid = "";
    $seg = $_;
}

# put space after any period that's followed by a non-number
$seg =~ s/\.(\D)/\. $1/g;
# put space before any period that's followed by a space
# the following space is introduced in the previous command
$seg =~ s/\. / \. /g;

# put space around colons and comas, unless they're surrounded by numbers
$seg =~ s/(\d)\.(\d)/$1DOTTKN$2/g;
$seg =~ s/(\d)\:(\d)/$1COLONTKN$2/g;
$seg =~ s/(\d)\,(\d)/$1COMATKN$2/g;

$seg =~ s/\W/ $& /g;

$seg =~ s/(\d)DOTTKN(\d)/$1\.$2/g;
$seg =~ s/(\d)COLONTKN(\d)/$1\:$2/g;
#$seg =~ s/(\d)COMATKN(\d)/$1\,$2/g;
$seg =~ s/(\d)COMATKN(\d)/$1$2/g;
$seg =~ s/([a-zA-Z])(\d)/$1 $2/g;
$seg =~ s/(\d)([a-zA-Z])/$1 $2/g;

if ($segid ne "") {
    print "<seg id=$segid>$seg</seg>\n";
} else {
    print "$seg\n";
}

