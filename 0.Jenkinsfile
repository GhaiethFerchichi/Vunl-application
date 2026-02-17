pipeline {
    agent any

    environment {
        AI_BRAIN_HOST     = "http://172.31.28.118"
        AI_BRAIN_PORT     = "11434"
        AI_MODEL          = "qwen2.5-coder:1.5b"
        BACKEND_URL       = "http://172.31.39.85:8000/api/v1/audit"
        REPORT_DIR        = "PR-${env.CHANGE_ID ?: 'manual-' + env.BUILD_NUMBER}"
        // Target branch for comparison
        TARGET_BRANCH     = "${env.CHANGE_TARGET ?: 'main'}"
    }

    stages {
        stage('Checkout & Identify Changes') {
            steps {
                script {
                    checkout scm
                    // Fetch the target branch to ensure we can diff against it
                    sh "git fetch origin ${TARGET_BRANCH}"
                    
                    // Get list of changed files (excluding deleted ones)
                    env.CHANGED_FILES = sh(
                        script: "git diff --name-only origin/${TARGET_BRANCH}...HEAD --diff-filter=d | tr '\\n' ' '",
                        returnStdout: true
                    ).trim()
                    
                    echo "🔍 Files changed in this PR: ${env.CHANGED_FILES}"
                    stash name: 'source-code', includes: '**'
                }
            }
        }

        stage('Security Scanning (Delta Only)') {
            agent { label 'security-hub' } 
            steps {
                script {
                    unstash 'source-code'
                    sh "mkdir -p ${REPORT_DIR}"
                    
                    // If no files changed (unlikely in a PR), skip scans
                    if (!env.CHANGED_FILES) {
                        echo "No file changes detected. Skipping scans."
                        return
                    }

                    parallel(
                        "CodeScanAI": {
                            // Already has --changes_only true, keep it as is
                            sh """
                                codescanai --provider custom --model ${AI_MODEL} \
                                           --host ${AI_BRAIN_HOST} --port ${AI_BRAIN_PORT} \
                                           --endpoint /api/generate --directory . \
                                           --changes_only true \
                                           --output_file ${REPORT_DIR}/codescan_report.json
                            """
                        },
                        "Gitleaks": {
                            // We use --log-opts to scan ONLY the commits in this PR
                            sh """
                                docker run --rm -v \$(pwd):/path zricethezav/gitleaks:latest \
                                detect --source=/path \
                                --log-opts="origin/${TARGET_BRANCH}..HEAD" \
                                --report-format=json --report-path=/path/${REPORT_DIR}/gitleaks_report.json || true
                            """
                        },
                        "Semgrep": {
                            // We pass only the changed files to Semgrep
                            sh """
                                docker run --rm -v \$(pwd):/src returntocorp/semgrep \
                                semgrep scan --config auto --json \
                                --output ${REPORT_DIR}/semgrep_report.json ${env.CHANGED_FILES} || true
                            """
                        },
                        "Trivy": {
                            // We scan only the specific files changed (FS mode)
                            sh """
                                docker run --rm -v \$(pwd):/root aquasec/trivy:latest \
                                fs --format json --output /root/${REPORT_DIR}/trivy_report.json \
                                ${env.CHANGED_FILES} || true
                            """
                        }
                    )
                    stash name: 'security-reports', includes: "${REPORT_DIR}/*.json"
                }
            }
        }

        stage('AI Orchestration') {
            steps {
                script {
                    if (env.CHANGE_ID) {
                        unstash 'security-reports'
                        
                        // Use the triple-dot diff to get exactly what this PR introduces
                        def gitDiff = sh(script: "git diff origin/${TARGET_BRANCH}...HEAD", returnStdout: true).trim()

                        def readPRFile = { name -> 
                            def path = "${REPORT_DIR}/${name}"
                            return fileExists(path) ? readFile(path) : "{}" 
                        }

                        def payloadMap = [
                            pr_number       : env.CHANGE_ID,
                            repository      : env.GIT_URL,
                            diff            : gitDiff,
                            codescan_report : readPRFile('codescan_report.json'),
                            gitleaks_report : readPRFile('gitleaks_report.json'),
                            semgrep_report  : readPRFile('semgrep_report.json'),
                            trivy_report    : readPRFile('trivy_report.json')
                        ]
                        
                        writeFile file: 'final_payload.json', text: groovy.json.JsonOutput.toJson(payloadMap)
                        sh "curl -X POST ${BACKEND_URL} -H 'Content-Type: application/json' --data-binary @final_payload.json"
                    }
                }
            }
        }
    }
}