FROM nginx:alpine

# Build estático do Vue (gerado por yarn build)
COPY ./supp-atendimento-front-end/dist /usr/share/nginx/html

# Configuração do nginx
COPY ./docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
