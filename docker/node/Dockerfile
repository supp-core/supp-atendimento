FROM node:18-alpine

WORKDIR /app

# Copiar package.json e package-lock.json primeiro para aproveitar o cache do Docker
COPY supp-atendimento-front-end/package*.json /app/

# Instalar dependências do frontend
RUN npm install

# Copiar código fonte do frontend
COPY supp-atendimento-front-end /app/

# Build do frontend para produção
RUN npm run build

# Instalar servidor estático (ex: serve) - Você pode usar outro como http-server
RUN npm install -g serve

# Expor a porta para o frontend (porta que o serve vai usar, padrão é 5000, mas vamos manter 5173 para consistência com dev)
EXPOSE 5173

# Comando para servir os arquivos estáticos de produção (assumindo que o build coloca em 'dist')
CMD ["serve", "-s", "dist", "-p", "5173"] # ou CMD ["http-server", "dist", "-p", "5173"]