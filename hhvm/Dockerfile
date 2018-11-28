FROM debian:jessie

# Install Apache, PHP, and PHP modules
RUN apt-get update \
    && apt-get -y install apache2 curl

RUN apt-key adv --recv-keys --keyserver hkp://keyserver.ubuntu.com:80 0x5a16e7281be7a449 \
&& echo deb http://dl.hhvm.com/debian jessie main | tee /etc/apt/sources.list.d/hhvm.list \
&& apt-get update \
&& apt-get install hhvm -y

#RUN apt-get install -y ssmtp
#COPY ./ssmtp.conf /etc/ssmtp/ssmtp.conf

RUN apt-get install -y lynx

RUN a2enmod proxy proxy_fcgi

# Install Magerun
ENV N98_MAGERUN_VERSION 1.96.1
ENV N98_MAGERUN_URL https://raw.githubusercontent.com/netz98/n98-magerun/$N98_MAGERUN_VERSION/n98-magerun.phar

RUN curl -o /usr/local/bin/mt $N98_MAGERUN_URL \
    && chmod +x /usr/local/bin/mt

# Enable rewrite
RUN a2enmod rewrite

COPY apache2.conf /etc/apache2
COPY php.ini /etc/hhvm

EXPOSE 80

COPY docker_entrypoint.sh /docker_entrypoint.sh
RUN chmod +x docker_entrypoint.sh

ENTRYPOINT /docker_entrypoint.sh
