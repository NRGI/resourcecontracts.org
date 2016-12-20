#!/bin/sh

#IMPORTANT!: Note that we do envsubst for all env variables for env.template. Take care with '$' characters in env.template, strings that start with $ will be treated like env vars.

#rc-admin
envsubst < ./env.template > /var/www/rc-admin/.env
