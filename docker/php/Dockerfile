# ./docker/php/Dockerfile

FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Garante que 'postgresql-client' seja instalado junto com outras dependências
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libpq-dev \
    postgresql-client \
    tzdata

# --- CONFIGURAÇÃO DE FUSO HORÁRIO ---
# Define a variável de ambiente para o fuso horário
ENV TZ=America/Sao_Paulo
# Cria o link simbólico para configurar o fuso horário do sistema
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
# --- FIM DA CONFIGURAÇÃO DE FUSO HORÁRIO ---

# Instala as extensões PHP
RUN docker-php-ext-install pdo pdo_pgsql zip intl

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos do composer para a imagem
COPY supp-atendimento-back-end/composer.json supp-atendimento-back-end/composer.lock* ./

# Roda o composer install para criar a pasta /vendor dentro da imagem
RUN composer install --prefer-dist --no-scripts --no-autoloader

# Copia o restante do código-fonte
COPY supp-atendimento-back-end/ /var/www/html/

# Otimiza o autoloader com o código-fonte completo
RUN composer dump-autoload --optimize

# Copia e dá permissão ao script de entrypoint
COPY ./docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Garante permissões corretas para o diretório var (cache, logs, doctrine proxies)
RUN chmod -R 777 /var/www/html/var/

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php-fpm"]