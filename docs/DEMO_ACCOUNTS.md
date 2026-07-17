# บัญชี Demo (Local / Presentation)

รหัสผ่านทุกบัญชี: **`Password123!`**

รัน seed ด้วย migration `020_seed_demo_users.sql` (หลัง `019`):

```bash
mysql -u root fitcoch < database/migrations/020_seed_demo_users.sql
```

| บทบาท | รหัสนักศึกษา | อีเมล | ชื่อ |
| :--- | :--- | :--- | :--- |
| Admin | `6501000001` | admin@fitcoch.com | นาย ผู้ดูแล ระบบ |
| Instructor | `6501000002` | instructor@fitcoch.com | อาจารย์ สมศักดิ์ ผู้สอน |
| Learner | `6501234567` | learner@fitcoch.com | นาย สมชาย ใจดี |
| Learner | `6501234568` | learner2@fitcoch.com | นางสาว สมหญิง รักเรียน |
| Learner | `6501234569` | te@fitcoch.com | นาย เต้ ไชยวัฒน์ |

ผู้เรียนทั้ง 3 คนถูกลงทะเบียนในรุ่น **ภาคเรียนที่ 1/2569** ของหลักสูตรตัวอย่าง และมี Readiness Ticket สำหรับ Unit 1

เข้าสู่ระบบได้ด้วย **อีเมล** หรือ **รหัสนักศึกษา**
