#!/bin/sh
set -e

# O 'composer install' foi removido daqui!
# Ele agora é feito na construção da imagem.
chown -R www-data:www-data /var/www/html/var
# --- Aguarda o PostgreSQL ficar pronto ---
DB_HOST=$(echo $DATABASE_URL | sed -E 's/.*@([^:]+):.*/\1/')
DB_USER=$(echo $DATABASE_URL | sed -E 's/.*:\/\/([^:]+):.*/\1/')
echo "Backend entrypoint: Aguardando o PostgreSQL no host '$DB_HOST'..."
until pg_isready --host="$DB_HOST" --username="$DB_USER" --quiet; do
  echo "PostgreSQL não está pronto. Tentando novamente em 2 segundos..."
  sleep 2
done
echo "PostgreSQL está pronto! Continuando com a inicialização."

# --- Configuração do Banco de Dados ---
# AQUI ESTÁ A GRANDE MUDANÇA!
echo "Removendo e recriando o banco de dados para um estado limpo..."

# 1. Dropar o banco de dados (se ele existir)
# A flag --if-exists previne erros se for a primeira execução
php bin/console doctrine:database:drop --force --if-exists

# 2. Criar o banco de dados novamente
php bin/console doctrine:database:create

echo "Banco de dados recriado com sucesso."

# 3. Executar as migrações no banco de dados agora vazio
echo "Executando as migrações do banco de dados..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# 4. Carregar os dados de teste (fixtures)
if php bin/console list | grep -q 'doctrine:fixtures:load'; then
    echo "DoctrineFixturesBundle encontrado. Carregando dados de teste (fixtures)..."
    php bin/console doctrine:fixtures:load --no-interaction --purge-with-truncate # Manter a flag é uma boa prática
else
    echo "DoctrineFixturesBundle não instalado. Pulando o carregamento de fixtures."
fi

echo "Banco de dados configurado com sucesso."

# --- Execução do Comando Principal ---
exec "$@"