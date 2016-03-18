#!/bin/bash
# Assets Compiler
echo "Make sure you have gulp and yuicompressor node package installed"
echo "If you dont have, install it by npm -g install gulp yuicompressor"
echo "-----------------------------------------------------------"
echo "--- Updating NPM and BOWER"
echo "-----------------------------------------------------------"
npm update -g
bower update
npm update
echo "-----------------------------------------------------------"
echo "--- gulping it up"
echo "-----------------------------------------------------------"
gulp --production
echo "-----------------------------------------------------------"
echo "--- Minifying using yuicompressor"
yuicompressor --type js public/js/app.js -o public/js/app.js
yuicompressor --type css public/css/style.css -o public/css/style.css
echo "--- Completed!"
echo "-----------------------------------------------------------"