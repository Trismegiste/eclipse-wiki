#!/bin/bash
composer install
bin/console a:l
bin/console p:d &
symfony server:start --no-tls