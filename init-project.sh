#!/bin/bash

# Nome do arquivo docker-compose de produção (ajuste se necessário)
COMPOSE_FILE="docker-compose-docker.yml"

# Mensagem inicial
echo "Iniciando a construção e inicialização do projeto em ambiente de PRODUÇÃO..."

# Criar estrutura de diretórios para Docker (opcional, pode remover se não precisar)
mkdir -p docker/{php,nginx,node}

# Iniciar os contêineres em modo detached (-d) usando o docker-compose de produção
echo "Iniciando os contêineres Docker com docker-compose -f ${COMPOSE_FILE} up -d"
docker-compose -f ${COMPOSE_FILE} up -d

# Aguardar alguns segundos para os contêineres iniciarem (ajuste conforme necessário)
echo "Aguardando 10 segundos para os contêineres iniciarem..."
sleep 10

# Executar migrations do Doctrine no backend (IMPORTANTE para produção)
echo "Executando as migrations do Doctrine no backend..."
docker-compose -f ${COMPOSE_FILE} exec backend php bin/console doctrine:migrations:migrate --no-interaction

# Remover a parte de fixtures para produção (NÃO USAR FIXTURES EM PRODUÇÃO!)
# echo "Carregando os dados iniciais (fixtures)..."
# docker-compose -f ${COMPOSE_FILE} exec backend php bin/console doctrine:fixtures:load --no-interaction

# Remover configuração do frontend, pois o build é feito no Dockerfile para produção
# echo "Configurando o frontend Vue.js..."
# docker-compose -f ${COMPOSE_FILE} exec frontend npm install


echo "Inicialização de produção concluída!"
echo "Projeto backend (API) disponível (verifique a configuração do seu webserver e DNS): http://seu_dominio_backend.com ou IP do servidor"
echo "Projeto frontend disponível (verifique a configuração do seu webserver/CDN ou se expôs diretamente): http://seu_dominio_frontend.com ou IP do servidor (porta 80 se expôs diretamente)"
# Removido links de desenvolvimento como PgAdmin e Mailhog, pois não são de produção

echo "Verifique os logs dos contêineres com 'docker-compose -f ${COMPOSE_FILE} logs -f' se precisar depurar."