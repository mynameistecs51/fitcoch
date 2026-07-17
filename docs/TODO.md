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
- [x] **UX Defaults** — โหมดสว่าง + ภาษาไทยอย่างเดียว (ตัดตัวเลือกอังกฤษออกจาก UI)
- [x] **Live cleanup** — ลบโค้ดห้องเรียนสด, routes, เมนู sidebar และ UI ผู้สอน
- [x] **Sprint 9 (Phase 1)** — Gamification engine: migration, XP, streak (BR-02), badges, hooks + แดชบอร์ด
- [x] **Sprint 9 (Phase 2)** — Instructor analytics `/instructor/analytics/cohort/{id}` + API
- [x] **Sprint 9 (Phase 3)** — Certificates (verify URL + PDF download)
- [x] **Animation** — micro-interactions, flashcard flip/slide บน `/review/daily`, hover/click polish
- [x] **หน้าแรกสาธารณะ** — Landing แบบ Thai MOOC (`/`, `HomeController`, layout สาธารณะ, ค้นหา/สถิติ/หมวดหมู่/หลักสูตร)
- [x] **ข้อความยังไม่อ่าน (ผู้สอน)** — migration `discussion_reads`, badge ในรายการหลักสูตร, mark read เมื่อเปิดกระดานสนทนา
- [x] **Quiz ผู้เรียน** — แสดงประวัติคำตอบล่าสุด + ปุ่มทำซ้ำ (`?retake=1`); แก้บล็อกเมื่อ Ticket ปลดล็อกแล้วแต่ยังไม่มี attempt
- [x] **โครงสร้างบทเรียน** — partial `course-lesson-structure`, การ์ดหลักสูตรพร้อม syllabus, ไฮไลต์บทเรียนที่เรียนจบ
- [x] **Syllabus โมดูล Quiz-only** — แสดงลิงก์แบบทดสอบใน Unit ที่ไม่มีวิดีโอ + ปุ่มเริ่มเรียนชี้ไป quiz ได้
- [x] **UX/UI Refresh** — design system (`app.css`), shell (header/sidebar/mobile nav), แดชบอร์ดผู้เรียน, โปรไฟล์, หน้า login/register
- [x] **ลงทะเบียน & โปรไฟล์** — รหัสนักศึกษา, คำนำหน้า, อีเมล, ยืนยันรหัสผ่าน; migration `019_add_user_student_fields`
- [x] **Login ปรับปรุง** — เข้าสู่ระบบด้วยอีเมลหรือรหัสนักศึกษา, จำการลงชื่อเข้าใช้ (30 วัน), ปุ่มแสดง/ซ่อนรหัสผ่าน
- [x] **นำเข้าผู้ใช้ Excel** — อัปเดตเทมเพลตคอลัมน์ (รหัสนักศึกษา, คำนำหน้า, ชื่อ, นามสกุล, รหัสผ่าน, role)
- [x] **หน้าภาพรวมทบทวน** — ออกแบบ `/review/dashboard` ใหม่ กระจายพื้นที่เต็มความกว้าง + hero/stat cards
- [x] **Demo users** — migration `020_seed_demo_users.sql` + เอกสาร `docs/DEMO_ACCOUNTS.md`
- [x] **UI ผู้สอนย่อย** — ความคืบหน้า, จัดการรุ่น, analytics, แนวคิดทบทวน (design system ครบชุด)

### ตัดออกจากแผน (Removed)

- ~~**Sprint 6/7 (Live interactions)**~~ — Poll, Chat, WebSocket (แทนด้วยกระดานสนทนา)
  - ลบโค้ด Live ออกจาก repo แล้ว (migration 010/011 ยังคงไว้เป็นประวัติ)
- ~~**Sprint 8 CRON แจ้งเตือน**~~ — ไม่ทำในแผนปัจจุบัน
- ~~**UI สองภาษา (ไทย / อังกฤษ)**~~ — ใช้ภาษาไทยอย่างเดียว

### รอทำ (Pending)

_ไม่มีงานค้างในแผนปัจจุบัน_

---

## Feature Backlog

### สำคัญ (HIGH)

- [x] แดชบอร์ดผู้เรียน + รายงานความคืบหน้าหลักสูตร (ผู้สอน)
- [x] UI จัดการรุ่น (cohort) — `/instructor/courses/{id}/cohorts`
- [x] หน้า Readiness — แสดงคะแนน quiz ล่าสุด + ปุ่มล็อกกลับ
- [x] กระดานสนทนาต่อบทเรียน — sidebar หน้า nugget/quiz + realtime + UX แชท
- [x] Sprint 8 — ทบทวนรายวัน `/review/daily` + อัลกอริทึม SM-2 + API
- [x] UI จัดการแนวคิดทบทวน — ตาราง + modal (เพิ่ม/แก้ไข/ดึงจากโมดูล)
- [x] โหมดสว่าง + ภาษาไทยอย่างเดียว (ไม่มีตัวเลือกอังกฤษ)

### ปานกลาง (MEDIUM)

- [x] Dark Mode (สลับได้ แต่ default เป็นโหมดสว่าง)
- [x] แถบ progress อัปโหลดไฟล์
- [x] นำเข้าผู้ใช้แบบ bulk (Excel)
- [x] ลืมรหัสผ่าน
- [x] แก้ไข Quiz ผู้สอน + นำเข้า Excel
- [x] CRUD โมดูลแบบ modal (เพิ่ม / แก้ไข)
- [x] ความคืบหน้าผู้เรียนที่ลงทะเบียน (รายบุคคล + รวม)

### ต่ำ (LOW)

- [x] Animation — hover/click transitions, flashcard flip, review slide, badge pop
- [x] หน้าแรกสาธารณะ (Landing) — hero, ค้นหา, สถิติ, หมวดหมู่, การ์ดหลักสูตร
- [x] ข้อความยังไม่อ่านในกระดานสนทนา (คอลัมน์ผู้สอน)
- [x] ประวัติ Quiz + ทำซ้ำ + แก้ UX เมื่อ Readiness Ticket ปลดล็อกแล้ว
- [x] Syllabus/การ์ดหลักสูตร — โครงสร้างบทเรียน + โมดูลที่มีแค่ Quiz
- [x] UX/UI ทั้งแอป — design system, shell, auth, แดชบอร์ด, หน้าภาพรวมทบทวน
- [x] ฟิลด์ลงทะเบียน (รหัสนักศึกษา, คำนำหน้า, อีเมล) + login ด้วยอีเมล/รหัสนักศึกษา

### อนาคต (Future)

- [ ] AI Trainer
- [ ] Flutter
