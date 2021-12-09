#!/bin/bash

# you've to install strip-json-comment-cli : npm install --global strip-json-comments-cli

#rm src/locale/datatables/*.lang1*
cd locale

for d in ../src/locale/datatables/* ; do
   res="$d"

   echo $res

   strip-json-comments $res > "${res}1"  
   
   rm $res
   mv "${res}1" $res
done
