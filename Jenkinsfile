pipeline {
    agent any // Default agent for orchestration and checkout

    environment {
        SONAR_SERVER_NAME = "SonarQube-Server"
        SONAR_HOST_URL    = "http://172.31.33.121:9000"
        BACKEND_URL       = "http://172.31.39.85:8000/api/v1/audit"
        SONAR_PROJECT_KEY = "Vunl-application"
        
        // AI Brain (Ollama)
        AI_BRAIN_HOST     = "http://172.31.28.118"
        AI_BRAIN_PORT     = "11434"
        AI_MODEL          = "qwen2.5-coder:1.5b"
    }

    stages {
        stage('Checkout') {
            steps {
                script {
                    checkout scm
                    sh "git fetch origin main || true"
                    // Save the workspace state for the security-hub node
                    stash name: 'source-code', includes: '**'
                }
            }
        }

        stage('Security Scanning (on security-hub node)') {
            agent { label 'security-hub' } // Targets your specific node with the Docker images
            steps {
                script {
                    unstash 'source-code'
                    
                    parallel(
                        "Gitleaks": {
                            sh "docker run --rm -v \$(pwd):/path zricethezav/gitleaks:latest detect --source=/path --report-format=json --report-path=/path/gitleaks_report.json || true"
                        },
                        "Semgrep": {
                            sh "docker run --rm -v \$(pwd):/src returntocorp/semgrep semgrep scan --config auto --json --output semgrep_report.json || true"
                        },
                        "Trivy FS": {
                            sh "docker run --rm -v \$(pwd):/root -v /var/run/docker.sock:/var/run/docker.sock aquasec/trivy:latest fs --format json --output /root/trivy_report.json /root || true"
                        }
                    )
                    // Send reports back to the controller to be processed by the Orchestrator
                    stash name: 'security-reports', includes: '*_report.json'
                }
            }
        }

        stage('CodeScanAI (Ollama)') {
            when { expression { env.CHANGE_ID != null } }
            steps {
                script {
                    echo "🧠 AI Code Review via Ollama..."
                    sh """
                        codescanai --provider custom \
                                   --host ${AI_BRAIN_HOST} \
                                   --port ${AI_BRAIN_PORT} \
                                   --model ${AI_MODEL} \
                                   --changes_only true \
                                   --directory . \
                                   --output_file codescan_report.json
                    """
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

        stage('AI Orchestration & PR Feedback') {
            steps {
                script {
                    if (env.CHANGE_ID) {
                        // Bring in the reports from the security-hub node
                        unstash 'security-reports'
                        
                        withCredentials([string(credentialsId: 'sonar-token', variable: 'SONAR_TOKEN')]) {
                            
                            def getReport = { filename -> 
                                return fileExists(filename) ? readFile(filename) : "{}" 
                            }

                            def sonarIssues = sh(
                                script: "curl -s -u ${SONAR_TOKEN}: '${SONAR_HOST_URL}/api/issues/search?componentKeys=${SONAR_PROJECT_KEY}&statuses=OPEN'", 
                                returnStdout: true
                            ).trim()

                            def gitDiff = sh(script: "git diff origin/main...HEAD", returnStdout: true).trim()

                            def payloadMap = [
                                pr_number       : env.CHANGE_ID,
                                repository      : env.GIT_URL,
                                diff            : gitDiff,
                                sonar_report    : sonarIssues,
                                codescan_report : getReport('codescan_report.json'),
                                gitleaks_report : getReport('gitleaks_report.json'),
                                semgrep_report  : getReport('semgrep_report.json'),
                                trivy_report    : getReport('trivy_report.json')
                            ]
                            
                            def jsonString = groovy.json.JsonOutput.toJson(payloadMap)
                            writeFile file: 'final_payload.json', text: jsonString

                            sh "curl -v -X POST ${BACKEND_URL} -H 'Content-Type: application/json' --data-binary @final_payload.json"
                        }
                    }
                }
            }
        }
    }
}