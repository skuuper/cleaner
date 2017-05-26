#!/usr/bin/perl -w

use strict;

# This is Kareem Darwish's stem_cp1256.pl modified by
# Leah Larkey, Alexander Fraser and Xiaoyi Ma

my $atb_stems = "$ENV{CTK}/lib/atb_stem_tbl.txt";
my %stem;

open S, "<$atb_stems" || die "$0: Cannot open $atb_stems\n";
while (<S>) {
    chomp;
    if (/^(.+)\s+(.+)$/) {
	$stem{$1} = $2;
    }
}
close S;

while (<>) {
    chomp;
    # split on spaces and punctuation
    s/،/\,/g;
    s/؟/\?/g;

    # split on spaces since tokenization was done by atoken.pl
    my @tokens = split ' ', $_;
    for my $token (@tokens) {
      # remove all non-letters (diacritics, punctuation)
      my $newtoken = "";
      while ($token =~ /\G.*?((ء|آ|أ|ؤ|إ|ئ|ا|ب|ة|ت|ث|ج|ح|خ|د|ذ|ر|ز|س|ش|ص|ض|ط|ظ|ع|غ|ف|ق|ك|ل|م|ن|ه|و|ي|ى|[\x21-\x7E])+)/g) {
	$newtoken .= $1;
      }
      $token = $newtoken;

      # normalize ya and Alef Maqsoura
      $token =~ s/ى/ي/g;

      # normalizing different alef-maad, alef-hamza-top,
      # alef-hamza-bottom, bare-alef you can choose between light and
      # aggressive normalization.  The default is aggressive.

      # light normalization
      # $token =~ s/(آ|أ|إ)/ا/g;
      # aggressive normalization
      $token =~ s/(ء|آ|أ|ؤ|إ|ئ)/ا/g;

      if (exists $stem{$token}) {
	  print "$stem{$token} ";
	  next;
      }# else {
#	  print STDERR "$token\n";
#      }


      # this regexp will match every string. It tries to take the longest
      # possible prefix and suffix. $2 will always be defined but can be empty.
      if ($token =~ /^(وال|فال|بال|بت|يت|لت|مت|تت|وت|ست|نت|بم|لم|وم|كم|فم|ال|لل|وي|لي|سي|في|وا|فا|لا|با)(.+)$/) {
	  $token = $2;
      }
      while ($token =~ /^(.+)(ات|وا|تا|ون|وه|ان|تي|ته|تم|كم|هن|هم|ها|ية|تك|نا|ين|يه|ة|ه|ي|ا)$/) {
	  $token = $1;
      }
#    if ($token =~ /^(وال|فال|بال|بت|يت|لت|مت|تت|وت|ست|نت|بم|لم|وم|كم|فم|ال|لل|وي|لي|سي|في|وا|فا|لا|با|)(.+?)(ات|وا|تا|ون|وه|ان|تي|ته|تم|كم|هن|هم|ها|ية|تك|نا|ين|يه|ة|ه|ي|ا)$/) {
#	  print "$2 ";
#      } else {
#	  print "$token ";
#      }
      print "$token ";
  }
    print "\n";
}

## Saved for possible future use
## remove diacritics and kashida
#s/(ً|ٌ|ٍ|َ|ُ|ِ|ّ|ْ|ـ)//g;
