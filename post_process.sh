# See: http://www.pement.org/sed/sed1line.txt

# remove duplicate lines
sed -e 'n;d' file

# remove everything between parentheses
sed -e 's/\[[^][]*\]//g' file