pipeline {
    agent any
    tools { 'hudson.plugins.sonar.SonarRunnerInstallation' 'SonarScanner' }
    
    environment {
        SONAR_SERVER = "SonarQube-Server"
        BACKEND_URL = "http://172.31.39.85:8000/api/v1/audit" // FastAPI endpoint
    }

    stages {
        stage('Checkout & Scan') {
            steps {
                checkout scm
                withSonarQubeEnv(SONAR_SERVER) {
                    sh "sonar-scanner -Dsonar.projectKey=${JOB_NAME}"
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
                    // 1. Get the Git Diff against the target branch (main)
                    def gitDiff = sh(script: "git diff origin/main...HEAD", returnStdout: true).trim()
                    
                    // 2. Fetch SonarQube Issues via API
                    def sonarIssues = sh(script: "curl -s -u ${SONAR_TOKEN}: 'http://172.31.33.121:9000/api/issues/search?componentKeys=${JOB_NAME}&statuses=OPEN'", returnStdout: true).trim()

                    // 3. Send Everything to FastAPI
                    def payload = groovy.json.JsonOutput.toJson([
                        pr_number: env.CHANGE_ID, // Automatically set by GitHub/Multibranch plugin
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