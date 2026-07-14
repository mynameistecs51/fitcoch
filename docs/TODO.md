# FMMP Development TODO

## Sprint Progress (ROADMAP)

### Completed

- [x] **Sprint 0.5** — Architecture Design & Diagrams
- [x] **Sprint 1** — Authentication Core
- [x] **Sprint 2** — User Profiles & Role Management
- [x] **Sprint 3** — Course Curriculum Structures
- [x] **Sprint 4** — Lessons (Nuggets) & Video Streaming
- [x] **Sprint 5** — Quiz Engine & Readiness Gate
  - [x] Migration: `quizzes`, `questions`, `options`, `quiz_attempts`, `quiz_responses`, `readiness_tickets`
  - [x] `QuizService` grading logic (single-choice, weighted points)
  - [x] Auto-unlock readiness tickets at ≥ 80%
  - [x] Learner quiz UI `/quizzes/{id}` + API submit
  - [x] Instructor readiness override panel per module
  - [x] Seed readiness quiz for Unit 1
- [x] **Post-Sprint 5 polish** — Admin, auth, and onboarding UX
  - [x] Admin user edit form (profile fields) + responsive 2-column layout
  - [x] Bulk user import (Excel/CSV template download + upload)
  - [x] Forgot password / reset password flow (migration `009`)
  - [x] Register form fix (`form-progress.js` no longer strips POST fields)
  - [x] Default timezone `Asia/Bangkok` (removed timezone fields from forms)

### Pending

- [ ] **Sprint 6** — Virtual Classroom & WebRTC Workspace
  - [ ] Migration: `live_sessions`, `session_participants`, `webrtc_signals`
  - [ ] `LiveSessionService` + readiness gate middleware on `/live/{id}`
  - [ ] Learner/instructor live room UI (WebRTC signaling stub)
  - [ ] Session scheduling from instructor course module panel
- [ ] **Sprint 7** — Live Classroom Interactions (Polling & Chat)
- [ ] **Sprint 8** — Spaced Repetition (SM-2 Engine)
- [ ] **Sprint 9** — Gamification, Analytics & Certificates

---

## Feature Backlog

### HIGH

- [ ] Finish Quiz (editor UI for instructors — future enhancement)
- [ ] Finish Dashboard
- [ ] Sprint 6 live classroom (WebRTC workspace)

### MEDIUM

- [x] Bilingual UI (Thai / English)
- [x] Dark Mode
- [x] Form upload progress bar
- [x] Admin bulk user import (Excel template)
- [x] Forgot password flow

### LOW

- [ ] Animation

### Future

- [ ] AI Trainer
- [ ] Flutter
