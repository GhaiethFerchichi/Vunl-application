pipeline {
    agent any

    environment {
        SONAR_SERVER_NAME = "SonarQube-Server"
        SONAR_HOST_URL = "http://172.31.33.121:9000"
        BACKEND_URL = "http://172.31.39.85:8000/api/v1/audit"
        // CHANGE THIS to your actual SonarQube Project Key
        SONAR_PROJECT_KEY = "Vunl-application" 
    }

    stages {
        stage('Checkout') {
            steps {
                // checkout scm is automatic in multibranch, but we add fetch 
                // to ensure we can diff against the main branch
                script {
                    checkout scm
                    sh "git fetch origin main" 
                }
            }
        }

        stage('SonarQube Analysis') {
            steps {
                script {
                    def scannerHome = tool name: 'SonarScanner', type: 'hudson.plugins.sonar.SonarRunnerInstallation'
                    withSonarQubeEnv(SONAR_SERVER_NAME) {
                        sh "${scannerHome}/bin/sonar-scanner -Dsonar.host.url=${SONAR_HOST_URL} -Dsonar.projectKey=${SONAR_PROJECT_KEY}"
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: false
                }
            }
        }

        stage('AI Orchestration') {
            steps {
                script {
                    if (env.CHANGE_ID) {
                        withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                            
                            echo "📝 Fetching Git Diff..."
                            // Force fetch main to ensure comparison works in detached HEAD
                            sh "git fetch origin main:main || true"
                            def gitDiff = sh(script: "git diff main...HEAD", returnStdout: true).trim()

                            echo "📊 Fetching SonarQube Report..."
                            def sonarIssues = sh(
                                script: "curl -s -u ${SONAR_TOKEN}: 'http://172.31.33.121:9000/api/issues/search?componentKeys=Vunl-application&statuses=OPEN'", 
                                returnStdout: true
                            ).trim()

                            echo "🚀 Preparing Payload..."
                            // Use built-in Groovy JSON (No plugin needed)
                            def payloadMap = [
                                pr_number: env.CHANGE_ID,
                                repository: env.GIT_URL,
                                diff: gitDiff,
                                sast_report: sonarIssues
                            ]
                            def jsonString = groovy.json.JsonOutput.toJson(payloadMap)

                            // Standard writeFile works on every Jenkins
                            writeFile file: 'payload.json', text: jsonString

                            echo "📡 Sending to Backend..."
                            // -v (verbose) will show us the EXACT error if the network is blocked
                            // --data-binary ensures special characters in your code don't break the curl
                            sh "curl -v -X POST ${BACKEND_URL} -H 'Content-Type: application/json' --data-binary @payload.json"
                        }
                    }
                }
            }
        }
    }
}