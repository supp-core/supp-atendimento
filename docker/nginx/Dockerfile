FROM nginx:alpine

# Remover configuração padrão
RUN rm /etc/nginx/conf.d/default.conf

# Copiar nova configuração
COPY nginx.conf /etc/nginx/conf.d/

EXPOSE 80