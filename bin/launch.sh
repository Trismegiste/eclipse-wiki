#!/bin/bash
composer install
bin/console cache:clear
bin/console ass:com
bin/console auth:get-link --qrcode
bin/console p:d &
cd /app/public
frankenphp php-server