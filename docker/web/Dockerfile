FROM dunglas/frankenphp:1.4.4-php8.3-bookworm

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

# OPCache
RUN docker-php-ext-install opcache

# Socket
RUN docker-php-ext-install sockets

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
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

RUN apt-get update && apt-get install -y pip
RUN pip install --break-system-packages -U pdf.tocgen

RUN apt-get update && apt-get install -y unzip

RUN apt-get install -y net-tools

COPY ./php.ini /usr/local/etc/php

# compil brandes algorithm
RUN git clone https://github.com/m-chrzan/brandes.git /root/brandes
RUN apt install -y cmake
RUN cd /root/brandes/ && mkdir build
RUN cd /root/brandes/build/ && cmake .. && make && mv brandes /usr/bin/
RUN cd /root && rm -rf brandes

### FOR testing PDF
RUN apt install -y poppler-utils

WORKDIR /app

CMD bash -c "bin/launch.sh"
