pipeline {
    agent any

    environment {
        IMAGE_NAME = "projet-dev-web"
        IMAGE_TAG = "latest"
        DEPLOYMENT_NAME = "projet-dev-web-depl"
        KUBECTL = "/var/lib/minikube/binaries/v1.35.1/kubectl"
        KUBECONFIG = "/etc/kubernetes/admin.conf"
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
                sh 'docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} apply -f /tmp/k8s.yml'
                sh 'docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} rollout restart deployment/${DEPLOYMENT_NAME}'
            }
        }

        stage('Check Status') {
            steps {
                sh 'docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} get pods'
                sh 'docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} get svc'
            }
        }
    }
}