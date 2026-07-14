# Security Assessment & Hardening Guide
## Flipped-Microlearning MOOC Platform (FMMP)

This document maps the security assessment, vulnerability profiles, and defense-in-depth mitigation strategies for the Flipped-Microlearning platform codebase.

*   **System Architecture:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **API Specification:** [API_SPEC.md](file:///d:/xamp/htdocs/fitcoch/docs/API_SPEC.md)

---

## 1. Vulnerability Profiling & Mitigations

### 1.1 SQL Injection (SQLi)
*   **Vulnerability Vector:** concatenation of user input strings directly inside database queries (e.g., query identifiers inside repository layers).
*   **Mitigation Strategy:** 
    *   Direct raw SQL queries must be processed via PDO prepared statements using named parameterized bindings.
    *   Strict validation of data types (e.g., casting `(int)` variables) before execution.
*   **Code Implementation Pattern:**
    ```php
    // SECURE: Prepared parameter binding inside Repositories
    $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => (int)$userId]);
    ```

---

### 1.2 Cross-Site Request Forgery (CSRF)
*   **Vulnerability Vector:** State-changing requests (`POST`, `PUT`, `DELETE`) executed by browsers automatically attaching session cookies when navigating malicious third-party links.
*   **Mitigation Strategy:**
    *   Incorporate a cryptographically secure random token (CSRF Token) in the user session.
    *   Transmit the token via hidden input fields in HTML views or custom request headers (`X-CSRF-TOKEN`).
    *   Implement validation in a global `CSRFMiddleware`.
*   **Code Implementation Pattern:**
    ```php
    // Generate Token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    // HTML View Inclusion
    echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    ```

---

### 1.3 Cross-Site Scripting (XSS)
*   **Vulnerability Vector:** Attackers injecting malicious JavaScript scripts via user profile inputs (e.g. name edits) or markdown cards, which execute in the browsers of other users.
*   **Mitigation Strategy:**
    *   *Output Escaping:* Escape all variables rendered in HTML layouts using `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
    *   *Content Security Policy (CSP):* Set standard HTTP security headers restricting external script executions to white-listed domains.
    *   *HTML Purifier:* Filter HTML content cards using a strict sanitizer library before database writes.
*   **Code Implementation Pattern:**
    ```php
    // Helper function for view templates
    function escape(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    ```

---

### 1.4 Session Hijacking & Session Fixation
*   **Vulnerability Vector:** Attackers acquiring the active user `PHPSESSID` cookie to impersonate users, or forcing a known session ID prior to authentication.
*   **Mitigation Strategy:**
    *   Configure strict PHP session cookie settings via `php.ini` or session initiation scripts:
        *   `session.cookie_httponly = 1` (Blocks JS access to session cookies).
        *   `session.cookie_secure = 1` (Forces SSL transmission).
        *   `session.cookie_samesite = "Lax"` (Prevents cross-site cookie attachment).
    *   Always rotate session identifiers immediately upon login/privilege changes using `session_regenerate_id(true)`.

---

### 1.5 Broken Authentication
*   **Vulnerability Vector:** Weak credential verification, credential stuffing attacks, or lack of account lockouts.
*   **Mitigation Strategy:**
    *   Enforce secure cryptographic hashing using standard `PASSWORD_ARGON2ID` algorithms via PHP's `password_hash()` function.
    *   Establish account locking mechanisms (temporarily lock accounts/IPs after 5 consecutive failed login attempts).
    *   Enforce session timeouts (auto-destroy sessions after 30 minutes of inactivity).

---

### 1.6 Broken Authorization (Privilege Escalation & IDOR)
*   **Vulnerability Vector:** Learners calling endpoints reserved for instructors (e.g., `/api/v1/instructor/analytics`) or modifying query parameters to view another student's quiz attempts.
*   **Mitigation Strategy:**
    *   Map route groups through validation middlewares (`RoleMiddleware`) verifying user roles.
    *   Perform relationship checks inside the service layer (e.g. verify if the authenticated `user_id` matches the `user_id` of the requested `quiz_attempt` resource record).
*   **Code Implementation Pattern:**
    ```php
    // Domain ownership validation in Service Layer
    $attempt = $this->quizRepo->findAttemptById($attemptId);
    if ($attempt->user_id !== $currentUserId && !$this->authService->isInstructor($currentUserId)) {
        throw new AuthorizationException("Access denied.");
    }
    ```

---

### 1.7 File Upload Risks
*   **Vulnerability Vector:** Uploading arbitrary files containing executable scripts (e.g. `exploit.php`) into public web directories, leading to Remote Code Execution (RCE).
*   **Mitigation Strategy:**
    *   Store uploaded files outside the public HTML web root (`/storage/uploads/`).
    *   Do not rely on the client-submitted MIME-type or extension. Inspect files using the server-side `finfo` signature library.
    *   Rename files to randomized secure hash keys (e.g. UUID) upon upload.
    *   Configure web server properties (Apache `.htaccess` / Nginx configuration rules) to explicitly disable PHP file execution inside the storage directories.

---

### 1.8 Video Access Control
*   **Vulnerability Vector:** Unauthorized sharing of direct video URLs to bypass curriculum sequential unlocking gates and flipped class gates.
*   **Mitigation Strategy:**
    *   Do not serve video files directly via static URL endpoints.
    *   Stream videos dynamically using a controller (`VideoController.php`) that checks the user's active login session, enrollment status, and syllabus module unlock status before opening the file stream.
    *   Use CloudFront signed cookies/URLs in cloud production environments.

---

### 1.9 Password Policy
*   **Vulnerability Vector:** Weak passwords allowing brute-force or dictionary cracking.
*   **Mitigation Strategy:**
    *   Enforce strict complexity validation checks during registration (min 10 characters, at least 1 uppercase, 1 lowercase, 1 number, and 1 special symbol).
    *   Compare passwords against list checks of compromised passwords.

---

### 1.10 Rate Limiting
*   **Vulnerability Vector:** API abuse, brute-force login attempts, or denial-of-service (DoS) attacks on expensive computations (like PDF certificate compiles).
*   **Mitigation Strategy:**
    *   Implement rate limiting middleware (`RateLimitMiddleware`) tracking requests by IP address or authentication token.
    *   Use a Redis memory bucket to log requests.
    *   **Limits Matrix:**
        *   `POST /api/v1/auth/login`: Maximum 5 attempts per 5 minutes per IP.
        *   Standard API Requests: Maximum 60 requests per minute per user.
        *   Certificate PDF Compile: Maximum 2 requests per minute per user.

---

## 2. Hardening Recommendations Action Plan

> [!IMPORTANT]
> The following actions must be prioritized in the production deployment phase to achieve compliance with GDPR and educational data security requirements:

1.  **Configure SSL/TLS Termination:**
    *   Enforce TLS 1.3. Disallow weak SSL protocols on reverse proxy/load balancers.
    *   Configure HTTP Strict Transport Security (HSTS) headers.
2.  **Web Server Settings Hardening:**
    *   Disable server signatures (e.g., `ServerTokens ProductOnly` in Nginx / Apache settings).
    *   Explicitly restrict direct file execution inside the uploads folder.
3.  **Strict Content Security Policy (CSP):**
    *   Implement standard headers allowing scripts only from white-listed domains:
        `Content-Security-Policy: default-src 'self'; script-src 'self' https://trusted-cdn.com;`
4.  **Database Privilege Isolation:**
    *   Database connection profiles used by the PHP application should not connect as database root admin.
    *   Configure a custom user holding limited privileges (only `SELECT`, `INSERT`, `UPDATE`, `DELETE` operations on target tables).
