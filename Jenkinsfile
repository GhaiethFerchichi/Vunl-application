pipeline {
    agent any

    environment {
        SONAR_SERVER_NAME = "SonarQube-Server"
        SONAR_HOST_URL = "http://172.31.33.121:9000"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('SonarQube Analysis') {
            steps {
                script {
                    // This fetches the tool path manually using the valid type from your error
                    def scannerHome = tool name: 'SonarScanner', type: 'hudson.plugins.sonar.SonarRunnerInstallation'
                    
                    withSonarQubeEnv(SONAR_SERVER_NAME) {
                        // Use the full path to the scanner executable
                        sh "${scannerHome}/bin/sonar-scanner -Dsonar.host.url=${SONAR_HOST_URL}"
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }
    }
}