#!/bin/bash

# Starting PHP app
composer install
bin/console cache:clear
bin/console ass:com
bin/console auth:get-link --qrcode
frankenphp run --config /app/docker/web/Caddyfile