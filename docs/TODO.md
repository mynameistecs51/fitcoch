# FMMP Development TODO

## ความคืบหน้า Sprint (ตาม ROADMAP)

### เสร็จแล้ว

- [x] **Sprint 0.5** — ออกแบบสถาปัตยกรรมและแผนภาพ
- [x] **Sprint 1** — ระบบ Authentication
- [x] **Sprint 2** — โปรไฟล์ผู้ใช้และบทบาท
- [x] **Sprint 3** — โครงสร้างหลักสูตรและ Cohort
- [x] **Sprint 4** — บทเรียน (Nuggets) และสตรีมวิดีโอ
- [x] **Sprint 5** — Quiz Engine และ Readiness Gate
- [x] **Post-Sprint 5** — ปรับ UX แอดมิน, auth และ onboarding
- [x] **Sprint 6 (แทน Live)** — กระดานสนทนาต่อบทเรียน (Discussion Board)
  - ตาราง `module_discussions` + API/Controller/Service
  - แสดงใน sidebar หน้า nugget/quiz (โพสต์ได้เมื่อลงทะเบียน หรือผู้สอน/แอดมิน)
  - UI แชท: ข้อความตัวเองชิดขวา คนอื่นชิดซ้าย
  - Realtime polling + โพสต์ AJAX (Enter ส่ง / Shift+Enter ขึ้นบรรทัดใหม่)
  - เลื่อนเฉพาะกล่องข้อความ + โฟกัสกลับช่อง input หลังโพสต์สำเร็จ
- [x] **Post-Sprint 6** — Quiz ผู้สอน, แดชบอร์ดผู้เรียน, CRUD โมดูล, รายงานความคืบหน้า
- [x] **Cohort & Readiness UX** — UI จัดการรุ่น + คะแนน quiz/ล็อกกลับในหน้า Readiness
- [x] **Sprint 8 (MVP)** — Spaced Repetition SM-2, `/review/daily`, API, แดชบอร์ด CTA
- [x] **Sprint 8 (Instructor)** — จัดการแนวคิดทบทวน (ตาราง + modal เพิ่ม/แก้ไข)
- [x] **Sprint 8 (Dashboard)** — หน้า `/review/dashboard` สรุปคิวทบทวนและประวัติ
- [x] **UX Defaults** — โหมดสว่าง + ภาษาไทยเป็นค่าเริ่มต้น
- [x] **Live cleanup** — ลบโค้ดห้องเรียนสด, routes, เมนู sidebar และ UI ผู้สอน
- [x] **Sprint 9 (Phase 1)** — Gamification engine: migration, XP, streak (BR-02), badges, hooks + แดชบอร์ด
- [x] **Sprint 9 (Phase 2)** — Instructor analytics `/instructor/analytics/cohort/{id}` + API

### ตัดออกจากแผน (Removed)

- ~~**Sprint 6/7 (Live interactions)**~~ — Poll, Chat, WebSocket (แทนด้วยกระดานสนทนา)
  - ลบโค้ด Live ออกจาก repo แล้ว (migration 010/011 ยังคงไว้เป็นประวัติ)
- ~~**Sprint 8 CRON แจ้งเตือน**~~ — ไม่ทำในแผนปัจจุบัน

### รอทำ (Pending)

- [ ] **Sprint 9 (Phase 3)** — Certificates (verify URL + PDF download)

---

## Feature Backlog

### สำคัญ (HIGH)

- [x] แดชบอร์ดผู้เรียน + รายงานความคืบหน้าหลักสูตร (ผู้สอน)
- [x] UI จัดการรุ่น (cohort) — `/instructor/courses/{id}/cohorts`
- [x] หน้า Readiness — แสดงคะแนน quiz ล่าสุด + ปุ่มล็อกกลับ
- [x] กระดานสนทนาต่อบทเรียน — sidebar หน้า nugget/quiz + realtime + UX แชท
- [x] Sprint 8 — ทบทวนรายวัน `/review/daily` + อัลกอริทึม SM-2 + API
- [x] UI จัดการแนวคิดทบทวน — ตาราง + modal (เพิ่ม/แก้ไข/ดึงจากโมดูล)
- [x] โหมดสว่าง + ภาษาไทยเป็น default

### ปานกลาง (MEDIUM)

- [x] UI สองภาษา (ไทย / อังกฤษ)
- [x] Dark Mode (สลับได้ แต่ default เป็นโหมดสว่าง)
- [x] แถบ progress อัปโหลดไฟล์
- [x] นำเข้าผู้ใช้แบบ bulk (Excel)
- [x] ลืมรหัสผ่าน
- [x] แก้ไข Quiz ผู้สอน + นำเข้า Excel
- [x] CRUD โมดูลแบบ modal (เพิ่ม / แก้ไข)
- [x] ความคืบหน้าผู้เรียนที่ลงทะเบียน (รายบุคคล + รวม)

### ต่ำ (LOW)

- [ ] Animation

### อนาคต (Future)

- [ ] AI Trainer
- [ ] Flutter
