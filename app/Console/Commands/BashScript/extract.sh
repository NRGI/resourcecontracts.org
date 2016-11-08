#!/bin/bash

export PGPASSWORD=$6
mkdir $5/$9
mkdir $5/${10}
mkdir $5/$7
for con in $(psql -X -d $4 -U $3 -h $1 -p $2 -t -c "SELECT id from contracts")
  do
    psql -X -d $4 -U $3 -h $1 -p $2 -t -A -F"," -c "select text from contract_pages where contract_id=${con}" > $5/$9/${con}.txt
    sed -r 's/<br \/>//g' $5/$9/${con}.txt > $5/${10}/${con}.txt
done
zip -r $5/$8 $5/${10}
mv $5/$8 $5/$7/




