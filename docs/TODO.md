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
- [x] **Sprint 6 (core)** — ห้องเรียนสดและ WebRTC (stub + ตารางเรียน)
- [x] **Post-Sprint 6** — Quiz ผู้สอน, แดชบอร์ดผู้เรียน, CRUD โมดูล, รายงานความคืบหน้า
- [x] **Cohort & Readiness UX** — UI จัดการรุ่น + คะแนน quiz/ล็อกกลับในหน้า Readiness

### เลื่อนออกไปก่อน (Deferred)

- [ ] **Sprint 6/7 (Live interactions)** — Poll, Chat, WebSocket
  - โค้ดห้องเรียนสดพื้นฐานมีแล้ว แต่ยังไม่พัฒนาต่อในรอบนี้

### รอทำ (Pending)

- [ ] **Sprint 8** — Spaced Repetition (SM-2 Engine)
- [ ] **Sprint 9** — Gamification, Analytics & Certificates

---

## Feature Backlog

### สำคัญ (HIGH)

- [x] แดชบอร์ดผู้เรียน + รายงานความคืบหน้าหลักสูตร (ผู้สอน)
- [x] UI จัดการรุ่น (cohort) — `/instructor/courses/{id}/cohorts`
- [x] หน้า Readiness — แสดงคะแนน quiz ล่าสุด + ปุ่มล็อกกลับ
- [x] แก้ปุ่มแก้ไขโมดูลใน modal หลักสูตร
- [ ] Sprint 7 live polling & chat (WebSocket) — **เลื่อนออกไปก่อน**

### ปานกลาง (MEDIUM)

- [x] UI สองภาษา (ไทย / อังกฤษ)
- [x] Dark Mode
- [x] แถบ progress อัปโหลดไฟล์
- [x] นำเข้าผู้ใช้แบบ bulk (Excel)
- [x] ลืมรหัสผ่าน
- [x] แก้ไข Quiz ผู้สอน + นำเข้า Excel
- [x] ห้องเรียนสด — host roster & broadcast (มีแล้ว ไม่พัฒนาต่อ)
- [x] CRUD โมดูลแบบ modal (เพิ่ม / แก้ไข)
- [x] ความคืบหน้าผู้เรียนที่ลงทะเบียน (รายบุคคล + รวม)

### ต่ำ (LOW)

- [ ] Animation

### อนาคต (Future)

- [ ] AI Trainer
- [ ] Flutter
