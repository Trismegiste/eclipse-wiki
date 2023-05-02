#!/bin/bash
composer install
bin/console cache:clear
bin/console auth:get-link --qrcode
bin/console p:d &
symfony server:stop
symfony server:start --no-tls
