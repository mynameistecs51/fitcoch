-- แก้ชื่อ demo ที่กลายเป็น ? จากการ seed ด้วย charset ผิด
-- รัน: .\scripts\run-sql.ps1 database\fixes\003_fix_demo_user_names.sql

USE fitcoch;

SET NAMES utf8mb4;

UPDATE users SET title_prefix = 'นาย', first_name = 'ผู้ดูแล', last_name = 'ระบบ'
WHERE email = 'admin@fitcoch.com';

UPDATE users SET title_prefix = 'อาจารย์', first_name = 'สมศักดิ์', last_name = 'ผู้สอน'
WHERE email = 'instructor@fitcoch.com';

UPDATE users SET title_prefix = 'นาย', first_name = 'สมชาย', last_name = 'ใจดี'
WHERE email = 'learner@fitcoch.com';

UPDATE users SET title_prefix = 'นางสาว', first_name = 'สมหญิง', last_name = 'รักเรียน'
WHERE email = 'learner2@fitcoch.com';

UPDATE users SET title_prefix = 'นาย', first_name = 'เต้', last_name = 'ไชยวัฒน์'
WHERE email = 'te@fitcoch.com';

SELECT id, email, title_prefix, first_name, last_name FROM users
WHERE email IN (
    'admin@fitcoch.com',
    'instructor@fitcoch.com',
    'learner@fitcoch.com',
    'learner2@fitcoch.com',
    'te@fitcoch.com'
);
