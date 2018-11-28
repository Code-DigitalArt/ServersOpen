FROM php:7.1-cli
RUN apt-get update && apt-get install -y \
    libpq-dev \
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
        curl \
        subversion \
        openssh-server \
        openssl \
        mercurial \
        bash \
        libpq-dev \
    && rm -r /var/lib/apt/lists/*

RUN docker-php-ext-configure bcmath \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

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

RUN yes | pecl install xdebug

RUN echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_port=9004" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_log=/var/www/php/xdebug/xdebug.log" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.profiler_enable=0" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.max_nesting_level=1500" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN docker-php-ext-enable xdebug