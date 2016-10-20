#!/bin/bash
HOST=$1
PORT=$2
USERNAME=$3
DATABASE=$4
PASSWORD=$5
STORAGE=$6
FOLDER=$7
CATEGORY=$8
FILENAME=$CATEGORY'-'`date +%Y_%m_%d`

export PGPASSWORD=$PASSWORD
mkdir -p $STORAGE/$FOLDER
mkdir -p $STORAGE/'download'

IFS=$'\n'

for con in $(echo "$(psql -X -d $DATABASE -U $USERNAME -h $HOST -p $PORT -t -c "SELECT id,metadata->>'open_contracting_id' from contracts where metadata->'category'->>0='${CATEGORY}'")"|xargs -n3)
  do
    contractID=$(echo $con | awk '{print $1}')
    OCID=$(echo $con | awk '{print $3}')
    psql -X -d $DATABASE -U $USERNAME -h $HOST -p $PORT -t -A -F"," -c "select text from contract_pages where contract_id=${contractID}"  > $STORAGE/$FOLDER/${OCID}.txt
    sed -i -e 's/<br>/ /g;s/<br >/ /g;s/<br \/>/ /g;s/<br>/ /g' $STORAGE/$FOLDER/${OCID}.txt
done

cd $STORAGE/$FOLDER
zip -r -q $STORAGE/'download'/$FILENAME'.zip' ./*

cd /
cd $STORAGE/'download'

FILE_NAME=$FILENAME'.zip'
FILE_SIZE=$(wc -c $FILE_NAME | awk '{print $1}')
echo "Size of $FILE_NAME = $FILE_SIZE bytes."

FINAL_FILE_NAME=$FILENAME-${FILE_SIZE}'.zip'
mv $FILE_NAME $FINAL_FILE_NAME

cd /
cd $STORAGE
rm -rf $FOLDER