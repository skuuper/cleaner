Text processing

- Remove double empty lines, the remaining lines are paragraphs


Remove newline if next does not start with number

sed -n '$!{ 1{x;d}; H}; ${ H;x;s|\n\([^0-9]\)| \1|g;p}' parsed.txt > t.txt
sed -n '$!{ 1{x;d}; H}; ${ H;x;s|\n\([^0-9|(]| \1|g;p}' parsed.txt > t.txt
