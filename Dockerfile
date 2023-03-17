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
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN composer self-update

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

COPY ./docker/php.ini /usr/local/etc/php

EXPOSE 8000
EXPOSE 9000

WORKDIR /www

CMD echo "bin/launch.sh" | bash