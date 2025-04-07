
# Para realizar Build do Projeto:
docker build -t suppregistry.azurecr.io/helpdesk/supp-backend-kubernetes:1.0.1 -f ./docker/php/Dockerfile .
docker build -t suppregistry.azurecr.io/helpdesk/supp-webserver-kubernetes:1.0.1 -f ./docker/nginx/Dockerfile .

# Enviar as imagems para azure:
docker push suppregistry.azurecr.io/helpdesk/supp-backend-kubernetes:1.0.1
docker push suppregistry.azurecr.io/helpdesk/supp-webserver-kubernetes:1.0.1

# Executar para kubernetes:
kubectl apply -f kubernetes/backend-deployment.yaml -n helpdesk && sleep 10 && \
kubectl apply -f kubernetes/backend-service.yaml -n helpdesk && sleep 10 && \
kubectl apply -f kubernetes/mailhog-deployment.yaml -n helpdesk && sleep 10 && \
kubectl apply -f kubernetes/webserver-deployment.yaml -n helpdesk && sleep 10 && \
kubectl apply -f kubernetes/webserver-service.yaml -n helpdesk

# Certificado TLS PGMBH
kubectl create secret tls supp-pgmbh-tls-secret \
  --namespace helpdesk \
  --cert=certificado-supp/supp-pgmbh-certificado.crt \
  --key=certificado-supp/supp-pgmbh-chave.key
secret/supp-pgmbh-tls-secret created

# Ingress para criação do Host de acesso helpdesk.pgmbh.org
kubectl apply -f kubernetes/ingress.yaml -n helpdesk

# Verificar os pods:
kubectl get pods -n helpdesk
kubectl get ingress -n helpdesk


# Deletar todos os serviços: 
kubectl delete -f kubernetes/backend-deployment.yaml -n helpdesk
kubectl delete -f kubernetes/mailhog-deployment.yaml -n helpdesk
kubectl delete -f kubernetes/webserver-deployment.yaml -n helpdesk
kubectl delete -f kubernetes/webserver-service.yaml -n helpdesk
kubectl delete -f kubernetes/ingress.yaml -n helpdesk