FROM php:7.0-fpm

USER root

# Installs Base Libraries

RUN apt-get update && apt-get -y install \
    openssl \
    openssh-server \
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
    openssh-client \
    telnet 
    
RUN docker-php-ext-configure gd \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/

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
    pgsql

RUN yes | pecl install xdebug

RUN echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20131226/xdebug.so" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.profiler_enable=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 80
EXPOSE 9000

COPY sshd_config /etc/ssh/sshd_config
COPY config/php.ini /usr/local/etc/php/

RUN echo "root:password" | chpasswd
