FROM node:18-alpine

WORKDIR /app

# Não precisamos criar um usuário personalizado,
# usaremos o usuário 'node' que já vem com a imagem
# Removemos a linha adduser e addgroup

# Configuração para o Vite
ENV HOST=0.0.0.0
ENV PORT=5173

# Não precisamos mudar para o usuário personalizado
# pois usaremos o 'node' diretamente no docker-compose

EXPOSE 5173

# O comando será substituído no docker-compose.yml
CMD ["sh", "-c", "npm install && npm run dev -- --host 0.0.0.0"]