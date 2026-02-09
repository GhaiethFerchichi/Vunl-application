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
                    // Only run full AI audit for Pull Requests to save resources
                    if (env.CHANGE_ID) {
                        withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                            
                            echo "📡 Fetching PR report for ${SONAR_PROJECT_KEY}..."
                            def sonarIssues = sh(
                                script: "curl -s -u ${SONAR_TOKEN}: '${SONAR_HOST_URL}/api/issues/search?componentKeys=${SONAR_PROJECT_KEY}&statuses=OPEN'", 
                                returnStdout: true
                            ).trim()

                            echo "📝 Extracting Git Diff against origin/main..."
                            def gitDiff = sh(script: "git diff origin/main...HEAD", returnStdout: true).trim()

                            echo "🚀 Sending PR #${env.CHANGE_ID} payload to Node C..."
                            def payload = groovy.json.JsonOutput.toJson([
                                pr_number: env.CHANGE_ID,
                                repository: env.GIT_URL,
                                diff: gitDiff,
                                sast_report: sonarIssues
                            ])

                            sh "curl -X POST ${BACKEND_URL} -H 'Content-Type: application/json' -d '${payload}'"
                        }
                    } else {
                        echo "🌿 Skipping AI Orchestration: Not a Pull Request (Branch: ${env.BRANCH_NAME})"
                    }
                }
            }
        }
    }
}