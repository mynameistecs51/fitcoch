-- Demo user accounts for local presentations and QA
-- Requires: 002_create_roles_tables.sql, 005_seed_sample_course.sql, 019_add_user_student_fields.sql
-- Password for every account below: Password123!
-- Run with UTF-8 (see scripts/run-sql.ps1)

USE fitcoch;

SET NAMES utf8mb4;

SET @demo_password = '$argon2id$v=19$m=65536,t=4,p=1$RExyZHpyd0ZNRU1jblRuOA$CeG39GTkhToftJ99977iyAgGAfwO7LTYBjpJK1z/0bQ';

INSERT INTO users (student_id, title_prefix, email, password_hash, first_name, last_name, timezone, status)
VALUES
    ('6501000001', 'นาย', 'admin@fitcoch.com', @demo_password, 'ผู้ดูแล', 'ระบบ', 'Asia/Bangkok', 'active'),
    ('6501000002', 'อาจารย์', 'instructor@fitcoch.com', @demo_password, 'สมศักดิ์', 'ผู้สอน', 'Asia/Bangkok', 'active'),
    ('6501234567', 'นาย', 'learner@fitcoch.com', @demo_password, 'สมชาย', 'ใจดี', 'Asia/Bangkok', 'active'),
    ('6501234568', 'นางสาว', 'learner2@fitcoch.com', @demo_password, 'สมหญิง', 'รักเรียน', 'Asia/Bangkok', 'active'),
    ('6501234569', 'นาย', 'te@fitcoch.com', @demo_password, 'เต้', 'ไชยวัฒน์', 'Asia/Bangkok', 'active')
ON DUPLICATE KEY UPDATE
    student_id = VALUES(student_id),
    title_prefix = VALUES(title_prefix),
    password_hash = VALUES(password_hash),
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    timezone = VALUES(timezone),
    status = VALUES(status);

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE u.email IN (
    'admin@fitcoch.com',
    'instructor@fitcoch.com',
    'learner@fitcoch.com',
    'learner2@fitcoch.com',
    'te@fitcoch.com'
)
AND r.name = 'learner';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE u.email = 'admin@fitcoch.com'
  AND r.name = 'admin';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT u.id, r.id
FROM users u
CROSS JOIN roles r
WHERE u.email = 'instructor@fitcoch.com'
  AND r.name = 'instructor';

SET @cohort_id = (
    SELECT co.id
    FROM cohorts co
    INNER JOIN courses c ON c.id = co.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
    LIMIT 1
);

SET @module_id = (
    SELECT m.id
    FROM modules m
    INNER JOIN courses c ON c.id = m.course_id
    WHERE c.title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
      AND m.sequence_order = 1
    LIMIT 1
);

INSERT IGNORE INTO cohort_enrollments (cohort_id, user_id, status)
SELECT @cohort_id, u.id, 'active'
FROM users u
WHERE @cohort_id IS NOT NULL
  AND u.email IN (
      'learner@fitcoch.com',
      'learner2@fitcoch.com',
      'te@fitcoch.com'
  );

INSERT IGNORE INTO readiness_tickets (user_id, cohort_id, module_id, status)
SELECT ce.user_id, ce.cohort_id, @module_id, 'locked'
FROM cohort_enrollments ce
INNER JOIN users u ON u.id = ce.user_id
WHERE @cohort_id IS NOT NULL
  AND @module_id IS NOT NULL
  AND ce.cohort_id = @cohort_id
  AND ce.status = 'active'
  AND u.email IN (
      'learner@fitcoch.com',
      'learner2@fitcoch.com',
      'te@fitcoch.com'
  );
