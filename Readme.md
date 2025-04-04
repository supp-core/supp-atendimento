
# Para realizar Build do Projeto:
docker build -t suppregistry.azurecr.io/helpdesk/supp-backend-kubernetes:1.0.0 -f ./docker/php/Dockerfile .
docker build -t suppregistry.azurecr.io/helpdesk/supp-webserver-kubernetes:1.0.0 -f ./docker/nginx/Dockerfile .

# Enviar as imagems para azure:
docker push suppregistry.azurecr.io/helpdesk/supp-backend-kubernetes:1.0.0
docker push suppregistry.azurecr.io/helpdesk/supp-webserver-kubernetes:1.0.0

# Executar para kubernetes:
kubectl apply -f kubernetes/backend-deployment.yaml -n helpdesk && sleep 15 && \
kubectl apply -f kubernetes/backend-service.yaml -n helpdesk && sleep 15 && \
kubectl apply -f kubernetes/mailhog-deployment.yaml -n helpdesk && sleep 15 && \
kubectl apply -f kubernetes/webserver-deployment.yaml -n helpdesk && sleep 15 && \
kubectl apply -f kubernetes/webserver-service.yaml -n helpdesk && sleep 15 && \
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
