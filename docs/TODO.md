# FMMP Development TODO

## Sprint Progress (ROADMAP)

### Completed

- [x] **Sprint 0.5** — Architecture Design & Diagrams (DFD, Use Case, Sequence, ER)
- [x] **Sprint 1** — Authentication Core
  - [x] DI Container (`Core\Container.php`)
  - [x] Router, Request, Response
  - [x] Database connection layer (`Core\Database.php`)
  - [x] `users` table migration
  - [x] AuthController (login / register / logout)
  - [x] Session cookies (HTTP-only, SameSite)
  - [x] JWT API endpoints (`/api/v1/auth/*`)
  - [x] AuthMiddleware
  - [x] Web views (`/login`, `/register`, `/dashboard`)
  - [x] DI container unit tests (5 tests passing)
  - [x] Database migrated on XAMPP (MariaDB)
- [x] **Sprint 2** — User Profiles & Role Management
  - [x] `roles` and `user_roles` tables + seed data
  - [x] `RoleRepository`, `AuthorizationService`
  - [x] `RoleMiddleware` (route-level RBAC)
  - [x] Auto-assign `learner` role on registration
  - [x] Profile settings view (`/profile`) with timezone update
  - [x] `GET /api/v1/users/me` (stats placeholder until Sprint 9)
  - [x] `GET /api/v1/instructor/ping` (RBAC demo: instructor/admin only)
  - [x] AuthorizationService unit tests (7 tests total passing)
  - [x] Admin account management (`/admin/users`) — role & status control
  - [x] FIT-FLIPPED UI redesign (sidebar, header, dashboard hero)
  - [x] Dark / Light mode toggle with localStorage persistence
  - [x] Admin edit page table layout + Router int param casting fix

- [x] **Sprint 3** — Course Curriculum Structures
  - [x] Migration: `courses`, `cohorts`, `cohort_enrollments`, `modules`
  - [x] Course/Module models, repositories, `CourseService`
  - [x] Learner views: `/courses`, `/courses/{id}` (table syllabus)
  - [x] Instructor views: `/instructor/courses` (create/edit/modules)
  - [x] API: `GET /api/v1/courses`, `GET /api/v1/courses/{id}`
  - [x] Sample course seed migration

### Pending

- [ ] **Sprint 4** — Lessons (Nuggets) & Video Streaming
- [ ] **Sprint 5** — Quiz Engine & Readiness Gate
- [ ] **Sprint 6** — Virtual Classroom & WebRTC Workspace
- [ ] **Sprint 7** — Live Classroom Interactions (Polling & Chat)
- [ ] **Sprint 8** — Spaced Repetition (SM-2 Engine)
- [ ] **Sprint 9** — Gamification, Analytics & Certificates

---

## Feature Backlog

### HIGH

- [ ] Finish Quiz
- [ ] Finish Dashboard

### MEDIUM

- [x] Bilingual UI (Thai / English) — language switcher, `lang/en.php`, `lang/th.php`
- [x] Dark Mode — header toggle, Tailwind `dark:` classes, localStorage

### LOW

- [ ] Animation

### Future

- [ ] AI Trainer
- [ ] Flutter
