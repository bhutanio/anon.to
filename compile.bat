@ECHO off
ECHO Make sure you have gulp and yuicompressor node package installed
ECHO If you dont have, install it by npm -g install gulp yuicompressor
ECHO -----------------------------------------------------------
ECHO --- Updating NPM and BOWER
ECHO -----------------------------------------------------------
CALL npm update -g
CALL bower update
CALL npm update
ECHO -----------------------------------------------------------
ECHO --- gulping it up
ECHO -----------------------------------------------------------
CALL gulp --production
ECHO -----------------------------------------------------------
ECHO --- Minifying using yuicompressor
CALL yuicompressor --type js public/js/app.js -o public/js/app.js
CALL yuicompressor --type css public/css/style.css -o public/css/style.css
ECHO --- Completed!
ECHO -----------------------------------------------------------
EXIT /B %errno%
