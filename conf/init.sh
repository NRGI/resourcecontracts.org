#!/bin/sh

#IMPORTANT!: Note that we do envsubst for all env variables for env.template. Take care with '$' characters in env.template, strings that start with $ will be treated like env vars.

#rc-admin
envsubst < ./env.template > /var/www/rc-admin/.env

#log_files
envsubst '${DEPLOYMENT_TYPE}' < ./log_files.yml.template > /etc/log_files.yml

#pdf-processor
envsubst < ./settings.config.template > /var/www/pdf-processor/settings.config

#add mturk/updates cronjob
echo "* * * * * www-data cd /var/www/rc-admin && /usr/bin/php artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/mturk_and_updates
cron restart
