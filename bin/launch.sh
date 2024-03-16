#!/bin/bash

# To fix error log in Chromium
mkdir /run/dbus
dbus-daemon --system
dbus-daemon --session --fork --print-address 1 > /tmp/socket-dbus.txt
export DBUS_SESSION_BUS_ADDRESS=$(cat /tmp/socket-dbus.txt)

# Starting PHP app
composer install
bin/console cache:clear
bin/console ass:com
bin/console auth:get-link --qrcode
frankenphp run --config /app/docker/Caddyfile