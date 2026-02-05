# PHP Vulnerable Application

An intentionally vulnerable PHP application designed for testing SAST (Static Application Security Testing) tools and learning security concepts.

**⚠️ WARNING: This application contains intentional security vulnerabilities. DO NOT use in production or deploy to public servers.**

## Vulnerabilities Included

### 1. **SQL Injection** (CWE-89)
- **File**: [index.php](index.php)
- Direct concatenation of user input in SQL queries
- Affected endpoints: Login form, search functionality

### 2. **Cross-Site Scripting (XSS)** (CWE-79)
- **File**: [index.php](index.php)
- Unescaped user input echoed directly to output
- Affected endpoints: Search results

### 3. **Insecure Direct Object References (IDOR)** (CWE-639)
- **File**: [index.php](index.php)
- No authorization checks before accessing user data
- Affected endpoints: User profile viewer

### 4. **Path Traversal** (CWE-22)
- **File**: [index.php](index.php), [files.php](files.php)
- Directory traversal in file operations
- Affected endpoints: File viewer, downloads

### 5. **Command Injection** (CWE-78)
- **File**: [index.php](index.php)
- User input directly passed to system commands
- Affected endpoints: File creation

### 6. **Weak Cryptography** (CWE-327)
- **File**: [index.php](index.php), [login.php](login.php)
- MD5 hashing without salt
- Hardcoded credentials
- Affected endpoints: Password hashing, login

### 7. **Cross-Site Request Forgery (CSRF)** (CWE-352)
- **File**: [index.php](index.php)
- No CSRF token validation
- Affected endpoints: Money transfer form

### 8. **Insecure Deserialization** (CWE-502)
- **File**: [index.php](index.php)
- Unserializing untrusted user data
- Potential for arbitrary code execution

### 9. **Security Misconfiguration** (CWE-16)
- Error display enabled in production
- Debug mode enabled
- Session fixation vulnerabilities
- No security headers

### 10. **Hardcoded Secrets** (CWE-798)
- **File**: [config.php](config.php)
- Database credentials exposed
- API keys hardcoded
- JWT secrets in code

### 11. **Local File Inclusion (LFI)** (CWE-98)
- **File**: [files.php](files.php)
- Dynamic file inclusion without validation

### 12. **Information Disclosure** (CWE-200)
- **File**: [login.php](login.php)
- Error messages reveal whether username is valid
- Verbose database errors

### 13. **API Vulnerabilities**
- **File**: [api.php](api.php)
- No authentication checks
- SQL injection in API endpoints
- No input validation
- Arbitrary file upload

## Project Structure

```
php-vulnerable-app/
├── index.php                    # Main application with multiple vulnerabilities
├── api.php                      # REST API with vulnerabilities
├── login.php                    # Authentication with vulnerabilities
├── config.php                   # Configuration with hardcoded secrets
├── files.php                    # File operations with vulnerabilities
├── database.php                 # Database operations with SQL injection
├── sonar-project.properties     # SonarQube configuration
├── package.json                 # Project metadata
└── README.md                    # This file
```

## Running the Application

### Using PHP Built-in Server

```bash
php -S localhost:8000
```

Access the application at `http://localhost:8000`

### Using Docker

```dockerfile
FROM php:8.0-apache
COPY . /var/www/html
RUN chmod -R 755 /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
```

## Testing with SAST Tools

### SonarQube Analysis

```bash
sonar-scanner \
  -Dsonar.projectKey=php-vulnerable-app \
  -Dsonar.sources=. \
  -Dsonar.host.url=http://localhost:9000 \
  -Dsonar.login=your-token
```

### PHPStan (Static Analysis)

```bash
composer require --dev phpstan/phpstan
phpstan analyse --level=0 .
```

### PHP CodeSniffer

```bash
composer require --dev squizlabs/php_codesniffer
phpcs --standard=PSR12 .
```

### OWASP Dependency-Check

```bash
dependency-check --scan .
```

## Exploitation Examples

### SQL Injection
```
Username: admin' OR '1'='1
Password: anything
```

### XSS
```
Search: <img src=x onerror="alert('XSS')">
```

### Path Traversal
```
File: ../../../etc/passwd
```

### Command Injection
```
Filename: test.txt; cat /etc/passwd
```

## Security Testing Checklist

- [ ] SQL Injection testing
- [ ] XSS testing
- [ ] CSRF testing
- [ ] IDOR testing
- [ ] Authentication bypass
- [ ] Authorization flaws
- [ ] Cryptographic failures
- [ ] Sensitive data exposure
- [ ] XML/XXE attacks
- [ ] Broken access control
- [ ] API vulnerabilities
- [ ] Session management flaws

## Learning Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [PHP Security](https://www.php.net/manual/en/security.php)

## Legal Disclaimer

This application is provided for educational and authorized security testing purposes only. Unauthorized access to computer systems is illegal. Users are responsible for ensuring they have proper authorization before testing this application against any systems.

## License

MIT
