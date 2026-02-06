pipeline {
    agent any

    environment {
        SONAR_SERVER_NAME = "SonarQube-Server"
        SONAR_HOST_URL = "http://172.31.33.121:9000"
        BACKEND_URL = "http://172.31.39.85:8000/api/v1/audit" // AI-Orchestrator-fastapi endpoint

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
        stage('AI Orchestration') {
            steps {
                script {
                    // This block maps your Jenkins Credential ID to the variable 'SONAR_TOKEN'
                    withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                        
                        echo "📡 Fetching report from SonarQube..."
                        // Fetch SonarQube Issues via API using the token
                        def sonarIssues = sh(
                            script: "curl -s -u ${SONAR_TOKEN}: 'http://172.31.33.121:9000/api/issues/search?componentKeys=${JOB_NAME}&statuses=OPEN'", 
                            returnStdout: true
                        ).trim()

                        echo "📝 Extracting Git Diff..."
                        def gitDiff = sh(script: "git diff origin/main...HEAD", returnStdout: true).trim()

                        echo "🚀 Sending payload to Node C..."
                        def payload = groovy.json.JsonOutput.toJson([
                            pr_number: env.CHANGE_ID ?: "0", // Fallback for manual builds
                            repository: env.GIT_URL,
                            diff: gitDiff,
                            sast_report: sonarIssues
                        ])

                        sh "curl -X POST ${BACKEND_URL} -H 'Content-Type: application/json' -d '${payload}'"
                    }
                }
            }
        }
    }
}