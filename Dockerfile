FROM phpdockerio/php:8.3-fpm
WORKDIR "/app"

# Установка дополнительных пакетов и инструментов
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        git \ 
        php8.3-gd \ 
        php8.3-mysql \ 
        php8.3-pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
