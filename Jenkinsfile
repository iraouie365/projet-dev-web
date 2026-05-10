pipeline {
    agent any

    stages {
        stage('Test Jenkins') {
            steps {
                echo 'Jenkins fonctionne avec GitHub'
                sh 'ls -la'
            }
        }

        stage('Test Docker') {
            steps {
                sh 'docker --version'
            }
        }
    }
}