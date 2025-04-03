# Criar o Service Principal e Configurar o Azure DevOps
# 1. Primeiro, precisamos garantir que o Azure DevOps possa acessar o AKS e o Container Registry.

# Agora, crie um Service Principal para permitir que o Azure DevOps se conecte ao AKS:
az ad sp create-for-rbac --name "azuredevops-sp" --role contributor \
    --scopes /subscriptions/f01fd13a-57e0-4e37-b825-73e0c468cdfa/resourceGroups/pdbl-vnet-prd-br-rg