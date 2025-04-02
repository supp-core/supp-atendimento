# FROM nginx:stable-alpine

# # Remover configuração padrão
# RUN rm /etc/nginx/conf.d/default.conf

# # Copiar nova configuração
# COPY supp-atendimento-back-end/nginx.conf /etc/nginx/conf.d/

# EXPOSE 80


#Docker modificado para ambiente azure#
# ./docker/nginx/Dockerfile (Adaptado para Produção)

FROM node:18-alpine AS builder

# Defina o diretório de trabalho dentro do container de build
WORKDIR /app/frontend

# Copie os arquivos de definição de dependência PRIMEIRO para aproveitar o cache do Docker
# O '../..' sobe dois níveis a partir de ./docker/nginx para chegar à raiz do projeto
COPY supp-atendimento-front-end/package.json supp-atendimento-front-end/package-lock.json* ./

# Instale as dependências
# 'npm ci' é geralmente preferido em builds por usar estritamente o lockfile e ser mais rápido
RUN npm ci

# Copie o restante do código fonte do frontend
COPY supp-atendimento-front-end/ ./

# Execute o comando de build
RUN npm run build
# Agora temos a pasta /app/frontend/dist dentro desta etapa 'builder'


# --- Stage 2: Configuração do Nginx ---
# Use a imagem Nginx final
FROM nginx:alpine

# Remover configuração padrão
RUN rm /etc/nginx/conf.d/default.conf

# Copiar a configuração Nginx específica para produção
# O './nginx.prod.conf' é relativo à localização DESTE Dockerfile
COPY ./docker/nginx/nginx.prod.conf /etc/nginx/conf.d/default.conf

# Copiar APENAS os arquivos construídos da etapa 'builder'
# Copia o conteúdo de /app/frontend/dist (do stage 'builder') para /var/www/frontend (no stage final)
COPY --from=builder /app/frontend/dist /var/www/frontend

# Expor a porta 80 (Nginx padrão)
EXPOSE 80

# Comando para iniciar o Nginx
CMD ["nginx", "-g", "daemon off;"]