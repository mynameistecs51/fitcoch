-- Sprint 3: Seed sample course curriculum
-- Requires: 004_create_course_tables.sql

USE fitcoch;

INSERT INTO courses (title, description, status)
SELECT
    'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง',
    'หลักสูตรวิทยาศาสตร์การกีฬา: กายวิภาคศาสตร์ การคัดกรองความเสี่ยง และหลัก FITT-VP สำหรับผู้ฝึกสอนส่วนบุคคล',
    'published'
WHERE NOT EXISTS (
    SELECT 1 FROM courses WHERE title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
);

SET @course_id = (
    SELECT id FROM courses
    WHERE title = 'ระบบการเรียนรู้แบบห้องเรียนกลับด้านร่วมกับไมโครเลิร์นนิง'
    LIMIT 1
);

INSERT INTO cohorts (course_id, name, start_date, end_date)
SELECT @course_id, 'ภาคเรียนที่ 1/2569', '2026-01-15', '2026-06-30'
WHERE @course_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM cohorts WHERE course_id = @course_id AND name = 'ภาคเรียนที่ 1/2569'
);

SET @cohort_id = (
    SELECT id FROM cohorts WHERE course_id = @course_id AND name = 'ภาคเรียนที่ 1/2569' LIMIT 1
);

INSERT IGNORE INTO cohort_enrollments (cohort_id, user_id, status)
SELECT @cohort_id, u.id, 'active'
FROM users u
WHERE @cohort_id IS NOT NULL;

INSERT INTO modules (course_id, title, sequence_order)
SELECT @course_id, 'Unit 1: Biomechanics & Squat Analysis', 1
WHERE @course_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM modules WHERE course_id = @course_id AND sequence_order = 1);

INSERT INTO modules (course_id, title, sequence_order)
SELECT @course_id, 'Unit 2: Health Screening (PAR-Q+)', 2
WHERE @course_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM modules WHERE course_id = @course_id AND sequence_order = 2);

INSERT INTO modules (course_id, title, sequence_order)
SELECT @course_id, 'Unit 3: FITT-VP Programming', 3
WHERE @course_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM modules WHERE course_id = @course_id AND sequence_order = 3);
