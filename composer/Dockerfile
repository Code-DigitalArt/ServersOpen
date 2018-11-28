FROM php:7.1-alpine
RUN apk update \
    && apk upgrade \
    && apk add --no-cache bash \
    libxml2-dev \
    libmcrypt-dev \
    libxslt-dev \
    libpng-dev \
    icu-dev \
    nano \
    vim \
    wget \
    git \
    curl \
    subversion \
    openssh \
    openssl \
    mercurial \
    bash \
    tini 

    
RUN docker-php-ext-configure gd \
    --with-png-dir=/usr/include/

RUN docker-php-ext-configure bcmath

# Installs PHP Libs

RUN docker-php-ext-install \
    mbstring \
    mcrypt \
    dom \
    bcmath \
    xsl \
    intl \
    gd \
    mysqli \
    pdo_mysql \
    soap \
    zip 

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=composer:latest /docker-entrypoint.sh /docker-entrypoint.sh

WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["composer"]

