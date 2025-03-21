#!/bin/bash

# Criar estrutura de diretórios para Docker
mkdir -p docker/{php,nginx,node}

# Iniciar os contêineres
docker-compose up -d

# Configurar o backend Symfony
echo "Configurando o backend Symfony..."
docker-compose exec backend composer install
docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Carregar os fixtures
echo "Carregando os dados iniciais (fixtures)..."
docker-compose exec backend php bin/console doctrine:fixtures:load --no-interaction


# Configurar o frontend Vue.js
echo "Configurando o frontend Vue.js..."
docker-compose exec frontend npm install

echo "Inicialização concluída!"
echo "Backend disponível em: http://localhost:8000"
echo "Frontend disponível em: http://localhost:5173"
echo "PgAdmin disponível em: http://localhost:5050"
echo "Mailhog disponível em: http://localhost:8025"