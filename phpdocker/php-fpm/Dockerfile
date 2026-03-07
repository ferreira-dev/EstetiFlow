FROM phpdockerio/php:8.2-fpm
WORKDIR "/application"

RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        php8.2-bcmath \
        php8.2-bz2 \
        php8.2-gd \
        php8.2-http \
        php8.2-mysql \
        php8.2-redis \
        php8.2-ssh2 \
        php8.2-swoole \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
