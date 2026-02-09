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
                    // env.CHANGE_ID is only populated in Multibranch PR builds
                    if (env.CHANGE_ID) { 
                        withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                            
                            echo "🔍 Fetching comparison context..."
                            // Ensures the local git workspace knows about the main branch
                            sh "git fetch origin main:main || true" 
                            
                            echo "📝 Generating Git Diff..."
                            // Using origin/main...HEAD is correct for PRs
                            def gitDiff = sh(script: "git diff main...HEAD", returnStdout: true).trim()

                            echo "📊 Fetching SonarQube Report..."
                            def sonarIssues = sh(
                                script: "curl -s -u ${SONAR_TOKEN}: 'http://172.31.33.121:9000/api/issues/search?componentKeys=Vunl-application&statuses=OPEN'", 
                                returnStdout: true
                            ).trim()

                            // PREVENT EMPTY DATA: If diff is empty, the AI will have nothing to audit
                            if (gitDiff.isEmpty()) {
                                echo "⚠️ WARNING: Git diff is empty. Check if your PR branch has new commits."
                            }

                            echo "🚀 Preparing Payload (Diff Size: ${gitDiff.length()} chars)"
                            def payloadMap = [
                                pr_number: env.CHANGE_ID,
                                repository: env.GIT_URL,
                                diff: gitDiff,
                                sast_report: sonarIssues
                            ]
                            
                            // CRITICAL: We use a file to send the payload. 
                            // Direct strings in 'sh' often break due to special characters in the code diff.
                            writeJSON file: 'payload.json', json: payloadMap
                            
                            sh "curl -X POST ${BACKEND_URL} -H 'Content-Type: application/json' --data @payload.json"
                            
                            // Clean up
                            sh "rm payload.json"
                        }
                    } else {
                        echo "🌿 Skipping AI: This is a branch build (${env.BRANCH_NAME}), not a Pull Request."
                    }
                }
            }
        }
    }
}