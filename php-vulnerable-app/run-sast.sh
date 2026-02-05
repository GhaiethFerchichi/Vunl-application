#!/bin/bash

# Jenkins Pipeline for PHP Vulnerable Application SAST Testing

# Build and test PHP vulnerable application with various SAST tools

set -e

echo "🚀 Starting PHP Vulnerable Application SAST Pipeline"

# Install PHP analysis tools
echo "📦 Installing PHP analysis tools..."
composer install --no-dev 2>/dev/null || true

# Run PHPStan (Static Analysis)
echo "🔍 Running PHPStan analysis..."
phpstan analyse --level=0 . --no-progress 2>/dev/null || true

# Run PHP CodeSniffer
echo "🔍 Running PHP CodeSniffer..."
phpcs --standard=PSR12 . 2>/dev/null || true

# Run OWASP Dependency-Check
echo "🔒 Running OWASP Dependency-Check..."
dependency-check --scan . --format JSON --out reports/ 2>/dev/null || true

# Run SonarQube Scanner
echo "📊 Running SonarQube analysis..."
sonar-scanner \
  -Dsonar.projectKey=php-vulnerable-app \
  -Dsonar.projectName="PHP Vulnerable Application" \
  -Dsonar.sources=. \
  -Dsonar.language=php \
  -Dsonar.exclusions="**/vendor/**,**/node_modules/**" \
  -Dsonar.qualitygate.wait=true

echo "✅ PHP Vulnerable Application SAST Testing Complete"
