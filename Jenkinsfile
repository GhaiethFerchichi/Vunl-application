pipeline {
    agent any

    tools {
        // This ensures the scanner is installed inside the Jenkins container
        // Make sure 'SonarScanner' matches the name in Manage Jenkins > Tools
        sonarScanner 'SonarScanner'
    }

    environment {
        // We use the server name defined in your screenshot
        SONAR_SERVER_NAME = "SonarQube-Server"
        
        // Point to the SonarQube Private IP from your screenshot
        SONAR_HOST_URL = "http://172.31.33.121:9000"
    }

    stages {
        stage('Checkout') {
            steps {
                echo "📥 Pulling code from GitHub..."
                checkout scm
            }
        }

        stage('SonarQube Analysis') {
            steps {
                script {
                    echo "🔍 Starting Static Analysis..."
                    // This block injects your 'SonarQube Token for the webhook' automatically
                    withSonarQubeEnv(SONAR_SERVER_NAME) {
                        sh "sonar-scanner -Dsonar.host.url=${SONAR_HOST_URL}"
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                // Jenkins will pause here and wait for SonarQube to send the Webhook result
                timeout(time: 5, unit: 'MINUTES') {
                    script {
                        echo "⏳ Waiting for SonarQube Quality Gate..."
                        def qg = waitForQualityGate()
                        if (qg.status != 'OK') {
                            error "❌ Pipeline Failed: Quality Gate is ${qg.status}"
                        } else {
                            echo "✅ Quality Gate Passed!"
                        }
                    }
                }
            }
        }
    }
}