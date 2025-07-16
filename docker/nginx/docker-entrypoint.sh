#!/bin/sh
set -e

echo "Entrypoint do Proxy: Aguardando o serviço frontend..."
# Loop que espera pelo frontend na porta 5173
until nc -z frontend 5173; do
  echo "Serviço frontend não está pronto. Tentando novamente em 2 segundos..."
  sleep 2
done
echo "✅ Frontend está pronto."

echo "Entrypoint do Proxy: Aguardando o serviço backend..."
# Loop que espera pelo backend na porta 9000
until nc -z backend 9000; do
  echo "Serviço backend não está pronto. Tentando novamente em 2 segundos..."
  sleep 2
done
echo "✅ Backend está pronto."

echo "🚀 Todos os serviços estão prontos! Iniciando Nginx..."
# Inicia o processo principal do Nginx
exec nginx -g 'daemon off;'