# ./docker/node/Dockerfile

FROM node:18-alpine

WORKDIR /app

# 1. Copia APENAS o package.json. Isso é um truque para otimizar o cache do Docker.
COPY supp-atendimento-front-end/package.json ./

# 2. Roda 'npm install' sem um package-lock.json presente no contexto.
RUN npm install

# 3. Agora que as dependências estão instaladas, copia o resto do código-fonte.
COPY supp-atendimento-front-end/ ./

EXPOSE 5173

# O comando para iniciar o servidor, garantindo que ele escute em todas as interfaces.
CMD ["npm", "run", "dev"]