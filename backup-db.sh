#!/bin/bash

# Configurações
BACKUP_DIR="./backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/suppdb_backup_${TIMESTAMP}.sql"

# Criar diretório de backup se não existir
mkdir -p $BACKUP_DIR

# Executar backup
echo "Iniciando backup do banco de dados..."
docker-compose exec -T database pg_dump -U suppadmin suppdb > $BACKUP_FILE

# Verificar se o backup foi bem-sucedido
if [ $? -eq 0 ]; then
  echo "Backup concluído com sucesso: $BACKUP_FILE"
else
  echo "Erro ao criar backup"
  exit 1
fi