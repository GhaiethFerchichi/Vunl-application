pipeline {
    agent any

    environment {
        AI_BRAIN_HOST     = "http://172.31.28.118"
        AI_BRAIN_PORT     = "11434"
        AI_MODEL          = "qwen2.5-coder:1.5b"
        BACKEND_URL       = "http://172.31.39.85:8000/api/v1/audit"
        REPORT_DIR        = "PR-${env.CHANGE_ID ?: 'manual-' + env.BUILD_NUMBER}"
        TARGET_BRANCH     = "${env.CHANGE_TARGET ?: 'main'}"
    }

    stages {
        stage('Checkout & Identify Changes') {
            steps {
                script {
                    // Standard checkout
                    checkout scm
                    
                    // FIX: Force fetch the target branch so git diff can find it
                    // This creates the 'origin/main' reference locally
                    sh "git fetch origin +refs/heads/${TARGET_BRANCH}:refs/remotes/origin/${TARGET_BRANCH}"
                    
                    // Get list of changed files
                    try {
                        env.CHANGED_FILES = sh(
                            script: "git diff --name-only origin/${TARGET_BRANCH}...HEAD --diff-filter=d | tr '\\n' ' '",
                            returnStdout: true
                        ).trim()
                    } catch (Exception e) {
                        echo "⚠️ Git diff failed. Falling back to empty file list."
                        env.CHANGED_FILES = ""
                    }
                    
                    echo "🔍 Files changed in this PR: ${env.CHANGED_FILES}"
                    stash name: 'source-code', includes: '**'
                }
            }
        }

        stage('Security Scanning (Delta Only)') {
            // Use 'any' if 'security-hub' is still offline
            // agent any 
            agent { label 'security-hub' } 
            steps {
                script {
                    if (!env.CHANGED_FILES) {
                        echo "✅ No file changes to scan. Skipping tools."
                        return
                    }
                    
                    unstash 'source-code'
                    sh "mkdir -p ${REPORT_DIR}"
                    
                    parallel(
                        "CodeScanAI": {
                            sh "codescanai --provider custom --model ${AI_MODEL} --host ${AI_BRAIN_HOST} --port ${AI_BRAIN_PORT} --endpoint /api/generate --directory . --changes_only true --output_file ${REPORT_DIR}/codescan_report.json"
                        },
                        "Gitleaks": {
                            sh "docker run --rm -v \$(pwd):/path zricethezav/gitleaks:latest detect --source=/path --log-opts='origin/${TARGET_BRANCH}..HEAD' --report-format=json --report-path=/path/${REPORT_DIR}/gitleaks_report.json || true"
                        },
                        "Semgrep": {
                            sh "docker run --rm -v \$(pwd):/src returntocorp/semgrep semgrep scan --config auto --json --output ${REPORT_DIR}/semgrep_report.json ${env.CHANGED_FILES} || true"
                        },
                        "Trivy": {
                            sh "docker run --rm -v \$(pwd):/root aquasec/trivy:latest fs --format json --output /root/${REPORT_DIR}/trivy_report.json ${env.CHANGED_FILES} || true"
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
                        
                        // Use triple-dot for the final report diff
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