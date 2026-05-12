pipeline {
    agent any

    environment {
        IMAGE_NAME = "projet-dev-web"
        IMAGE_TAG = "${BUILD_NUMBER}"
        DEPLOYMENT_NAME = "projet-dev-web-depl"
        KUBECTL = "/var/lib/minikube/binaries/v1.35.1/kubectl"
        KUBECONFIG = "/etc/kubernetes/admin.conf"
    }

    stages {
        stage('Show Commit') {
            steps {
                sh 'git log --oneline -1'
                sh 'ls -la'
            }
        }

        stage('Build Docker Image') {
            steps {
                sh "docker build --no-cache -t ${IMAGE_NAME}:${IMAGE_TAG} ."
                sh "docker tag ${IMAGE_NAME}:${IMAGE_TAG} ${IMAGE_NAME}:latest"
            }
        }

        stage('Load Image To Minikube') {
            steps {
                sh "docker save ${IMAGE_NAME}:${IMAGE_TAG} | docker exec -i minikube ctr -n k8s.io images import -"
                sh "docker save ${IMAGE_NAME}:latest | docker exec -i minikube ctr -n k8s.io images import -"
            }
        }

        stage('Deploy To Kubernetes') {
            steps {
                sh "docker exec -i minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} apply -f - < k8s.yml"

                sh """
                docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} set image deployment/${DEPLOYMENT_NAME} \
                projet-dev-web-container=${IMAGE_NAME}:${IMAGE_TAG}
                """

                sh "docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} rollout restart deployment/${DEPLOYMENT_NAME}"
                sh "docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} rollout status deployment/${DEPLOYMENT_NAME}"
            }
        }

        stage('Check Status') {
            steps {
                sh "docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} get pods -o wide"
                sh "docker exec minikube ${KUBECTL} --kubeconfig=${KUBECONFIG} get svc"
            }
        }
    }
}