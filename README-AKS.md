trigger:
  branches:
    include:
    - main
    - develop

variables:
  # Substitua com seu registro de contêiner Azure (ACR)
  acrName: 'suppacr'
  acrLoginServer: '$(acrName).azurecr.io'
  backendImageName: 'supp-backend'
  frontendImageName: 'supp-frontend'
  # Nome do seu cluster AKS
  aksClusterName: 'supp-aks-cluster'
  aksResourceGroup: 'supp-resource-group'
  # Namespace Kubernetes
  k8sNamespace: 'supp'

stages:
- stage: Build
  jobs:
  - job: BuildImages
    pool:
      vmImage: 'ubuntu-latest'
    steps:
    - task: Docker@2
      displayName: 'Build and Push Backend Image'
      inputs:
        containerRegistry: 'ACR'
        repository: '$(backendImageName)'
        command: 'buildAndPush'
        Dockerfile: 'docker/backend.Dockerfile'
        tags: |
          $(Build.BuildId)
          latest
          
    - task: Docker@2
      displayName: 'Build and Push Frontend Image'
      inputs:
        containerRegistry: 'ACR'
        repository: '$(frontendImageName)'
        command: 'buildAndPush'
        Dockerfile: 'docker/frontend.Dockerfile'
        tags: |
          $(Build.BuildId)
          latest

- stage: Deploy
  dependsOn: Build
  jobs:
  - job: DeployToK8s
    pool:
      vmImage: 'ubuntu-latest'
    steps:
    - task: KubernetesManifest@0
      displayName: 'Create/Update ConfigMaps and Secrets'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/configmaps-secrets.yaml
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Persistent Volumes'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/persistent-volumes.yaml
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Database'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/database-statefulset.yaml
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Backend'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/backend-deployment.yaml
        containers: |
          $(acrLoginServer)/$(backendImageName):$(Build.BuildId)
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Frontend'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/frontend-deployment.yaml
        containers: |
          $(acrLoginServer)/$(frontendImageName):$(Build.BuildId)
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Webserver'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/webserver-deployment.yaml
          
    - task: KubernetesManifest@0
      displayName: 'Deploy MailHog'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/mailhog-deployment.yaml
          
    - task: KubernetesManifest@0
      displayName: 'Deploy Ingress'
      inputs:
        action: 'deploy'
        kubernetesServiceConnection: 'AKS-Connection'
        namespace: '$(k8sNamespace)'
        manifests: |
          kubernetes/ingress.yaml