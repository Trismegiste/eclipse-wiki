#!/bin/bash
composer install
bin/console cache:clear
bin/console a:l
bin/console p:d &
symfony server:start --no-tls