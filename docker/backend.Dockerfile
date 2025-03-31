FROM php:8.2-fpm

# Instalar dependências e extensões em uma única camada
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuração PHP
ENV PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=20M \
    PHP_POST_MAX_SIZE=20M \
    PHP_MAX_EXECUTION_TIME=120

# Configuração para produção
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "date.timezone = UTC" >> "$PHP_INI_DIR/php.ini" \
    && echo "memory_limit = ${PHP_MEMORY_LIMIT}" >> "$PHP_INI_DIR/php.ini" \
    && echo "upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}" >> "$PHP_INI_DIR/php.ini" \
    && echo "post_max_size = ${PHP_POST_MAX_SIZE}" >> "$PHP_INI_DIR/php.ini" \
    && echo "max_execution_time = ${PHP_MAX_EXECUTION_TIME}" >> "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Define um usuário não-root
RUN useradd -ms /bin/bash appuser && \
    chown -R appuser:appuser /var/www/html

USER appuser

# O comando será sobrescrito no docker-compose
CMD ["php-fpm"]