g:\exe\perl -pi.~ -x %0 %1
exit
#!perl

BEGIN { undef $/; }
s#([^À-ß][À-ß])\. #\1\.NOBR#g;
s#([0-9])\. #\1\.NOBR#g;
s#\.\.( +)([à-ÿ¿³º])#\.\.NOBR\2#g;
s#( +)([à-ÿ¿³º])#NOBR\2#g;

s#([!?.])([»"])? ([ -]*)([à-ÿ¿³ºa-z¢¸´])#\1\2NOBR\3\4#g;
s#([!?.])([»"])? ([ -]*)([À-ß¯²ªA-Z¡¨¥])#\1\2\n\3\4#g;
s#: *([-]+)#:\n\1#g;

s#\. #\.\n#g;
s#\? #\?\n#g;
s#\! #\!\n#g;
s# #\n#g;
s#([!?][»"]) #\1\n#g;
s#(\.\.\.[»"]) #\1\n#g;
s#\t# #g;
s# +# #g;
s#\n +#\n#g;
s#\n+#\n#g;
s#NOBR# #g;
s# +# #g;
