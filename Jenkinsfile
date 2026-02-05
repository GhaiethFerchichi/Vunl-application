pipeline {
    agent any

    // 1. Auto-install the Scanner inside the Jenkins container
    tools {
        sonarScanner 'SonarScanner'
    }

    environment {
        // 2. Networking: Use the Private IP of the EC2 instance
        // Jenkins (Container) -> Host Network -> SonarQube (Container)
        // REPLACE '172.31.XX.XX' with your actual EC2 Private IP
        SONAR_HOST_URL = "http://172.31.33.121:9000"
        
        // This Name must match 'Manage Jenkins > System > SonarQube servers'
        SONAR_SERVER_NAME = "SonarServer" 
    }

    stages {
        stage('Checkout') {
            steps {
                echo "📥 Cloning from GitHub..."
                checkout scm
            }
        }

        stage('SonarQube Analysis') {
            steps {
                script {
                    echo "🔍 Running SonarScanner..."
                    // This injects the authentication token automatically
                    withSonarQubeEnv(SONAR_SERVER_NAME) {
                        // Pass the host URL explicitly to override 'localhost' defaults
                        sh "sonar-scanner -Dsonar.host.url=${SONAR_HOST_URL}"
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                // 3. Wait for SonarQube to call the Webhook back
                timeout(time: 2, unit: 'MINUTES') {
                    script {
                        echo "⏳ Waiting for Quality Gate..."
                        def qg = waitForQualityGate()
                        if (qg.status != 'OK') {
                            error "❌ Pipeline Failed: Quality Gate is ${qg.status}"
                        } else {
                            echo "✅ Pipeline Passed: Quality Gate is GREEN"
                        }
                    }
                }
            }
        }
    }
}