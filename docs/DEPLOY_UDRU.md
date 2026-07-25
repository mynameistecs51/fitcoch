# Deploy บน UDRU Hosting

**URL:** https://hosting.udru.ac.th/~fitcoch/public/

ตอนนี้โฟลเดอร์บนเซิร์ฟเวอร์ยังว่าง ([ดู directory listing](https://hosting.udru.ac.th/~fitcoch/)) — ต้องอัปโหลดไฟล์และ import DB ก่อนใช้งาน

---

## วิธีที่ 1 — อัปโหลด ZIP (แนะนำถ้า FTP จาก Cursor ไม่ได้)

### บนเครื่อง dev

```powershell
cd D:\xamp\htdocs\fitcoch
copy .env.production.udru.example .env.udru
# แก้ .env.udru: DB_PASSWORD, JWT_SECRET (สุ่ม 64 ตัว)
.\scripts\package-udru.ps1
```

ได้ไฟล์ **`fitcoch-deploy.zip`**

### อัปโหลดไปเซิร์ฟเวอร์

ใช้ **FileZilla** หรือ File Manager ของโฮสต์:

| รายการ | ค่า |
|--------|-----|
| Host | `hosting.udru.ac.th` |
| Protocol | **SFTP** (ลองก่อน) หรือ **FTP** |
| Port | 22 (SFTP) / 21 (FTP) |
| User | `fitcoch` |
| Password | รหัสที่ IT ให้ |

1. อัปโหลด `fitcoch-deploy.zip` ไปที่ home (`~fitcoch`)
2. Extract บนเซิร์ฟเวอร์ (หรืออัปโหลดไฟล์ที่ extract แล้วทั้งโฟลเดอร์)
3. อัปโหลด `.env.udru` ของคุณเป็น **`.env`** ที่ root โปรเจกต์

---

## วิธีที่ 2 — สคริปต์ SFTP (ถ้า SSH login ได้)

```powershell
pip install paramiko
copy .env.production.udru.example .env.udru   # แก้ค่าให้ครบ
$env:FITCOCH_SFTP_PASS='รหัสผ่าน'
python scripts/deploy-udru-sftp.py
```

---

## ฐานข้อมูล (phpMyAdmin)

1. เปิด https://hosting.udru.ac.th/phpmyadmin  
2. Login: user `fitcoch`  
3. เลือก DB **`fitcoch`**  
4. **Import** ไฟล์ `database/migrations/all.sql` (สร้างด้วย `.\scripts\build-migration-bundle.ps1`)  
   - Charset: **utf8mb4**  
5. ถ้าชื่อไทยเป็น `?` → import `database/fixes/003_fix_demo_user_names.sql`

### ค่า `.env` สำหรับ DB

```ini
DB_HOST=localhost
DB_DATABASE=fitcoch
DB_USERNAME=fitcoch
DB_PASSWORD=รหัสจริง
APP_URL=https://hosting.udru.ac.th/~fitcoch/public
APP_ENV=production
APP_DEBUG=false
```

> บน shared hosting ใช้ `DB_HOST=localhost` ไม่ใช่ชื่อโดเมน phpMyAdmin

---

## สิทธิ์โฟลเดอร์ (chmod 755 หรือ 775)

- `storage/logs`
- `storage/cache`
- `public/uploads/videos`

---

## ทดสอบหลัง deploy

1. https://hosting.udru.ac.th/~fitcoch/ → redirect ไป `/public/`  
2. หน้า Landing โหลด  
3. Login demo (หลัง seed): `learner@fitcoch.com` / `Password123!`

---

## ปัญหาที่พบจากเครื่อง dev

- **FTP (port 21):** เชื่อมต่อแล้ว connection หลุด (อาจต้องใช้ FTPS หรืออัปโหลดผ่าน FileZilla จากเครื่องคุณ)  
- **SSH/SFTP:** `Authentication failed` — ตรวจรหัสผ่านกับ IT ว่าเปิด SSH หรือใช้รหัสคนละชุดกับ phpMyAdmin  

---

## ความปลอดภัย

- **อย่า commit `.env` / `.env.udru`** (อยู่ใน `.gitignore` แล้ว)  
- เปลี่ยนรหัสผ่านหลัง deploy ถ้าเคยส่งในแชท/อีเมล  
- ตั้ง `JWT_SECRET` เป็นค่าสุ่มยาว ๆ
