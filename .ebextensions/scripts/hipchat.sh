#!/bin/bash
HIPCHAT_ROOM=xxxxx
HIPCHAT_TOKEN=xxxxx
HIPCHAT_COLOR="yellow"     		#yellow, green, red, purple, gray, random.
HIPCHAT_MESSAGE=""
HIPCHAT_NOTIFY=true 			#true, false
HIPCHAT_MESSAGE_FORMAT="html"   #html, text.
NOW=$(TZ=":Asia/Kathmandu" date +"%r")
ENVIRONMENT=''
while getopts "ht:u:p:" opt
do
  case $opt
  in
    h)
      echo "Help:"
      echo ""
      echo "  -h                - This help screen"
      echo "  -t TYPE           - Type of message (pre or post)"
      echo "  -u USER           - User"
      echo "  -p ENVIRONMENT    - Environment"

      exit 0
      ;;
    t)
      TYPE=$OPTARG
      ;;
    u)
      USER=$OPTARG
      ;;
    p)
      ENVIRONMENT=$OPTARG
      ;;
    \?)
      echo "Invalid option: -$OPTARG" >&2
      exit 1
      ;;
  esac
done

if [ "$TYPE" = "post" ]
then
  HIPCHAT_COLOR="green"
  HIPCHAT_MESSAGE="<strong>${USER}</strong> finished deploying rc-admin <p><strong>Environment: </strong>${ENVIRONMENT}</p> <p><strong>Time:</strong> ${NOW}</p>"
else
  HIPCHAT_MESSAGE="<strong>${USER}</strong> is deploying rc-admin <p><strong>Environment:</strong> ${ENVIRONMENT}</p> <p><strong>Time:</strong> ${NOW}</p>"
fi

curl -X POST -H "Content-Type: application/json" \
  --data "{\"color\":\"${HIPCHAT_COLOR}\", \"message\":\"${HIPCHAT_MESSAGE}\", \"message_format\":\"${HIPCHAT_MESSAGE_FORMAT}\", \"notify\":${HIPCHAT_NOTIFY}}" \
  https://api.hipchat.com/v2/room/${HIPCHAT_ROOM}/notification?auth_token=${HIPCHAT_TOKEN}
