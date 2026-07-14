# FMMP Development TODO

## Sprint Progress (ROADMAP)

### Completed

- [x] **Sprint 0.5** — Architecture Design & Diagrams (DFD, Use Case, Sequence, ER)
- [x] **Sprint 1** — Authentication Core
- [x] **Sprint 2** — User Profiles & Role Management
  - [x] Admin account management, FIT-FLIPPED UI, dark/light mode, responsive layout
  - [x] Form upload progress bar (`form-progress.js`)
- [x] **Sprint 3** — Course Curriculum Structures
  - [x] Courses, cohorts, modules, instructor management, learner syllabus API
- [x] **Sprint 4** — Lessons (Nuggets) & Video Streaming
  - [x] Migration: `nuggets`, `nugget_progress`
  - [x] `Nugget`, `NuggetRepository`, `NuggetProgressRepository`, `NuggetService`
  - [x] `VideoService` — upload validation, YouTube URLs, `206` byte-range streaming
  - [x] Instructor video fields (YouTube / file upload) on create & edit course
  - [x] Learner nugget view `/nuggets/{id}` with HTML5 / YouTube player
  - [x] Stream route `GET /nuggets/{id}/stream` (enrollment-gated)
  - [x] Progress API `POST /api/v1/nuggets/{id}/progress` + client heartbeat (`nugget-player.js`)
  - [x] Syllabus links to nugget lessons
  - [x] Fix `CourseRepository::query()` → `prepare()` for instructor course list

### Pending

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

- [x] Bilingual UI (Thai / English)
- [x] Dark Mode

### LOW

- [ ] Animation

### Future

- [ ] AI Trainer
- [ ] Flutter
