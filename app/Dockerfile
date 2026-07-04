FROM php:8.2-apache

ENV TZ=America/Santiago

RUN apt-get update \
    && apt-get install -y --no-install-recommends tzdata \
    && docker-php-ext-install pdo pdo_mysql \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone \
    && echo "date.timezone=$TZ" > /usr/local/etc/php/conf.d/timezone.ini \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
