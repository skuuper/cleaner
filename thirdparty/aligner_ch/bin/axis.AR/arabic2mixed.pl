#!/usr/bin/perl -p
#
# Purpose: convert Arabic-Indic digits and Arabic
#          punctuations to English equivalents
#

# convert Arabic-Indic digits to Roman digits
s/\xD9\xA0|\xDB\xB0/0/g;
s/\xD9\xA1|\xDB\xB1/1/g;
s/\xD9\xA2|\xDB\xB2/2/g;
s/\xD9\xA3|\xDB\xB3/3/g;
s/\xD9\xA4|\xDB\xB4/4/g;
s/\xD9\xA5|\xDB\xB5/5/g;
s/\xD9\xA6|\xDB\xB6/6/g;
s/\xD9\xA7|\xDB\xB7/7/g;
s/\xD9\xA8|\xDB\xB8/8/g;
s/\xD9\xA9|\xDB\xB9/9/g;

# convert Arabic punctuations to English puctuations
s/\xD8\x9F/\?/g;
s/\xD9\xAA/\%/g;
s/\xD9\xAB/\./g; # Arabic decimal separator
s/\xD9\xAC/\,/g; # Arabic thousand separator
s/\xD8\x9B/\;/g;
s/\xD8\x8C/\,/g;


