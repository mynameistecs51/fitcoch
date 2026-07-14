# Development Modules Specification
## Flipped-Microlearning MOOC Platform (FMMP)

This document breaks the platform implementation into six cohesive development modules. Each module isolates distinct features, models, views, controllers, services, repositories, and database schemas. 

*   **Global System Architecture Context:** [ARCHITECTURE.md](file:///d:/xamp/htdocs/fitcoch/docs/ARCHITECTURE.md)
*   **Database Specifications:** [DATABASE.md](file:///d:/xamp/htdocs/fitcoch/DATABASE.md)
*   **Software Requirements:** [SRS.md](file:///d:/xamp/htdocs/fitcoch/SRS.md)

---

## Module 1: User Authentication & Role Management (Core System)

### 1.1 Purpose
Secure user profiles and establish Role-Based Access Control (RBAC) layers across learners, instructors, and system administrators.

### 1.2 Features
*   User registration and secure password hashing (Argon2id/bcrypt).
*   Stateful Cookie/Session Authentication (Web) and Stateless JWT handling (REST API).
*   Role-Based Authorization checks (RBAC check middleware).
*   Timezone profiling (essential for local midnight habit streak validation).

### 1.3 Dependencies
*   None (Application Foundation Layer).

### 1.4 Database Tables
*   `users` (Data Dictionary: [DATABASE.md#3.1.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L145))
*   `roles` (Data Dictionary: [DATABASE.md#3.1.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L166))
*   `user_roles` (Data Dictionary: [DATABASE.md#3.1.3](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L180))

### 1.5 Code Elements Map
*   **Pages:**
    *   `/login` — System entry screen.
    *   `/register` — Registration portal.
    *   `/profile` — Personal timezone and setting editor.
*   **Controllers:**
    *   [AuthController](file:///d:/xamp/htdocs/fitcoch/app/Controllers/AuthController.php) — Manages register, login, and logout routines.
*   **Models:**
    *   `App\Models\User` — Domain wrapper for student identity.
    *   `App\Models\Role` — Representation of user clearance profiles.
*   **Views:**
    *   `app/Views/auth/login.php` — Tailwind CSS login view.
    *   `app/Views/auth/register.php` — Registration layout view.
    *   `app/Views/auth/profile.php` — Settings view.
    *   `app/Views/layouts/app.php` — Core master template view containing styling tokens.
*   **Services:**
    *   [AuthService](file:///d:/xamp/htdocs/fitcoch/app/Services/AuthService.php) — Processes validation logic and session state logs.
    *   `App\Services\AuthorizationService` — Evaluates RBAC privileges.
*   **Repositories:**
    *   [UserRepository](file:///d:/xamp/htdocs/fitcoch/app/Repositories/UserRepository.php) — Mapped parameterized queries lookup on `users`.

### 1.6 Estimated Complexity
*   **Complexity Level:** Low
*   **Timeline Estimate:** 5 Days

### 1.7 Acceptance Criteria
```gherkin
Scenario: Logging in with correct credentials
  Given a User exists with email "learner@fitcoch.com" and password "FitCoch123!"
  When the Client posts to /login with email "learner@fitcoch.com" and password "FitCoch123!"
  Then the response sets session variable user_id
  And redirects to /dashboard with status 200 OK.

Scenario: Blocking unauthorized route access
  Given an unauthenticated HTTP Client
  When the Client requests GET /profile
  Then the system intercepts the request via AuthMiddleware
  And returns HTTP 401 Unauthorized status.
```

---

## Module 2: Course & Microlearning Content Delivery (Asynchronous)

### 2.1 Purpose
Deliver bite-sized microlearning snippets sequentially (text cards, infographics, audio, and streamed video assets) while tracking student progression.

### 2.2 Features
*   Course syllabus tree structuring (Course ➔ Module ➔ Nuggets).
*   Sequence progression locking (unlocked only upon completing preceding modules/nuggets).
*   Chunked video files delivery supporting HTTP range scrub requests (`206 Partial Content`).
*   Progress logging using player client heartbeats.

### 2.3 Dependencies
*   Module 1 (Authentication & Session control context).

### 2.4 Database Tables
*   `courses` (Data Dictionary: [DATABASE.md#3.2.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L197))
*   `cohorts` (Data Dictionary: [DATABASE.md#3.2.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L213))
*   `cohort_enrollments` (Data Dictionary: [DATABASE.md#3.2.3](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L230))
*   `modules` (Data Dictionary: [DATABASE.md#3.2.4](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L244))
*   `nuggets` (Data Dictionary: [DATABASE.md#3.2.5](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L259))
*   `nugget_progress` (Data Dictionary: [DATABASE.md#3.2.6](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L278))

### 2.5 Code Elements Map
*   **Pages:**
    *   `/courses` — Course catalogs and active enrollments panel.
    *   `/courses/{course_id}` — Sequential syllabus overview.
    *   `/nuggets/{nugget_id}` — Media viewer rendering video players or reading cards.
    *   `/instructor/courses/edit` — Panel for uploads and sequence organizing.
*   **Controllers:**
    *   [CourseController](file:///d:/xamp/htdocs/fitcoch/app/Controllers/CourseController.php) — Processes curriculum fetches and progress updates.
    *   [VideoController](file:///d:/xamp/htdocs/fitcoch/app/Controllers/VideoController.php) — Serves chunked file streams.
*   **Models:**
    *   `App\Models\Course` — Entity for course properties.
    *   `App\Models\Cohort` — Entity tracking specific cohort runs.
    *   `App\Models\Module` — Logical node representing groups of nuggets.
    *   `App\Models\Nugget` — Bite-sized learning card metadata entity.
    *   `App\Models\NuggetProgress` — Tracker model for completion status.
*   **Views:**
    *   `app/Views/courses/index.php` — Course grid view.
    *   `app/Views/courses/show.php` — Syllabus lock/unlock flow view.
    *   `app/Views/courses/nugget.php` — Microlearning viewport (video player + card layout).
*   **Services:**
    *   `App\Services\CourseService` — Evaluates path unlocking gates.
    *   [VideoService](file:///d:/xamp/htdocs/fitcoch/app/Services/VideoService.php) — Computes range offsets and emits bytes.
    *   [FileUploadService](file:///d:/xamp/htdocs/fitcoch/app/Services/FileUploadService.php) — Handles local sandbox storage updates.
*   **Repositories:**
    *   `App\Repositories\CourseRepository` — Manages course structures.
    *   [NuggetRepository](file:///d:/xamp/htdocs/fitcoch/app/Repositories/NuggetRepository.php) — Tracks specific nugget data rows.

### 2.6 Estimated Complexity
*   **Complexity Level:** Medium
*   **Timeline Estimate:** 10 Days

### 2.7 Acceptance Criteria
```gherkin
Scenario: Enforcing sequential locking constraints
  Given the Learner has completed Nugget 1 in Module 1
  And has not completed Nugget 2
  When the Learner requests GET /nuggets/3
  Then the CourseService blocks access
  And returns HTTP 403 Forbidden.

Scenario: Seeking inside video nugget
  Given the Learner request GET /video/42/stream with range header "bytes=2000-5000"
  When the VideoService streams the file
  Then the response headers contain "HTTP/1.1 206 Partial Content"
  And "Content-Range: bytes 2000-5000/filesize"
  And exactly 3001 bytes are flushed to output.
```

---

## Module 3: Assessment & Flipped Readiness Gate (Pre-Class Validation)

### 3.1 Purpose
Verify asynchronous learner preparation via readiness quizzes, unlocking entry tickets for live flipped sessions.

### 3.2 Features
*   Readiness quiz grading and tracking of attempts.
*   Automatic ticket generation for live sessions (`readiness_tickets` mapping).
*   Manual override locks tool for instructors.
*   Auto-locking gates freezing prep options 1 hour before live classes start.

### 3.3 Dependencies
*   Module 1 (Authentication structure).
*   Module 2 (Module and curriculum entities).

### 3.4 Database Tables
*   `quizzes` (Data Dictionary: [DATABASE.md#3.3.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L298))
*   `questions` (Data Dictionary: [DATABASE.md#3.3.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L314))
*   `options` (Data Dictionary: [DATABASE.md#3.3.3](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L329))
*   `quiz_attempts` (Data Dictionary: [DATABASE.md#3.3.4](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L343))
*   `quiz_responses` (Data Dictionary: [DATABASE.md#3.3.5](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L358))
*   `readiness_tickets` (Data Dictionary: [DATABASE.md#3.3.6](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L374))

### 3.5 Code Elements Map
*   **Pages:**
    *   `/quiz/{quiz_id}` — Quiz rendering view (interactive selection cards).
    *   `/quiz/{quiz_id}/results` — Correct/incorrect choices overview and score display.
    *   `/instructor/overrides` — Gate bypass dashboard.
*   **Controllers:**
    *   [QuizController](file:///d:/xamp/htdocs/fitcoch/app/Controllers/QuizController.php) — Processes submissions, saves attempts, evaluates grades.
*   **Models:**
    *   `App\Models\Quiz` — Parent model defining passing limits.
    *   `App\Models\Question` — Sub-model wrapper.
    *   `App\Models\Option` — Option record linked by composite PK.
    *   `App\Models\QuizAttempt` — Single attempt results record.
    *   `App\Models\ReadinessTicket` — Active flipped access record.
*   **Views:**
    *   `app/Views/quizzes/attempt.php` — Interactive test template.
    *   `app/Views/quizzes/result.php` — Score metrics display.
    *   `app/Views/quizzes/gate_locked.php` — Locked-out message layout.
*   **Services:**
    *   [QuizService](file:///d:/xamp/htdocs/fitcoch/app/Services/QuizService.php) — Evaluates quiz answers, updates readiness tickets.
*   **Repositories:**
    *   [QuizRepository](file:///d:/xamp/htdocs/fitcoch/app/Repositories/QuizRepository.php) — Performs database reads and inserts for quizzes.

### 3.6 Estimated Complexity
*   **Complexity Level:** Medium
*   **Timeline Estimate:** 8 Days

### 3.7 Acceptance Criteria
```gherkin
Scenario: Unlocking flipped gate ticket with score >= 80%
  Given the Learner is attempting "Readiness Quiz A" (Module 1)
  When the Learner submits correct choices resulting in a score of 80%
  Then the QuizService inserts a record into readiness_tickets with status "unlocked"
  And the Learner can access the live session.

Scenario: Blocking entry for failing quiz
  Given the Learner fails "Readiness Quiz A" with a score of 60%
  When the Learner attempts to join the live cohort room
  Then the system redirects the user to /courses with "Readiness Gate Locked" message.
```

---

## Module 4: Virtual Synchronous Classroom & Live Interaction (In-Class WebRTC/WS)

### 4.1 Purpose
Host WebRTC virtual synchronous classrooms featuring real-time polling updates, presenter synchronization, and attendance logging.

### 4.2 Features
*   WebRTC room matching and signaling connections.
*   Ticket checks verifying user readiness at entry.
*   Real-time poll deployment using WebSockets push logs.
*   Aggregated interactive whiteboard synch logs.
*   Participant attendance logging (joined_at, left_at, total duration seconds).

### 4.3 Dependencies
*   Module 1 (Identity context).
*   Module 3 (Readiness ticket gate status).

### 4.4 Database Tables
*   `live_sessions` (Data Dictionary: [DATABASE.md#3.4.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L395))
*   `live_attendance` (Data Dictionary: [DATABASE.md#3.4.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L415))
*   `live_polls` (Data Dictionary: [DATABASE.md#3.4.3](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L430))
*   `live_poll_options` (Data Dictionary: [DATABASE.md#3.4.4](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L446))
*   `live_poll_responses` (Data Dictionary: [DATABASE.md#3.4.5](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L459))

### 4.5 Code Elements Map
*   **Pages:**
    *   `/live/{room_id}` — Video/audio collaborative interface.
    *   `/instructor/live/{session_id}` — Room administration hub (triggers polls and breakout groups).
*   **Controllers:**
    *   `App\Controllers\LiveSessionController` — Orchestrates sessions signaling and attendance saves.
*   **Models:**
    *   `App\Models\LiveSession` — Cohort session wrapper.
    *   `App\Models\LiveAttendance` — Tracking entity logging connection periods.
    *   `App\Models\LivePoll` — Model tracking questions asked live.
    *   `App\Models\LivePollResponse` — Individual submission record.
*   **Views:**
    *   `app/Views/live/classroom.php` — Video panel + sidebar chat + interactive poll overlay.
    *   `app/Views/live/instructor_control.php` — Instructor console dashboard.
*   **Services:**
    *   `App\Services\LiveInteractionService` — Coordinates WebSockets push broadcasts.
*   **Repositories:**
    *   `App\Repositories\LiveSessionRepository` — Handles SQL inserts and duration logs.

### 4.6 Estimated Complexity
*   **Complexity Level:** High
*   **Timeline Estimate:** 15 Days

### 4.7 Acceptance Criteria
```gherkin
Scenario: Enforcing ticket entry validation
  Given a Learner does not possess an "unlocked" or "overridden" ticket for Module 2
  When the Learner requests GET /live/room-12
  Then the system renders live/gate_locked view
  And blocks entry to WebRTC signaling paths.

Scenario: Recording session duration logs
  Given the Learner joins the room at 10:00:00
  When the Learner leaves the room at 10:45:00
  Then the LiveSessionRepository inserts/updates live_attendance with total_seconds set to 2700.
```

---

## Module 5: Spaced Repetition Reinforcement Engine (SM-2 Scheduler)

### 5.1 Purpose
Drive daily retention quizzes using the SuperMemo-2 cognitive logic, automatically generating custom review packets.

### 5.2 Features
*   Declarative knowledge nodes mapping.
*   Adaptive recall scheduling implementing SM-2 mathematics:
    *   $$EF_{new} = EF_{old} + 0.1 - (5 - q) \times (0.08 + (5 - q) \times 0.02)$$
*   CRON-driven queue generation aligning daily checks with user local timezones.

### 5.3 Dependencies
*   Module 1 (Identity & Timezone properties).
*   Module 3 (Quiz structure and response templates).

### 5.4 Database Tables
*   `knowledge_items` (Data Dictionary: [DATABASE.md#3.5.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L480))
*   `spaced_rep_schedules` (Data Dictionary: [DATABASE.md#3.5.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L494))

### 5.5 Code Elements Map
*   **Pages:**
    *   `/review/daily` — Spaced repetition interface.
    *   `/review/dashboard` — Progression calendars.
*   **Controllers:**
    *   `App\Controllers\ReviewController` — Handles consolidation responses and schedules next occurrences.
*   **Models:**
    *   `App\Models\KnowledgeItem` — Declarative concept model.
    *   `App\Models\SpacedRepSchedule` — Adaptive interval state tracker model.
*   **Views:**
    *   `app/Views/reviews/daily_quiz.php` — Rapid-fire flashcards UI.
    *   `app/Views/reviews/dashboard.php` — Schedule projections dashboard.
*   **Services:**
    *   `App\Services\SpacedRepetitionService` — Computes SM2 intervals.
*   **Repositories:**
    *   `App\Repositories\SpacedRepetitionRepository` — Manages review logs and queries.

### 5.6 Estimated Complexity
*   **Complexity Level:** Medium
*   **Timeline Estimate:** 7 Days

### 5.7 Acceptance Criteria
```gherkin
Scenario: Correct memory recall rating (SM-2 update)
  Given the Learner reviews Concept A (current repetition=1, EF=2.500)
  When the Learner inputs response quality rating 4
  Then the system updates interval_days to 6
  And repetition_number to 2
  And schedules next_review_date to current_date + 6 days.

Scenario: Resetting interval after failure rating < 3
  Given the Learner reviews Concept A
  When the Learner inputs response quality rating 1 (fail)
  Then the system resets repetition_number to 0
  And sets next_review_date to tomorrow (interval_days = 1).
```

---

## Module 6: Gamification, Streaks & Analytics (Engagement & Dashboards)

### 6.1 Purpose
Compute and present user progress, XP milestones, daily streak logs, and compiler tools preparing readiness summaries for educators.

### 6.2 Features
*   Habit streak validations matching local user midnight constraints (BR-02).
*   Experience Points (XP) transaction allocations.
*   Badge generation validations.
*   Cohort readiness aggregation queries calculating readiness index.
*   Misconception lists and at-risk user flags alert setups.

### 6.3 Dependencies
*   Module 1 (Identity).
*   Module 2 (Progress heartbeats).
*   Module 3 (Quiz logs).
*   Module 5 (Spaced repetition quiz logs).

### 6.4 Database Tables
*   `user_streaks` (Data Dictionary: [DATABASE.md#3.6.1](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L515))
*   `xp_transactions` (Data Dictionary: [DATABASE.md#3.6.2](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L530))
*   `badges` (Data Dictionary: [DATABASE.md#3.6.3](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L545))
*   `user_badges` (Data Dictionary: [DATABASE.md#3.6.4](file:///d:/xamp/htdocs/fitcoch/DATABASE.md#L559))

### 6.5 Code Elements Map
*   **Pages:**
    *   `/dashboard` — Main dashboard showing streaks, stats, XP progress, and badges.
    *   `/instructor/analytics/cohort/{cohort_id}` — Prep progress summary charts.
*   **Controllers:**
    *   [AnalyticsController](file:///d:/xamp/htdocs/fitcoch/app/Controllers/AnalyticsController.php) — Serves aggregations and streak logs.
*   **Models:**
    *   `App\Models\UserStreak` — Active streak metadata model.
    *   `App\Models\XpTransaction` — Single XP record model.
    *   `App\Models\Badge` — System badge definitions.
    *   `App\Models\UserBadge` — Award records container model.
*   **Views:**
    *   `app/Views/dashboard/user_home.php` — Interactive gamified home layout.
    *   `app/Views/dashboard/instructor_analytics.php` — High charts visual dashboard.
*   **Services:**
    *   [AnalyticsService](file:///d:/xamp/htdocs/fitcoch/app/Services/AnalyticsService.php) — Processes aggregation indexes and alerts thresholds.
    *   `App\Services\GamificationService` — Awards badges and records streak updates.
*   **Repositories:**
    *   [AnalyticsRepository](file:///d:/xamp/htdocs/fitcoch/app/Repositories/AnalyticsRepository.php) — Mapped SQL queries aggregating progress logs.
    *   `App\Repositories\GamificationRepository` — Tracks streaks and XP counters.

### 6.6 Estimated Complexity
*   **Complexity Level:** Medium
*   **Timeline Estimate:** 9 Days

### 6.7 Acceptance Criteria
```gherkin
Scenario: Incrementing daily habit streaks
  Given the User completed a Spaced Rep Quiz today (July 14)
  And the last_activity_date was yesterday (July 13)
  When the GamificationService processes the event
  Then current_streak increments by 1
  And last_activity_date updates to July 14.

Scenario: Triggering low class readiness alerts (BR-04)
  Given class starts in 12 hours
  And 50% of the cohort has completed prep (less than the 60% requirement)
  When the AnalyticsService runs calculation checks
  Then the response object flags trigger_low_readiness_alert as true.
```
