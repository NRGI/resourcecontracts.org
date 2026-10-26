#!/bin/sh
set -e

# IMPORTANT: envsubst substitutes every ${VAR} in the templates. Take care with '$' characters.

cd /var/container_init

# rc-admin .env
envsubst < ./env.template > /var/www/rc-admin/.env

# Papertrail log shipping
envsubst '${DEPLOYMENT_TYPE}' < ./log_files.yml.template > /etc/log_files.yml

# pdf-processor
envsubst < ./settings.config.template > /var/www/pdf-processor/settings.config

# Laravel caches (config is derived from the generated .env, so cache at container start, not build time)
cd /var/www/rc-admin
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data: bootstrap/cache

# Scheduler (MTurk / codelist updates)
echo "* * * * * www-data /usr/bin/php /var/www/rc-admin/artisan schedule:run 1>> /dev/null 2>&1" > /etc/cron.d/mturk_and_updates
chmod 0644 /etc/cron.d/mturk_and_updates
service cron start

/etc/init.d/beanstalkd start
supervisord -c /etc/supervisord.conf

exec /usr/sbin/apache2ctl -D FOREGROUND
