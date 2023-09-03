FROM php:8.1-cli

# Ext MongoDb for PHP
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Ext GD
RUN apt-get update && apt-get install -y libpng-dev libwebp-dev libjpeg-dev libfreetype6-dev
RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype
RUN docker-php-ext-install gd

# Ext XSL
RUN apt-get install -y libxslt-dev && docker-php-ext-install xsl

# Ext ZIP
RUN apt-get install -y libzip-dev && docker-php-ext-install zip

# INTL
RUN docker-php-ext-install intl

# PDF
RUN apt-get install -y wkhtmltopdf

# OPCache
RUN docker-php-ext-install opcache

# Socket
RUN docker-php-ext-install sockets

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt -y install symfony-cli

# Set the locale
RUN apt -y install locales
RUN sed -i '/en_US.UTF-8/s/^# //g' /etc/locale.gen && \
    locale-gen
ENV LANG en_US.UTF-8  
ENV LANGUAGE en_US:en  
ENV LC_ALL en_US.UTF-8 

RUN apt-get install -y imagemagick

RUN apt-get update && apt-get install -y chromium

RUN apt-get update && apt-get install -y pip
RUN pip install --break-system-packages -U pdf.tocgen

RUN apt-get update && apt-get install -y unzip

RUN apt-get install -y net-tools

COPY ./docker/php.ini /usr/local/etc/php

EXPOSE 8000
EXPOSE 9000

WORKDIR /www

CMD echo "bin/launch.sh" | bash
