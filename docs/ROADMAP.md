# Engineering Roadmap & Sprint Schedules
## Flipped-Microlearning MOOC Platform (FMMP)

This document establishes the sprint-based roadmap for the FMMP project. The execution path is structured into **nine sequential 2-week sprints** mapped from system requirements, specifying effort estimates (Story Points), priority, dependencies, and deliverables.

*   **Global Architecture:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **Module Breakdown:** [MODULES.md](file:///d:/xamp/htdocs/fitcoch/docs/MODULES.md)
*   **REST API Directory:** [API_SPEC.md](file:///d:/xamp/htdocs/fitcoch/docs/API_SPEC.md)
*   **Performance Sizing:** [PERFORMANCE.md](file:///d:/xamp/htdocs/fitcoch/docs/PERFORMANCE.md)

---

## 1. Roadmap Executive Summary

The roadmap focuses on building the core application foundation first, followed by content delivery mechanisms, interactive classroom loops, cognitive retention engines, and finally engagement mechanics.

```
Engineering Roadmap Dependency Path
┌────────────────────────────────────────────────────────┐
│ Sprint 1: Authentication                               │
└──────────────────────────┬─────────────────────────────┘
                           ▼
┌────────────────────────────────────────────────────────┐
│ Sprint 2: User Profiles & Timezones                    │
└──────────────────────────┬─────────────────────────────┘
                           ▼
┌────────────────────────────────────────────────────────┐
│ Sprint 3 & 4: Course, Lessons & Video Streaming        │
└──────────────────────────┬─────────────────────────────┘
                           ▼
┌────────────────────────────────────────────────────────┐
│ Sprint 5: Quiz & Readiness Gate                        │
└──────────────────────────┬─────────────────────────────┘
                           ▼
┌────────────────────────────────────────────────────────┐
│ Sprint 6 & 7: Live WebRTC Class & Spaced Repetition    │
└──────────────────────────┬─────────────────────────────┘
                           ▼
┌────────────────────────────────────────────────────────┐
│ Sprint 8 & 9: Gamification, Analytics & Certificates   │
└────────────────────────────────────────────────────────┘
```

---

## 2. Sprint Timeline & Execution Details

### 2.1 Sprint 1: Authentication Core
*   **Focus:** Establish framework directories, Database connection layers, custom Dependency Injection (DI) Container, and basic login session handling.
*   **Effort Estimate:** 8 Story Points (5 developer days)
*   **Priority:** Critical
*   **Dependencies:** None (Base Bootstrap)
*   **Deliverables:**
    1.  Initialize custom DI Container (`Core\Container.php`) and Router.
    2.  Setup DB connections pool (`Core\Database.php`) with MySQL 8.
    3.  Create Auth Controller (`app/Controllers/AuthController.php`) handling login/register endpoints.
    4.  Configure secure HTTP-Only session cookie rules.
*   **Mitigation Strategy:** Establish integration unit tests for DI container dependency resolution before building routes.

---

### 2.2 Sprint 2: User Profiles & Role Management
*   **Focus:** Build user directories, manage profile data, set up localized timezones, and verify Role-Based Access Control (RBAC) middleware.
*   **Effort Estimate:** 5 Story Points (3 developer days)
*   **Priority:** Critical
*   **Dependencies:** Sprint 1 (Auth session context)
*   **Deliverables:**
    1.  Create `roles` and `user_roles` database schemas.
    2.  Configure `RoleMiddleware` checking user credentials.
    3.  Develop User Profile settings views (`app/Views/auth/profile.php`) allowing timezone configurations.
    4.  Expose `/api/v1/users/me` endpoint.

---

### 2.3 Sprint 3: Course Curriculum Structures
*   **Focus:** Develop core relational schemas for Course catalogs, Cohorts runs, Syllabus modules, and asynchronous enrollment paths.
*   **Effort Estimate:** 8 Story Points (5 developer days)
*   **Priority:** High
*   **Dependencies:** Sprint 2 (User profiles mapping)
*   **Deliverables:**
    1.  Create database tables for `courses`, `cohorts`, and `cohort_enrollments`.
    2.  Build course listing and outline views for students.
    3.  Implement Course Creator view interface for instructors.
    4.  Build Course and Module model validation routines.

---

### 2.4 Sprint 4: Lessons (Nuggets) & Video Streaming
*   **Focus:** Deliver microlearning content cards and implement chunk-based video streaming supporting byte-range player scrub requests.
*   **Effort Estimate:** 13 Story Points (8 developer days)
*   **Priority:** High
*   **Dependencies:** Sprint 3 (Course trees)
*   **Deliverables:**
    1.  Create `nuggets` and `nugget_progress` schemas.
    2.  Implement `VideoService.php` handling `206 Partial Content` ranges.
    3.  Develop custom HTML5 video player integration in lesson view.
    4.  Setup client heartbeats updates calling `/api/v1/nuggets/{id}/progress`.
*   **Mitigation Strategy:** Use `finfo` server validations to block arbitrary file types on video upload endpoints.

---

### 2.5 Sprint 5: Quiz Engine & Readiness Gate
*   **Focus:** Validate student pre-class preparation via quizzes, unlocking access tickets for live session entry.
*   **Effort Estimate:** 10 Story Points (6 developer days)
*   **Priority:** High
*   **Dependencies:** Sprint 4 (Lesson progress checks)
*   **Deliverables:**
    1.  Develop tables for `quizzes`, `questions`, `options`, and `readiness_tickets`.
    2.  Create quiz grading calculations logic.
    3.  Build automatic readiness ticket unlocks status loops ($score \ge 80\%$).
    4.  Build the manual override tool for instructors.

---

### 2.6 Sprint 6: Virtual Classroom & WebRTC Workspace
*   **Focus:** Establish signaling nodes and support WebRTC session entries restricted by active readiness tickets.
*   **Effort Estimate:** 21 Story Points (14 developer days)
*   **Priority:** High
*   **Dependencies:** Sprint 5 (Readiness tickets validations)
*   **Deliverables:**
    1.  Create `live_sessions` and `live_attendance` schemas.
    2.  Setup WebRTC signaling channel mappings.
    3.  Enforce gate middleware blockages on classroom route entries (`/live/{id}`).
    4.  Write participant connection duration logs.

---

### 2.7 Sprint 7: Live Classroom Interactions (Polling & Chat)
*   **Focus:** Build WebSocket communication pipelines to support live instructor polls and whiteboard data sharing.
*   **Effort Estimate:** 13 Story Points (8 developer days)
*   **Priority:** Medium
*   **Dependencies:** Sprint 6 (Live sessions connection context)
*   **Deliverables:**
    1.  Create `live_polls` and `live_poll_responses` database tables.
    2.  Integrate WebSockets server managing message routing.
    3.  Build live poll display overlays on student screens.
    4.  Generate aggregate distribution charts on instructor control consoles.

---

### 2.8 Sprint 8: Spaced Repetition (SM-2 Engine)
*   **Focus:** Calculate cognitive recall spacing schedules using SuperMemo-2 mathematical models to output daily student reviews.
*   **Effort Estimate:** 13 Story Points (8 developer days)
*   **Priority:** Medium
*   **Dependencies:** Sprint 5 (Quiz structures)
*   **Deliverables:**
    1.  Create `knowledge_items` and `spaced_rep_schedules` schemas.
    2.  Build `QuizService::calculateSM2` formulas.
    3.  Deploy a CRON background scheduler routing daily queues based on user timezones.
    4.  Build rapid-fire review quiz card layouts.

---

### 2.9 Sprint 9: Gamification, Analytics & Certificates
*   **Focus:** Build engagement metrics (streaks, XP, badges), compile class readiness indexes, and generate dynamically verify-able certificates.
*   **Effort Estimate:** 13 Story Points (9 developer days)
*   **Priority:** Medium
*   **Dependencies:** Sprint 8 (Daily review schedules)
*   **Deliverables:**
    1.  Create `user_streaks`, `xp_transactions`, and `user_badges` schemas.
    2.  Implement timezone-aligned streak check loops (BR-02).
    3.  Build class analytics monitor (readiness ratings, misconceptions alert triggers).
    4.  Expose certificate verification URLs and PDF download routes.
*   **Mitigation Strategy:** Cache analytics indexes on Redis server to prevent database lock issues during class loads.
