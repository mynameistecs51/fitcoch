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
- [x] **UX Defaults** — โหมดสว่าง + ภาษาไทยเป็นค่าเริ่มต้น

### ตัดออกจากแผน (Removed)

- ~~**Sprint 6/7 (Live interactions)**~~ — Poll, Chat, WebSocket (แทนด้วยกระดานสนทนา)
  - โค้ดห้องเรียนสดพื้นฐานยังอยู่ใน repo แต่ไม่พัฒนาต่อ

### รอทำ (Pending)

- [ ] **Sprint 8 (ต่อ)** — CRON scheduler แจ้งเตือนรายวัน + หน้า `/review/dashboard`
- [ ] **Sprint 9** — Gamification, Analytics & Certificates

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
