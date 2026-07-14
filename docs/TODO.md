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

### Pending

- [ ] **Sprint 2** — User Profiles & Role Management
  - [ ] `roles` and `user_roles` tables
  - [ ] `RoleMiddleware`
  - [ ] Profile settings view (`/profile`)
  - [ ] `GET /api/v1/users/me`
- [ ] **Sprint 3** — Course Curriculum Structures
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

- [ ] Dark Mode

### LOW

- [ ] Animation

### Future

- [ ] AI Trainer
- [ ] Flutter
