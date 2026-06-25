FROM dunglas/frankenphp:1-php8.4

# Ekstensi PHP yang dibutuhkan Laravel + PostgreSQL + Redis + Media Library
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    redis \
    bcmath \
    intl \
    zip \
    gd \
    exif \
    opcache \
    pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Caddyfile khusus
COPY Caddyfile /etc/caddy/Caddyfile

# php.ini production-appropriate (di-copy dari folder yang sama, lihat catatan build context)
COPY php.ini /usr/local/etc/php/conf.d/99-falaya.ini

ENV SERVER_NAME=:80
ENV FRANKENPHP_CONFIG="worker off"

EXPOSE 80

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
