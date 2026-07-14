# REST API Specification
## Flipped-Microlearning MOOC Platform (FMMP)

This document specifies the REST API endpoints, payload configurations, error envelopes, and HTTP status codes for the FMMP services. All endpoints conform to JSON API standards under the `/api/v1` path prefix.

*   **Global Architecture:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **Module Plan:** [MODULES.md](file:///d:/xamp/htdocs/fitcoch/docs/MODULES.md)
*   **UI Guideline:** [UI_GUIDELINE.md](file:///d:/xamp/htdocs/fitcoch/docs/UI_GUIDELINE.md)

---

## 1. Global Specifications

### 1.1 Response Format (Standard Envelope)
All successful API responses return a structured envelope containing the requested data and metadata.

```json
{
  "success": true,
  "data": {},
  "meta": {
    "timestamp": "2026-07-14T02:30:00Z",
    "version": "1.0.0",
    "pagination": {
      "total": 120,
      "count": 20,
      "per_page": 20,
      "current_page": 1,
      "total_pages": 6
    }
  }
}
```

### 1.2 Error Format
Error payloads follow standard envelopes detailing validation or system exceptions.

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The provided inputs failed validation requirements.",
    "details": {
      "email": [
        "The email address has already been registered."
      ]
    }
  }
}
```

### 1.3 HTTP Status Codes
| Code | Name | Description |
| :--- | :--- | :--- |
| **200** | `OK` | Request succeeded. Returns data. |
| **201** | `Created` | Resource created (e.g. attempt saved). |
| **206** | `Partial Content` | Byte-range media chunks served for video playing. |
| **400** | `Bad Request` | Malformed parameters or business logic breaches. |
| **401** | `Unauthorized` | Missing or invalid auth credentials (JWT/Session). |
| **403** | `Forbidden` | RBAC restriction or locked readiness gate. |
| **404** | `Not Found` | Target resource does not exist. |
| **422** | `Unprocessable Entity` | Inputs validation failed. |
| **429** | `Too Many Requests` | Rate limit exceeded. |
| **500** | `Internal Error` | Unexpected application code failures. |

---

## 2. API Endpoints Directory

### 2.1 Authentication Module
Secure credentials login, registration, token refresh, and logout.

#### 2.1.1 Authenticate User (Login)
*   **Path:** `POST /api/v1/auth/login`
*   **Headers:** `Content-Type: application/json`
*   **Request Body:**
    ```json
    {
      "email": "learner@fitcoch.com",
      "password": "Password123!"
    }
    ```
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "access_token": "eyJhbGciOiJIUzI1NiIsIn...",
        "token_type": "Bearer",
        "expires_in": 3600,
        "user": {
          "id": 42,
          "email": "learner@fitcoch.com",
          "first_name": "John",
          "last_name": "Doe",
          "roles": ["learner"]
        }
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

#### 2.1.2 Register User
*   **Path:** `POST /api/v1/auth/register`
*   **Request Body:**
    ```json
    {
      "email": "learner@fitcoch.com",
      "password": "Password123!",
      "first_name": "John",
      "last_name": "Doe",
      "timezone": "America/New_York"
    }
    ```
*   **Success Response (201 Created):** Contains user object metadata.

#### 2.1.3 Invalidate Session (Logout)
*   **Path:** `POST /api/v1/auth/logout`
*   **Headers:** `Authorization: Bearer <access_token>`
*   **Success Response (200 OK):** `{"success": true, "message": "Session invalidated."}`

---

### 2.2 Users Module
Manage user profile properties and fetch gamified achievements.

#### 2.2.1 Get Current User Stats (Dashboard Profile)
*   **Path:** `GET /api/v1/users/me`
*   **Headers:** `Authorization: Bearer <access_token>`
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "id": 42,
        "email": "learner@fitcoch.com",
        "first_name": "John",
        "last_name": "Doe",
        "timezone": "America/New_York",
        "stats": {
          "current_streak": 14,
          "longest_streak": 35,
          "xp_balance": 3450,
          "level": 4,
          "shields_count": 2
        }
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

#### 2.2.2 List Awarded Credentials (Badges)
*   **Path:** `GET /api/v1/users/me/badges`
*   **Success Response (200 OK):** Returns array of badges (`id`, `name`, `icon_url`, `awarded_at`).

---

### 2.3 Course & Curriculum Module
Explore syllabuses and module paths.

#### 2.3.1 List Enrolled Courses
*   **Path:** `GET /api/v1/courses`
*   **Headers:** `Authorization: Bearer <access_token>`
*   **Query Parameters:** `page=1`, `limit=20`
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": [
        {
          "id": 1,
          "title": "Introduction to Microlearning",
          "status": "published",
          "progress_percentage": 65
        }
      ],
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

#### 2.3.2 Get Course Syllabus Details
*   **Path:** `GET /api/v1/courses/{course_id}`
*   **Success Response (200 OK):** Returns full module trees including array of nested nuggets with active unlocking indicator fields.

---

### 2.4 Lessons (Nuggets) Module
Access reading and video nuggets.

#### 2.4.1 Get Nugget Details
*   **Path:** `GET /api/v1/nuggets/{nugget_id}`
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "id": 12,
        "module_id": 1,
        "title": "Atomic Principles",
        "nugget_type": "reading",
        "content_url": null,
        "content_body": "<p>Microlearning breaks concepts down...</p>",
        "duration_seconds": 180,
        "sequence_order": 1,
        "status": "unlocked"
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

---

### 2.5 Video Module
Streams and records video nugget data.

#### 2.5.1 Stream Video Chunk
*   **Path:** `GET /api/v1/nuggets/{nugget_id}/stream`
*   **Headers:**
    *   `Authorization: Bearer <access_token>`
    *   `Range: bytes=1048576-2097151`
*   **Success Response (206 Partial Content):**
    *   **Headers:**
        *   `Content-Type: video/mp4`
        *   `Content-Range: bytes 1048576-2097151/104857600`
        *   `Content-Length: 1048576`
    *   **Body:** Binary byte stream.

---

### 2.6 Quiz Module
Validate prep, submit scores, review spaced-rep cards.

#### 2.6.1 Fetch Quiz Assessment Schema
*   **Path:** `GET /api/v1/quizzes/{quiz_id}`
*   **Success Response (200 OK):** Returns quiz metadata and array of questions containing options details (hiding correctness Boolean flags from learners).

#### 2.6.2 Submit Quiz Attempt (Readiness Gate Check)
*   **Path:** `POST /api/v1/quizzes/{quiz_id}/attempts`
*   **Request Body:**
    ```json
    {
      "responses": [
        { "question_id": 101, "selected_option_number": 3 },
        { "question_id": 102, "selected_option_number": 1 }
      ]
    }
    ```
*   **Success Response (201 Created):**
    ```json
    {
      "success": true,
      "data": {
        "attempt_id": 501,
        "score_pct": 100,
        "passed": true,
        "readiness_ticket": {
          "status": "unlocked",
          "unlocked_at": "2026-07-14T02:30:00Z"
        },
        "xp_awarded": 150
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

#### 2.6.3 Retrieve Spaced Repetition Queue (Daily Packet)
*   **Path:** `GET /api/v1/reviews/daily`
*   **Success Response (200 OK):** Returns list of concept review objects (`spaced_rep_schedules` records merged with `knowledge_items` definitions).

#### 2.6.4 Submit SM-2 Recall Quality Response
*   **Path:** `POST /api/v1/reviews/{knowledge_item_id}/respond`
*   **Request Body:**
    ```json
    {
      "rating": 4
    }
    ```
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "next_review_date": "2026-07-20",
        "interval_days": 6,
        "easiness_factor": 2.6,
        "xp_awarded": 50
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

---

### 2.7 Tracking Module
Track progress percentages.

#### 2.7.1 Update Nugget Progress Heartbeat
*   **Path:** `POST /api/v1/nuggets/{nugget_id}/progress`
*   **Request Body:**
    ```json
    {
      "progress_percentage": 85
    }
    ```
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "nugget_id": 12,
        "progress_percentage": 85,
        "status": "in_progress",
        "completed_at": null
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

---

### 2.8 Analytics Module (Admin/Instructor Only)
Query cohort readiness indexes.

#### 2.8.1 Fetch Cohort Module Readiness Aggregates
*   **Path:** `GET /api/v1/instructor/analytics/cohorts/{cohort_id}/modules/{module_id}`
*   **Headers:** `Authorization: Bearer <instructor_token>`
*   **Success Response (200 OK):**
    ```json
    {
      "success": true,
      "data": {
        "cohort_id": 1,
        "module_id": 3,
        "total_enrolled": 150,
        "completed_prep": 120,
        "readiness_ratio": 0.8,
        "alert_triggered": false,
        "top_misconceptions": [
          {
            "question_id": 105,
            "question_text": "Choose the SM-2 algorithm variable",
            "incorrect_ratio": 0.45
          }
        ]
      },
      "meta": { "timestamp": "2026-07-14T02:30:00Z", "version": "1.0.0" }
    }
    ```

---

### 2.9 Certificate Module
Fetch and download certificates.

#### 2.9.1 Retrieve Certificate Verification Details
*   **Path:** `GET /api/v1/certificates/{hash}`
*   **Success Response (200 OK):** Returns validation verification data (`user_name`, `course_title`, `awarded_at`, `verification_hash`).
