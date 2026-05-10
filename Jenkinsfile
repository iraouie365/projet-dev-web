pipeline {
    agent any

    environment {
        IMAGE_NAME = "projet-dev-web"
        IMAGE_TAG = "latest"
        DEPLOYMENT_NAME = "projet-dev-web-depl"
    }

    stages {
        stage('Build Docker Image') {
            steps {
                sh 'docker build -t ${IMAGE_NAME}:${IMAGE_TAG} .'
            }
        }

        stage('Load Image To Minikube') {
            steps {
                sh 'docker save ${IMAGE_NAME}:${IMAGE_TAG} | docker exec -i minikube ctr -n k8s.io images import -'
            }
        }

        stage('Deploy To Kubernetes') {
            steps {
                sh 'docker cp k8s.yml minikube:/tmp/k8s.yml'
                sh 'docker exec minikube kubectl apply -f /tmp/k8s.yml'
                sh 'docker exec minikube kubectl rollout restart deployment/${DEPLOYMENT_NAME}'
            }
        }

        stage('Check Status') {
            steps {
                sh 'docker exec minikube kubectl get pods'
                sh 'docker exec minikube kubectl get svc'
            }
        }
    }
}