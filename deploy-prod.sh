#!/bin/bash

# Script de implantação para ambiente de produção

set -e  # Encerra o script se qualquer comando falhar

# Variáveis de ambiente
ENV_FILE=".env.prod"

# Verifica se o arquivo de variáveis de ambiente existe
if [ ! -f "$ENV_FILE" ]; then
    echo "Erro: Arquivo $ENV_FILE não encontrado."
    exit 1
fi

# Carrega variáveis de ambiente
source "$ENV_FILE"

echo "🚀 Iniciando implantação em produção..."

# Verifica se o Docker está instalado
if ! command -v docker &> /dev/null || ! command -v docker-compose &> /dev/null; then
    echo "Erro: Docker e/ou Docker Compose não estão instalados."
    exit 1
fi

# Faz backup do banco de dados antes da atualização
echo "📦 Realizando backup do banco de dados..."
./backup-db.sh

# Parar todos os contêineres atuais
echo "🛑 Parando contêineres em execução..."
docker-compose -f docker-compose.prod.yml down

# Construir as imagens para produção
echo "🏗️ Construindo imagens para produção..."
docker-compose -f docker-compose.prod.yml build

# Iniciar os serviços
echo "🚀 Iniciando serviços..."
docker-compose -f docker-compose.prod.yml up -d

# Verificar se os contêineres estão em execução
echo "🔍 Verificando status dos contêineres..."
docker-compose -f docker-compose.prod.yml ps

# Executar migrações do banco de dados
echo "🔄 Executando migrações do banco de dados..."
docker-compose -f docker-compose.prod.yml exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Limpar cache
echo "🧹 Limpando cache..."
docker-compose -f docker-compose.prod.yml exec backend php bin/console cache:clear --env=prod

# Aquecer o cache
echo "🔥 Aquecendo o cache..."
docker-compose -f docker-compose.prod.yml exec backend php bin/console cache:warmup --env=prod

# Configurar permissões
echo "🔒 Configurando permissões..."
docker-compose -f docker-compose.prod.yml exec backend chown -R www-data:www-data var public/uploads

echo "✅ Implantação concluída com sucesso!"
echo "   A aplicação está disponível em: https://yourdomain.com"