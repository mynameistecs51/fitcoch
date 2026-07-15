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
  - [x] Password show/hide toggle on login & register (`password-toggle.js`)
- [x] **Sprint 6** — Virtual Classroom & WebRTC Workspace
  - [x] Migration: `live_sessions`, `live_attendance` (`010`, `011`)
  - [x] `LiveSessionService` + readiness gate middleware on `/live/{id}`
  - [x] Learner/instructor live room UI (WebRTC signaling stub)
  - [x] Session scheduling from instructor course module panel
  - [x] Host live controls (go live / end session, camera/mic toggle)
  - [x] Host participant roster API + real-time polling
- [x] **Post-Sprint 6 polish** — Instructor quiz & live UX
  - [x] Instructor quiz editor per module (create/edit/delete quiz & questions)
  - [x] Bulk add multiple questions in one submit
  - [x] Quiz import from Excel/CSV template (download + upload)
  - [x] Learner quiz answer layout — 2-column options on wider screens

### Pending

- [ ] **Sprint 7** — Live Classroom Interactions (Polling & Chat)
- [ ] **Sprint 8** — Spaced Repetition (SM-2 Engine)
- [ ] **Sprint 9** — Gamification, Analytics & Certificates

---

## Feature Backlog

### HIGH

- [ ] Finish Dashboard
- [ ] Sprint 7 live polling & chat (WebSocket)

### MEDIUM

- [x] Bilingual UI (Thai / English)
- [x] Dark Mode
- [x] Form upload progress bar
- [x] Admin bulk user import (Excel template)
- [x] Forgot password flow
- [x] Instructor quiz editor + Excel import
- [x] Live room host roster & broadcast controls

### LOW

- [ ] Animation

### Future

- [ ] AI Trainer
- [ ] Flutter
