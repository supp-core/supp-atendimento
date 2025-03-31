#!/bin/bash
set -e

echo "Iniciando os containers..."
docker-compose up -d

echo "Aguardando database estar pronto..."
sleep 5

echo "Instalando dependências do backend..."
docker-compose exec backend composer install

echo "Executando migrações..."
docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

echo "Carregando fixtures..."
docker-compose exec backend php bin/console doctrine:fixtures:load --no-interaction

echo "Criando diretório de uploads..."
docker-compose exec -u 0 backend mkdir -p public/uploads/attachments
docker-compose exec -u 0 backend chmod -R 777 public/uploads/attachments

echo "====================================="
echo "Aplicação inicializada com sucesso!"
echo "Backend: http://localhost:8000"
echo "Frontend: http://localhost:5173"
echo "PgAdmin: http://localhost:5050"
echo "Mailhog: http://localhost:8025"
echo "====================================="