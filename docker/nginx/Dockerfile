# ./docker/proxy/Dockerfile
# Este é o único Dockerfile que o serviço 'proxy' precisa.

FROM nginx:alpine

# Instala o 'netcat' para os testes de conexão do entrypoint
RUN apk add --no-cache netcat-openbsd

# Copia o script de entrypoint que espera pelos outros serviços
COPY ./docker/nginx/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

# Define o script como o ponto de entrada do contêiner
ENTRYPOINT ["/docker-entrypoint.sh"]