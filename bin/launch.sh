#!/bin/bash
composer install
bin/console cache:clear
bin/console ass:com
bin/console auth:get-link 80 --qrcode
bin/console p:d &
frankenphp run --config /etc/caddy/Caddyfile