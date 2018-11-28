FROM php:7.1-fpm

USER root

# Installs Base Libraries

RUN apt-get update && apt-get -y install \
    libc-client-dev \
    libkrb5-dev \
    libxml2-dev \
    libmcrypt4 \
    libmcrypt-dev \
    libxslt-dev \
    libicu-dev \
    libpng-dev \
    zlib1g-dev  \
    libcurl4-openssl-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpng-dev \
    nano \
    vim \
    ack-grep \
    wget \
    git \
    libpq-dev
    
RUN docker-php-ext-configure gd \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

RUN docker-php-ext-configure bcmath
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl

# Installs PHP Libs

RUN docker-php-ext-install \
    mbstring \
    mcrypt \
    dom \
    bcmath \
    imap \
    xsl \
    intl \
    gd \
    curl \
    mysqli \
    pdo_mysql \
    soap \
    zip \
    ftp \
    pdo_pgsql \
    pgsql

#COPY config/php.ini /usr/local/etc/php/
COPY config/www.conf /usr/local/etc/php-fpm.d/

RUN yes | pecl install xdebug

RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_port=9004" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_log=/var/www/php/xdebug/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.max_nesting_level=1500" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-enable xdebug