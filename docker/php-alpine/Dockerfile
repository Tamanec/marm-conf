FROM php:7.1-alpine

RUN apk add --update openssl-dev vim \
    build-base autoconf libtool pcre-dev && \

    pecl install mongodb && docker-php-ext-enable mongodb && \
    pecl install xdebug-2.5.0 && docker-php-ext-enable xdebug && \

    apk del --purge *-dev build-base autoconf libtool && \
    rm -rf /usr/include/php7 /usr/lib/php7/build /usr/lib/php7/modules/*.a

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/bin/composer

COPY php-error.ini /usr/local/etc/php/conf.d/php-error.ini
RUN touch /var/log/php_error.log