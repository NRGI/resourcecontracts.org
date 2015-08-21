#!/bin/bash

for file in excels/*; do mv "$file" `echo $file | tr ' ' '_'` ; done
for file in excels/*.xlsx;
  # do  xlsx2csv $i  "$i" -a  ; 
  do
    y=${file%.xlsx}
    dir=${y##*/}
    echo $dir
    xlsx2csv $file  "converted/$dir" -a -i
done

for file in excels/*.xlsm;
  # do  xlsx2csv $i  "$i" -a  ; 
  do
    y=${file%.xlsm}
    dir=${y##*/}
    echo $dir
    xlsx2csv $file  "converted/$dir" -a -i
done