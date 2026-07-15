-- แก้ชื่อรุ่นที่ถูกบันทึกเป็น ? จากการ seed ด้วย charset ผิด
-- รันผ่าน MySQL client เท่านั้น (ไม่ใช่ PowerShell โดยตรง)
-- ตัวอย่าง: D:\xamp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 fitcoch < database/fixes/001_fix_cohort_name_encoding.sql

USE fitcoch;

UPDATE cohorts
SET name = 'ภาคเรียนที่ 1/2569'
WHERE course_id = 1
  AND name LIKE '%1/2569%';

SELECT id, course_id, name FROM cohorts WHERE course_id = 1;
