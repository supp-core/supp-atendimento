FROM php:8.2-fpm

# Instalar dependências do sistema#@##
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

# Copiar código da aplicação BACKEND para dentro da imagem
COPY supp-atendimento-back-end /var/www/html/

# Instalar dependências Composer para PRODUÇÃO
RUN composer install --no-dev --optimize-autoloader

# Criar diretório JWT e gerar as chaves
RUN mkdir -p config/jwt && \
    openssl genrsa -out config/jwt/private.pem 4096 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem && \
    chown -R www-data:www-data config/jwt && \
    chmod 644 config/jwt/private.pem config/jwt/public.pem

# Configurar permissões para o usuário www-data
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Copiar configuração PHP personalizada
COPY supp-atendimento-back-end/php.ini /usr/local/etc/php/conf.d/app.ini

# KUBERNETES: Corrigir PHP-FPM para escutar em todas as interfaces
RUN sed -i 's|listen = .*|listen = 0.0.0.0:9000|' /usr/local/etc/php-fpm.d/www.conf

# Expor a porta PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]