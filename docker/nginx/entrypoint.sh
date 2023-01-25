#!/usr/bin/env sh

envsubst '$NGINX_HOST_PATH,$NGINX_PHP_FPM_HOST' < /etc/nginx/default.conf > /etc/nginx/conf.d/default.conf

nginx -g "daemon off;"
