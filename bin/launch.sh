#!/bin/bash

# To fix error log in Chromium
mkdir /run/dbus
dbus-daemon --system
dbus-daemon --session --fork --print-address 1 > /tmp/socket-dbus.txt
export DBUS_SESSION_BUS_ADDRESS=$(cat /tmp/socket-dbus.txt)
# Need to replace previous line ?
# export DBUS_SESSION_BUS_ADDRESS=`dbus-daemon --fork --config-file=/usr/share/dbus-1/session.conf --print-address`
# /etc/init.d/dbus restart

# Starting PHP app
composer install
bin/console cache:clear
bin/console ass:com
bin/console auth:get-link --qrcode
frankenphp run --config /app/docker/Caddyfile