FROM php:8.2-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev

# Instalar extensões PHP necessárias
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    intl

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /var/www/html

# Configurar permissões para o usuário www-data
RUN chown -R www-data:www-data /var/www/html

# Copiar configuração PHP personalizada
COPY php.ini /usr/local/etc/php/conf.d/app.ini

# Expor a porta PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]